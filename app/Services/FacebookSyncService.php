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
 * Service de synchronisation des vidéos d'une Page Facebook.
 *
 * Utilise Facebook Graph API :
 *   GET /{page-id}/videos?fields=id,title,description,permalink_url,created_time,length,
 *                               picture,thumbnails{uri,is_preferred}
 *                        &access_token={page-access-token}
 *
 * Prérequis :
 *   - Une Page Facebook gérée par votre compte
 *   - Un Page Access Token (long-lived) avec la permission pages_read_engagement
 *   - Identifiants saisis dans Admin → Réseaux sociaux (ou FACEBOOK_PAGE_ID / FACEBOOK_PAGE_ACCESS_TOKEN)
 *
 * Seules les vidéos de la Page sont importées (le site les affiche comme des vidéos).
 */
final class FacebookSyncService
{
    private const GRAPH_BASE = 'https://graph.facebook.com/v21.0';

    public function __construct(
        private readonly string $pageId,
        private readonly string $pageAccessToken,
        private readonly int    $maxResults = 20,
    ) {}

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les dernières vidéos de la Page et les persiste en base.
     *
     * @return int Nombre de vidéos créées ou mises à jour.
     */
    public function syncLatestPosts(): int
    {
        $videos = $this->request("/{$this->pageId}/videos", [
            'fields' => 'id,title,description,permalink_url,created_time,length,picture,thumbnails{uri,is_preferred}',
            'limit'  => min(max($this->maxResults, 1), 100),
        ])['data'] ?? [];

        $synced = 0;
        foreach ($videos as $video) {
            $this->upsertVideo($video);
            $synced++;
        }

        return $synced;
    }

    /**
     * Vérifie le jeton et la Page, sans rien enregistrer.
     *
     * @return string Nom de la Page.
     */
    public function testConnection(): string
    {
        return $this->request("/{$this->pageId}", ['fields' => 'name'])['name']
            ?? throw new \RuntimeException('Page Facebook introuvable avec cet ID.');
    }

    // -------------------------------------------------------------------------
    // Méthodes privées
    // -------------------------------------------------------------------------

    private function request(string $path, array $params): array
    {
        try {
            $response = Http::timeout(15)->get(self::GRAPH_BASE . $path, $params + ['access_token' => $this->pageAccessToken]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('FacebookSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Impossible de joindre Facebook. Vérifiez la connexion internet du serveur.', 0, $e);
        }

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            $code = (int) $response->json('error.code');
            Log::error('FacebookSyncService: erreur API.', ['error' => $error]);

            $hint = match (true) {
                $code === 190 => ' (jeton expiré ou invalide : générez un nouveau jeton de Page)',
                $code === 100 => ' (ID de Page incorrect)',
                in_array($code, [10, 200], true) => ' (permission manquante : pages_read_engagement)',
                default => '',
            };

            throw new \RuntimeException("Facebook : {$error}{$hint}");
        }

        return $response->json() ?? [];
    }

    /**
     * Crée ou met à jour l'enregistrement d'une vidéo de la Page.
     */
    private function upsertVideo(array $video): void
    {
        $videoId     = (string) $video['id'];
        $description = $video['description'] ?? '';
        $title       = ($video['title'] ?? '') ?: $this->extractTitle($description);
        $permalink   = isset($video['permalink_url'])
            ? (str_starts_with($video['permalink_url'], 'http') ? $video['permalink_url'] : 'https://www.facebook.com' . $video['permalink_url'])
            : "https://www.facebook.com/{$this->pageId}/videos/{$videoId}/";
        $thumbnail   = $this->preferredThumbnail($video);
        $publishedAt = isset($video['created_time']) ? Carbon::parse($video['created_time']) : null;
        $duration    = isset($video['length']) ? 'PT' . (int) round((float) $video['length']) . 'S' : null;

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::Facebook->value, 'platform_id' => $videoId],
            [
                'media_type'    => 'video',
                'title'         => $title,
                'body'          => $description ?: null,
                'thumbnail_url' => $thumbnail,
                'post_url'      => $permalink,
                'source_id'     => $this->pageId,
                'published_at'  => $publishedAt,
                'duration'      => $duration,
                'synced_at'     => now(),
                'raw_data'      => $video,
            ]
        );

        Video::updateOrCreate(
            ['platform' => VideoPlatform::Facebook->value, 'video_id' => $videoId],
            [
                'title'          => $title,
                'description'    => $description ?: null,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $permalink,
                'channel_id'     => $this->pageId,
                'channel_title'  => 'Facebook',
                'published_at'   => $publishedAt,
                'duration'       => $duration,
                'synced_at'      => now(),
                'raw_metadata'   => $video,
            ]
        );
    }

    private function preferredThumbnail(array $video): ?string
    {
        $thumbs = $video['thumbnails']['data'] ?? [];
        foreach ($thumbs as $thumb) {
            if (! empty($thumb['is_preferred']) && ! empty($thumb['uri'])) {
                return $thumb['uri'];
            }
        }

        return $thumbs[0]['uri'] ?? $video['picture'] ?? null;
    }

    /**
     * Extrait un titre court depuis la description (première ligne, 80 caractères max).
     */
    private function extractTitle(string $text): string
    {
        $lines = array_filter(explode("\n", trim($text)));
        $first = reset($lines) ?: 'Vidéo Facebook';

        return mb_strlen($first) > 80 ? mb_substr($first, 0, 77) . '...' : $first;
    }
}
