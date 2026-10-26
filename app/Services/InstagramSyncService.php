<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VideoPlatform;
use App\Models\SocialFeed;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de synchronisation des médias d'un compte Instagram professionnel.
 *
 * Deux types de jetons sont acceptés :
 *   - Jeton « Instagram Login » (commence par IG…) :
 *       GET https://graph.instagram.com/v21.0/me/media
 *   - Jeton Facebook (commence par EA…) + ID du compte Instagram professionnel :
 *       GET https://graph.facebook.com/v21.0/{ig-user-id}/media
 *
 * Champs : id, caption, media_type, media_url, thumbnail_url, permalink, timestamp,
 *          like_count, comments_count, username
 *
 * Tous les médias vont dans le flux social ; seules les vidéos et Reels
 * sont ajoutés aux vidéos du site.
 *
 * Documentation : https://developers.facebook.com/docs/instagram-platform
 */
final class InstagramSyncService
{
    private const FACEBOOK_GRAPH  = 'https://graph.facebook.com/v21.0';
    private const INSTAGRAM_GRAPH = 'https://graph.instagram.com/v21.0';

    public function __construct(
        private readonly string $igUserId,
        private readonly string $accessToken,
        private readonly int    $maxResults = 20,
    ) {}

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les derniers médias du compte Instagram et les persiste en base.
     *
     * @return int Nombre de vidéos créées ou mises à jour sur le site.
     */
    public function syncLatestMedia(): int
    {
        $items = $this->request('/media', [
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count,username',
            'limit'  => min(max($this->maxResults, 1), 100),
        ])['data'] ?? [];

        $synced = 0;
        foreach ($items as $item) {
            if ($this->upsertMedia($item)) {
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Vérifie le jeton et le compte, sans rien enregistrer.
     *
     * @return string Nom du compte (@username).
     */
    public function testConnection(): string
    {
        $username = $this->request('', ['fields' => 'username'])['username'] ?? null;

        return $username ? '@' . $username : throw new \RuntimeException('Compte Instagram introuvable.');
    }

    // -------------------------------------------------------------------------
    // Méthodes privées
    // -------------------------------------------------------------------------

    /** Le jeton vient-il de la « connexion Instagram » (graph.instagram.com) ? */
    private function usesInstagramLogin(): bool
    {
        return str_starts_with($this->accessToken, 'IG');
    }

    /** Appel GET sur le compte (« me » ou l'ID du compte) suivi de $path. */
    private function request(string $path, array $params): array
    {
        if ($this->usesInstagramLogin()) {
            $url = self::INSTAGRAM_GRAPH . '/me' . $path;
        } elseif ($this->igUserId !== '') {
            $url = self::FACEBOOK_GRAPH . '/' . $this->igUserId . $path;
        } else {
            throw new \RuntimeException('Instagram : avec un jeton Facebook (EAA…), renseignez aussi l’ID du compte Instagram professionnel.');
        }

        try {
            $response = Http::timeout(15)->get($url, $params + ['access_token' => $this->accessToken]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('InstagramSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Impossible de joindre Instagram. Vérifiez la connexion internet du serveur.', 0, $e);
        }

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            $hint = (int) $response->json('error.code') === 190 ? ' (jeton expiré : générez un nouveau jeton)' : '';
            Log::error('InstagramSyncService: erreur API.', ['error' => $error]);
            throw new \RuntimeException("Instagram : {$error}{$hint}");
        }

        return $response->json() ?? [];
    }

    /**
     * Enregistre un média dans le flux social, et dans les vidéos du site s'il s'agit d'une vidéo.
     *
     * @return bool true si une vidéo a été créée ou mise à jour.
     */
    private function upsertMedia(array $item): bool
    {
        $mediaId     = (string) $item['id'];
        $caption     = $item['caption'] ?? '';
        $title       = $this->extractTitle($caption);
        $mediaType   = $item['media_type'] ?? 'IMAGE';   // IMAGE | VIDEO | CAROUSEL_ALBUM
        $permalink   = $item['permalink'] ?? null;
        $thumbnail   = $mediaType === 'VIDEO' ? ($item['thumbnail_url'] ?? $item['media_url'] ?? null) : ($item['media_url'] ?? null);
        $publishedAt = isset($item['timestamp']) ? Carbon::parse($item['timestamp']) : null;
        $shortcode   = $this->extractShortcode($permalink ?? '') ?: $mediaId;

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::Instagram->value, 'platform_id' => $shortcode],
            [
                'media_type'     => match ($mediaType) { 'VIDEO' => 'video', 'CAROUSEL_ALBUM' => 'carousel', default => 'image' },
                'title'          => $title,
                'body'           => $caption,
                'thumbnail_url'  => $thumbnail,
                'media_url'      => $item['media_url'] ?? null,
                'post_url'       => $permalink,
                'source_id'      => $this->igUserId ?: null,
                'source_name'    => $item['username'] ?? null,
                'published_at'   => $publishedAt,
                'likes_count'    => (int) ($item['like_count'] ?? 0),
                'comments_count' => (int) ($item['comments_count'] ?? 0),
                'synced_at'      => now(),
                'raw_data'       => $item,
            ]
        );

        if ($mediaType !== 'VIDEO') {
            return false;
        }

        Video::updateOrCreate(
            ['platform' => VideoPlatform::Instagram->value, 'video_id' => $shortcode],
            [
                'title'          => $title,
                'description'    => $caption,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $permalink,
                'channel_id'     => $this->igUserId ?: null,
                'channel_title'  => $item['username'] ?? 'Instagram',
                'published_at'   => $publishedAt,
                'likes_count'    => (int) ($item['like_count'] ?? 0),
                'comments_count' => (int) ($item['comments_count'] ?? 0),
                'synced_at'      => now(),
                'raw_metadata'   => $item,
            ]
        );

        return true;
    }

    /**
     * Extrait un titre court depuis la légende (première ligne, max 80 chars).
     */
    private function extractTitle(string $caption): string
    {
        if (trim($caption) === '') {
            return 'Vidéo Instagram';
        }

        $lines = array_filter(explode("\n", trim($caption)));
        $first = reset($lines) ?: $caption;

        return mb_strlen($first) > 80 ? mb_substr($first, 0, 77) . '...' : $first;
    }

    /**
     * Extrait le shortcode depuis un permalink Instagram (publication, Reel ou IGTV).
     * ex: https://www.instagram.com/reel/CxYZ1234abcd/ → CxYZ1234abcd
     */
    private function extractShortcode(string $permalink): ?string
    {
        return preg_match('#instagram\.com/(?:p|reel|reels|tv)/([A-Za-z0-9_-]+)#', $permalink, $m) ? $m[1] : null;
    }
}
