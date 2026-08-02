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
 * Service de synchronisation automatique des médias d'un compte Instagram Business.
 *
 * Utilise Instagram Graph API (via Facebook Graph API v19.0) :
 *   GET /{ig-user-id}/media?fields=id,caption,media_type,media_url,thumbnail_url,
 *                                  permalink,timestamp,like_count,comments_count
 *                          &access_token={long-lived-token}
 *
 * Prérequis :
 *   - Un compte Instagram Business ou Creator
 *   - Relié à une Page Facebook gérée par votre compte
 *   - Une app Facebook avec la permission instagram_basic
 *   - Variables .env : INSTAGRAM_USER_ID, INSTAGRAM_ACCESS_TOKEN
 *
 * Types de médias supportés : IMAGE, VIDEO, CAROUSEL_ALBUM
 *
 * Documentation : https://developers.facebook.com/docs/instagram-api/reference/ig-user/media
 */
final class InstagramSyncService
{
    private const GRAPH_BASE = 'https://graph.facebook.com/v19.0';

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
     * @return int Nombre d'enregistrements créés ou mis à jour.
     */
    public function syncLatestMedia(): int
    {
        $items = $this->fetchMedia();

        $synced = 0;
        foreach ($items as $item) {
            $this->upsertMedia($item);
            $synced++;
        }

        return $synced;
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — appels API
    // -------------------------------------------------------------------------

    /**
     * Récupère les médias depuis l'API Instagram Graph.
     *
     * @return array<int, array>
     */
    private function fetchMedia(): array
    {
        try {
            $response = Http::timeout(15)->get(self::GRAPH_BASE . "/{$this->igUserId}/media", [
                'fields'       => 'id,caption,media_type,media_url,thumbnail_url,'
                    . 'permalink,timestamp,like_count,comments_count,username',
                'limit'        => $this->maxResults,
                'access_token' => $this->accessToken,
            ]);

            if ($response->failed()) {
                $error = $response->json('error.message') ?? $response->body();
                Log::error('InstagramSyncService: erreur API.', ['error' => $error]);
                throw new \RuntimeException("Instagram Graph API error: {$error}");
            }

            return $response->json('data') ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('InstagramSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — persistance
    // -------------------------------------------------------------------------

    /**
     * Crée ou met à jour un enregistrement Video à partir d'un média Instagram.
     *
     * L'identifiant unique est l'ID Instagram du média.
     */
    private function upsertMedia(array $item): void
    {
        $mediaId     = $item['id'];
        $caption     = $item['caption'] ?? '';
        $title       = $this->extractTitle($caption);
        $mediaType   = $item['media_type'] ?? 'IMAGE';   // IMAGE | VIDEO | CAROUSEL_ALBUM
        $permalink   = $item['permalink'] ?? null;

        // Pour les vidéos : thumbnail_url = miniature générée par Instagram
        // Pour les images : media_url est l'image directement
        $thumbnail = match ($mediaType) {
            'VIDEO'          => $item['thumbnail_url'] ?? $item['media_url'] ?? null,
            'CAROUSEL_ALBUM' => $item['media_url'] ?? null,
            default          => $item['media_url'] ?? null,
        };

        $publishedAt = isset($item['timestamp'])
            ? Carbon::parse($item['timestamp'])
            : null;

        // Le shortcode Instagram est extrait depuis le permalink (ex: /p/CxYZ123/)
        $shortcode = $this->extractShortcode($permalink ?? '');

        // Traduit le type API Instagram vers le type générique du feed
        $feedMediaType = match ($mediaType) {
            'VIDEO'          => 'video',
            'CAROUSEL_ALBUM' => 'carousel',
            default          => 'image',   // IMAGE
        };

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::Instagram->value, 'platform_id' => $shortcode ?: $mediaId],
            [
                'media_type'     => $feedMediaType,
                'title'          => $title,
                'body'           => $caption,
                'thumbnail_url'  => $thumbnail,
                'media_url'      => $item['media_url'] ?? null,
                'post_url'       => $permalink,
                'source_id'      => $this->igUserId,
                'source_name'    => $item['username'] ?? null,
                'published_at'   => $publishedAt,
                'likes_count'    => (int) ($item['like_count'] ?? 0),
                'comments_count' => (int) ($item['comments_count'] ?? 0),
                'synced_at'      => now(),
                'raw_data'       => $item,
            ]
        );

        Video::updateOrCreate(
            ['platform' => VideoPlatform::Instagram->value, 'video_id' => $shortcode ?: $mediaId],
            [
                'title'          => $title,
                'description'    => $caption,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $permalink,
                'channel_id'     => $this->igUserId,
                'channel_title'  => $item['username'] ?? 'Instagram',
                'published_at'   => $publishedAt,
                'duration'       => null,
                'view_count'     => 0,
                'likes_count'    => (int) ($item['like_count'] ?? 0),
                'comments_count' => (int) ($item['comments_count'] ?? 0),
                'synced_at'      => now(),
                'raw_metadata'   => $item,
            ]
        );
    }

    /**
     * Extrait un titre court depuis la légende (première ligne, max 80 chars).
     */
    private function extractTitle(string $caption): string
    {
        if ($caption === '') {
            return 'Post Instagram';
        }

        $lines = array_filter(explode("\n", trim($caption)));
        $first = reset($lines) ?: $caption;

        return mb_strlen($first) > 80
            ? mb_substr($first, 0, 77) . '...'
            : $first;
    }

    /**
     * Extrait le shortcode depuis un permalink Instagram.
     * ex: https://www.instagram.com/p/CxYZ1234abcd/ → CxYZ1234abcd
     */
    private function extractShortcode(string $permalink): ?string
    {
        if (preg_match('#instagram\.com/p/([A-Za-z0-9_-]+)#', $permalink, $m)) {
            return $m[1];
        }

        return null;
    }
}
