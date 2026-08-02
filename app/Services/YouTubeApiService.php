<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VideoPlatform;
use App\Models\SocialFeed;
use App\Models\Video;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'intégration YouTube Data API v3.
 *
 * Responsabilités :
 *  1. Récupérer les dernières vidéos d'une chaîne via l'API YouTube.
 *  2. Persister (créer ou mettre à jour) les enregistrements Video en base.
 *  3. Gérer les erreurs réseau, les erreurs API et les dépassements de quota.
 *
 * NON responsabilité de ce service :
 *  - Envoi d'e-mail ou de notifications (déléguer à un Listener/Event).
 *  - Logique de présentation ou de routing.
 */
final class YouTubeApiService
{
    private const BASE_URL = 'https://www.googleapis.com/youtube/v3';

    // Code d'erreur renvoyé par YouTube quand le quota journalier est épuisé.
    private const QUOTA_EXCEEDED_REASON = 'quotaExceeded';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $channelId,
        /** Nombre maximum de vidéos récupérées par appel (max autorisé : 50). */
        private readonly int $maxResults = 10,
    ) {}

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les $maxResults dernières vidéos de la chaîne configurée
     * et les persiste en base de données.
     *
     * @return int Nombre de vidéos créées ou mises à jour.
     *
     * @throws \RuntimeException Si le quota est dépassé (ne pas re-planifier immédiatement).
     * @throws \Illuminate\Http\Client\ConnectionException Si l'API est injoignable.
     */
    public function syncLatestVideos(): int
    {
        // --- Étape 1 : récupérer la playlist "uploads" de la chaîne -------
        $playlistId = $this->fetchUploadsPlaylistId();

        // --- Étape 2 : récupérer les items de la playlist -----------------
        $videoIds = $this->fetchVideoIdsFromPlaylist($playlistId);

        if (empty($videoIds)) {
            return 0;
        }

        // --- Étape 3 : récupérer les détails complets de chaque vidéo -----
        $items = $this->fetchVideoDetails($videoIds);

        // --- Étape 4 : persister en base ----------------------------------
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
     * Récupère l'ID de la playlist "uploads" de la chaîne.
     * Supporte les vrais IDs (UCxxxxx) et les handles YouTube (@monhandle).
     * Coût quota : 1 unité (lecture de channels.list).
     */
    private function fetchUploadsPlaylistId(): string
    {
        // Les handles commencent par @ ; on utilise forHandle au lieu de id
        $isHandle = str_starts_with($this->channelId, '@');

        $params = ['part' => 'contentDetails'];

        if ($isHandle) {
            $params['forHandle'] = ltrim($this->channelId, '@');
        } else {
            $params['id'] = $this->channelId;
        }

        $response = $this->get('/channels', $params);

        $playlistId = $response['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;

        if (! $playlistId) {
            throw new \RuntimeException(
                "Impossible de trouver la playlist 'uploads' pour la chaîne [{$this->channelId}]."
            );
        }

        Log::info('YouTubeApiService: playlist uploads trouvée.', [
            'channel'    => $this->channelId,
            'playlistId' => $playlistId,
        ]);

        return $playlistId;
    }

    /**
     * Récupère les identifiants des dernières vidéos depuis la playlist uploads.
     * Coût quota : 1 unité (lecture de playlistItems.list).
     *
     * @return string[]
     */
    private function fetchVideoIdsFromPlaylist(string $playlistId): array
    {
        try {
            $response = $this->get('/playlistItems', [
                'part'       => 'contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => $this->maxResults,
            ]);
        } catch (\RuntimeException $e) {
            // playlistNotFound = playlist vide ou vidéo pas encore indexée (très récente)
            if (str_contains($e->getMessage(), 'playlistNotFound') || str_contains($e->getMessage(), '404')) {
                Log::warning('YouTubeApiService: playlist uploads vide ou pas encore indexée.', [
                    'playlistId' => $playlistId,
                    'error'      => $e->getMessage(),
                ]);
                return [];
            }
            throw $e;
        } catch (RequestException $e) {
            if ($e->response->status() === 404) {
                Log::warning('YouTubeApiService: playlist uploads vide ou pas encore indexée.', [
                    'playlistId' => $playlistId,
                ]);
                return [];
            }
            throw $e;
        }

        return array_map(
            fn (array $item): string => $item['contentDetails']['videoId'],
            $response['items'] ?? []
        );
    }

    /**
     * Récupère les métadonnées complètes d'une liste d'IDs vidéo.
     * Coût quota : 1 unité (lecture de videos.list).
     *
     * @param  string[] $videoIds
     * @return array<int, array>
     */
    private function fetchVideoDetails(array $videoIds): array
    {
        $response = $this->get('/videos', [
            'part' => 'snippet,contentDetails,statistics',
            'id'   => implode(',', $videoIds),
        ]);

        return $response['items'] ?? [];
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — persistance
    // -------------------------------------------------------------------------

    /**
     * Crée ou met à jour un enregistrement SocialFeed à partir d'un item YouTube.
     */
    private function upsertVideo(array $item): void
    {
        $snippet    = $item['snippet'] ?? [];
        $details    = $item['contentDetails'] ?? [];
        $statistics = $item['statistics'] ?? [];

        // Sélectionne la meilleure miniature disponible
        $thumbnail = $this->selectBestThumbnail($snippet['thumbnails'] ?? []);

        SocialFeed::updateOrCreate(
            // Clé d'unicité : (platform, platform_id)
            ['platform' => VideoPlatform::YouTube->value, 'platform_id' => $item['id']],
            [
                'media_type'     => 'video',
                'title'          => $snippet['title'] ?? 'Sans titre',
                'body'           => $snippet['description'] ?? null,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => 'https://www.youtube.com/watch?v=' . $item['id'],
                'source_id'      => $snippet['channelId'] ?? null,
                'source_name'    => $snippet['channelTitle'] ?? null,
                'published_at'   => isset($snippet['publishedAt'])
                    ? Carbon::parse($snippet['publishedAt'])
                    : null,
                'duration'       => $details['duration'] ?? null,   // ex: "PT4M13S"
                'view_count'     => (int) ($statistics['viewCount'] ?? 0),
                'likes_count'    => (int) ($statistics['likeCount'] ?? 0),
                'comments_count' => (int) ($statistics['commentCount'] ?? 0),
                'synced_at'      => now(),
                'raw_data'       => $item,
            ]
        );

        // Compatibilité UI actuelle: les pages publiques lisent encore le modèle Video.
        Video::updateOrCreate(
            ['platform' => VideoPlatform::YouTube->value, 'video_id' => $item['id']],
            [
                'title'          => $snippet['title'] ?? 'Sans titre',
                'description'    => $snippet['description'] ?? null,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => 'https://www.youtube.com/watch?v=' . $item['id'],
                'channel_id'     => $snippet['channelId'] ?? null,
                'channel_title'  => $snippet['channelTitle'] ?? null,
                'published_at'   => isset($snippet['publishedAt'])
                    ? Carbon::parse($snippet['publishedAt'])
                    : null,
                'duration'       => $details['duration'] ?? null,
                'view_count'     => (int) ($statistics['viewCount'] ?? 0),
                'likes_count'    => (int) ($statistics['likeCount'] ?? 0),
                'comments_count' => (int) ($statistics['commentCount'] ?? 0),
                'synced_at'      => now(),
                'raw_metadata'   => $item,
            ]
        );
    }

    /**
     * Sélectionne la meilleure miniature disponible (maxres > standard > high > medium > default).
     */
    private function selectBestThumbnail(array $thumbnails): ?string
    {
        foreach (['maxres', 'standard', 'high', 'medium', 'default'] as $quality) {
            if (isset($thumbnails[$quality]['url'])) {
                return $thumbnails[$quality]['url'];
            }
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — HTTP
    // -------------------------------------------------------------------------

    /**
     * Effectue un GET vers l'API YouTube et retourne le corps JSON décodé.
     *
     * @throws \RuntimeException            Si le quota est dépassé.
     * @throws \Illuminate\Http\Client\ConnectionException Si l'API est injoignable.
     * @throws \Illuminate\Http\Client\RequestException   Pour toute autre erreur HTTP.
     */
    private function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 500, throw: false)   // ne pas lancer d'exception auto après retry
                ->get(self::BASE_URL . $endpoint, array_merge($params, [
                    'key' => $this->apiKey,
                ]));

            // YouTube répond 200 même en cas d'erreur métier — on inspecte le corps
            if ($response->failed()) {
                $this->handleApiError($response->json() ?? []);
            }

            return $response->json() ?? [];

        } catch (ConnectionException $e) {
            Log::error('YouTubeApiService: API injoignable.', ['message' => $e->getMessage()]);
            throw $e;

        } catch (RequestException $e) {
            Log::error('YouTubeApiService: erreur HTTP.', [
                'status'  => $e->response->status(),
                'body'    => $e->response->body(),
            ]);
            throw $e;
        }
    }

    /**
     * Inspecte la réponse d'erreur de YouTube et lève l'exception appropriée.
     *
     * @throws \RuntimeException Si le quota est dépassé (traitement spécial côté appelant).
     * @throws \RuntimeException Pour toute autre erreur API.
     */
    private function handleApiError(array $body): void
    {
        $error  = $body['error'] ?? [];
        $reason = $error['errors'][0]['reason'] ?? 'unknown';
        $status = $error['code'] ?? 0;
        $msg    = $error['message'] ?? 'Erreur inconnue';

        if ($reason === self::QUOTA_EXCEEDED_REASON) {
            Log::warning('YouTubeApiService: quota journalier dépassé. La synchro reprendra demain.');
            throw new \RuntimeException("YouTube API quota dépassé : {$msg}", 429);
        }

        Log::error('YouTubeApiService: erreur API YouTube.', [
            'status' => $status,
            'reason' => $reason,
            'msg'    => $msg,
        ]);

        throw new \RuntimeException("YouTube API error [{$status}] {$reason}: {$msg}");
    }
}
