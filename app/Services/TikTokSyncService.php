<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VideoPlatform;
use App\Models\SocialFeed;
use App\Models\Video;
use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de synchronisation automatique des vidéos d'un compte TikTok.
 *
 * Utilise TikTok API v2 (Display API) :
 *   POST https://open.tiktokapis.com/v2/video/list/?fields=…   corps JSON : {"max_count": 20}
 *   GET  https://open.tiktokapis.com/v2/user/info/?fields=…
 *
 * Prérequis :
 *   1. Créer une app sur https://developers.tiktok.com/ (produit « Login Kit »,
 *      autorisations user.info.basic et video.list).
 *   2. Faire autoriser l'app par le compte TikTok (OAuth 2.0) pour obtenir
 *      access_token, refresh_token et open_id.
 *   3. Saisir ces valeurs dans Admin → Réseaux sociaux.
 *
 * Gestion du token :
 *   L'access_token expire après 24 h. En cas de refus (401 ou access_token_invalid),
 *   le service le renouvelle avec le refresh_token et appelle $onTokenRefreshed,
 *   qui enregistre les nouveaux jetons en base (voir SocialSyncManager).
 *
 * Documentation : https://developers.tiktok.com/doc/tiktok-api-v2-video-list
 */
final class TikTokSyncService
{
    private const API_BASE       = 'https://open.tiktokapis.com/v2';
    private const TOKEN_ENDPOINT = 'https://open.tiktokapis.com/v2/oauth/token/';
    private const VIDEO_FIELDS   = 'id,title,video_description,cover_image_url,share_url,create_time,duration,like_count,comment_count,view_count,share_count';

    public function __construct(
        private readonly string $clientKey,
        private readonly string $clientSecret,
        private string          $accessToken,
        private readonly string $openId,
        private readonly int    $maxResults = 20,
        private ?string         $refreshToken = null,
        /** @var Closure(string $accessToken, ?string $refreshToken): void|null */
        private readonly ?Closure $onTokenRefreshed = null,
    ) {
        $this->refreshToken ??= config('services.tiktok.refresh_token');
    }

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les dernières vidéos du compte TikTok et les persiste en base.
     *
     * @return int Nombre de vidéos créées ou mises à jour.
     */
    public function syncLatestVideos(): int
    {
        $data = $this->call(fn () => Http::timeout(15)->withToken($this->accessToken)
            ->post(self::API_BASE . '/video/list/?fields=' . self::VIDEO_FIELDS, [
                'max_count' => min(max($this->maxResults, 1), 20), // TikTok : 20 au maximum par page
            ]));

        $synced = 0;
        foreach ($data['videos'] ?? [] as $item) {
            $this->upsertVideo($item);
            $synced++;
        }

        return $synced;
    }

    /**
     * Vérifie les jetons, sans rien enregistrer.
     *
     * @return string Nom affiché du compte TikTok.
     */
    public function testConnection(): string
    {
        $data = $this->call(fn () => Http::timeout(15)->withToken($this->accessToken)
            ->get(self::API_BASE . '/user/info/', ['fields' => 'open_id,display_name']));

        return $data['user']['display_name'] ?? throw new \RuntimeException('Compte TikTok introuvable.');
    }

    // -------------------------------------------------------------------------
    // Méthodes privées
    // -------------------------------------------------------------------------

    /**
     * Exécute un appel ; si le jeton est refusé, le renouvelle et réessaie une fois.
     *
     * @param  Closure(): Response $send
     */
    private function call(Closure $send): array
    {
        try {
            $response = $send();
            if ($this->tokenRejected($response)) {
                Log::info('TikTokSyncService: token expiré, tentative de renouvellement.');
                $this->refreshAccessToken();
                $response = $send();
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('TikTokSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Impossible de joindre TikTok. Vérifiez la connexion internet du serveur.', 0, $e);
        }

        $errorCode = $response->json('error.code');
        if ($response->failed() || ($errorCode && $errorCode !== 'ok')) {
            $error = $response->json('error.message') ?: ($errorCode ?: $response->body());
            Log::error('TikTokSyncService: erreur API.', ['error' => $error]);
            throw new \RuntimeException("TikTok : {$error}");
        }

        return $response->json('data') ?? [];
    }

    private function tokenRejected(Response $response): bool
    {
        return $response->status() === 401
            || in_array($response->json('error.code'), ['access_token_invalid', 'access_token_expired'], true);
    }

    /**
     * Renouvelle l'access_token avec le refresh_token, puis signale les nouveaux jetons.
     *
     * @throws \RuntimeException Si le renouvellement échoue.
     */
    private function refreshAccessToken(): void
    {
        if (empty($this->refreshToken)) {
            throw new \RuntimeException('TikTok : l’Access Token a expiré et aucun Refresh Token n’est enregistré. Saisissez-en un dans l’admin.');
        }

        $response = Http::timeout(10)->asForm()->post(self::TOKEN_ENDPOINT, [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
        ]);

        if ($response->failed() || empty($response->json('access_token'))) {
            throw new \RuntimeException('TikTok : le renouvellement du jeton a échoué. Refaites l’autorisation du compte TikTok et saisissez les nouveaux jetons.');
        }

        $this->accessToken  = $response->json('access_token');
        $this->refreshToken = $response->json('refresh_token') ?: $this->refreshToken;

        if ($this->onTokenRefreshed) {
            ($this->onTokenRefreshed)($this->accessToken, $this->refreshToken);
        }

        Log::info('TikTokSyncService: jeton renouvelé et enregistré.');
    }

    /**
     * Crée ou met à jour un enregistrement Video à partir d'une vidéo TikTok.
     */
    private function upsertVideo(array $item): void
    {
        $videoId     = (string) ($item['id'] ?? '');
        $title       = ($item['title'] ?? '') ?: (($item['video_description'] ?? '') ?: 'Vidéo TikTok');
        $thumbnail   = $item['cover_image_url'] ?? null;
        $shareUrl    = $item['share_url'] ?? null;
        $publishedAt = isset($item['create_time']) ? Carbon::createFromTimestamp($item['create_time']) : null;

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::TikTok->value, 'platform_id' => $videoId],
            [
                'media_type'     => 'video',
                'title'          => mb_substr($title, 0, 255),
                'body'           => $item['video_description'] ?? null,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $shareUrl,
                'source_id'      => $this->openId ?: null,
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
                'channel_id'     => $this->openId ?: null,
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
