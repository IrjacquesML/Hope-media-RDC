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
 * Service de synchronisation automatique des vidéos d'un compte TikTok.
 *
 * Utilise TikTok Content API v2 :
 *   POST https://open.tiktokapis.com/v2/video/list/
 *
 * Prérequis :
 *   1. Créer une app sur https://developers.tiktok.com/
 *   2. Activer le produit "Login Kit" + les scopes : video.list, user.info.basic
 *   3. Faire passer le propriétaire du compte TikTok par le flux OAuth 2.0
 *      (Login Kit) pour obtenir un access_token et un open_id.
 *   4. Variables .env :
 *        TIKTOK_CLIENT_KEY     = votre Client Key
 *        TIKTOK_CLIENT_SECRET  = votre Client Secret
 *        TIKTOK_ACCESS_TOKEN   = access_token OAuth de l'utilisateur
 *        TIKTOK_OPEN_ID        = open_id de l'utilisateur
 *
 * Gestion du token :
 *   Les access_tokens TikTok expirent après 24 h. Ce service tente de
 *   renouveler automatiquement le token via le refresh_token si TIKTOK_REFRESH_TOKEN
 *   est défini dans le .env. En production, implémentez un stockage persistant
 *   du refresh_token (base de données recommandée).
 *
 * Documentation : https://developers.tiktok.com/doc/tiktok-api-v2-video-list
 */
final class TikTokSyncService
{
    private const API_BASE        = 'https://open.tiktokapis.com/v2';
    private const TOKEN_ENDPOINT  = 'https://open.tiktokapis.com/v2/oauth/token/';

    public function __construct(
        private readonly string $clientKey,
        private readonly string $clientSecret,
        private string          $accessToken,
        private readonly string $openId,
        private readonly int    $maxResults = 20,
    ) {}

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les dernières vidéos du compte TikTok et les persiste en base.
     *
     * @return int Nombre de vidéos créées ou mises à jour.
     *
     * @throws \RuntimeException Si l'authentification échoue.
     */
    public function syncLatestVideos(): int
    {
        $items = $this->fetchVideos();

        $synced = 0;
        foreach ($items as $item) {
            $this->upsertVideo($item);
            $synced++;
        }

        return $synced;
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — appels API
    // -------------------------------------------------------------------------

    /**
     * Récupère la liste des vidéos via l'endpoint /v2/video/list/.
     *
     * @return array<int, array>
     */
    private function fetchVideos(): array
    {
        try {
            $response = Http::timeout(15)
                ->withToken($this->accessToken)
                ->post(self::API_BASE . '/video/list/', [
                    'fields' => 'id,title,video_description,cover_image_url,'
                        . 'share_url,create_time,duration,like_count,'
                        . 'comment_count,view_count,share_count',
                    'max_count' => min($this->maxResults, 20), // TikTok limite à 20 par page
                ]);

            // 401 → token expiré, on tente le renouvellement
            if ($response->status() === 401) {
                Log::info('TikTokSyncService: token expiré, tentative de renouvellement.');
                $this->refreshAccessToken();

                // Deuxième tentative après renouvellement
                $response = Http::timeout(15)
                    ->withToken($this->accessToken)
                    ->post(self::API_BASE . '/video/list/', [
                        'fields' => 'id,title,video_description,cover_image_url,'
                            . 'share_url,create_time,duration,like_count,'
                            . 'comment_count,view_count,share_count',
                        'max_count' => min($this->maxResults, 20),
                    ]);
            }

            if ($response->failed()) {
                $error = $response->json('error.message') ?? $response->body();
                Log::error('TikTokSyncService: erreur API.', ['error' => $error]);
                throw new \RuntimeException("TikTok API error: {$error}");
            }

            return $response->json('data.videos') ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('TikTokSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Renouvelle le token d'accès via le refresh_token stocké en .env.
     * En production, persistez le nouveau token en base de données.
     *
     * @throws \RuntimeException Si le renouvellement échoue.
     */
    private function refreshAccessToken(): void
    {
        $refreshToken = config('services.tiktok.refresh_token');
        if (empty($refreshToken)) {
            throw new \RuntimeException(
                'TikTok: access_token expiré et TIKTOK_REFRESH_TOKEN non défini. '
                    . 'Relancez le flux OAuth pour obtenir un nouveau token.'
            );
        }

        $response = Http::timeout(10)->asForm()->post(self::TOKEN_ENDPOINT, [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->failed() || empty($response->json('access_token'))) {
            throw new \RuntimeException(
                'TikTok: échec du renouvellement du token. Relancez le flux OAuth.'
            );
        }

        $this->accessToken = $response->json('access_token');

        // Log pour rappeler à l'administrateur de mettre à jour le .env
        Log::warning('TikTokSyncService: token renouvelé. Mettez à jour TIKTOK_ACCESS_TOKEN dans .env.', [
            'new_token_prefix' => substr($this->accessToken, 0, 10) . '...',
        ]);
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — persistance
    // -------------------------------------------------------------------------

    /**
     * Crée ou met à jour un enregistrement Video à partir d'une vidéo TikTok.
     */
    private function upsertVideo(array $item): void
    {
        $videoId     = (string) ($item['id'] ?? '');
        $title       = $item['title'] ?: ($item['video_description'] ?? 'Vidéo TikTok');
        $thumbnail   = $item['cover_image_url'] ?? null;
        $shareUrl    = $item['share_url'] ?? null;
        $publishedAt = isset($item['create_time'])
            ? Carbon::createFromTimestamp($item['create_time'])
            : null;

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::TikTok->value, 'platform_id' => $videoId],
            [
                'media_type'     => 'video',
                'title'          => mb_substr($title, 0, 255),
                'body'           => $item['video_description'] ?? null,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $shareUrl,
                'source_id'      => $this->openId,
                'published_at'   => $publishedAt,
                'duration'       => isset($item['duration']) ? (string) $item['duration'] : null,
                'view_count'     => (int) ($item['view_count'] ?? 0),
                'likes_count'    => (int) ($item['like_count'] ?? 0),
                'comments_count' => (int) ($item['comment_count'] ?? 0),
                'shares_count'   => (int) ($item['share_count'] ?? 0),
                'synced_at'      => now(),
                'raw_data'       => $item,
            ]
        );

        Video::updateOrCreate(
            ['platform' => VideoPlatform::TikTok->value, 'video_id' => $videoId],
            [
                'title'          => mb_substr($title, 0, 255),
                'description'    => $item['video_description'] ?? null,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $shareUrl,
                'channel_id'     => $this->openId,
                'channel_title'  => 'TikTok',
                'published_at'   => $publishedAt,
                'duration'       => isset($item['duration']) ? (string) $item['duration'] : null,
                'view_count'     => (int) ($item['view_count'] ?? 0),
                'likes_count'    => (int) ($item['like_count'] ?? 0),
                'comments_count' => (int) ($item['comment_count'] ?? 0),
                'synced_at'      => now(),
                'raw_metadata'   => $item,
            ]
        );
    }
}
