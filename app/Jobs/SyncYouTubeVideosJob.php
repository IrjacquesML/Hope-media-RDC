<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\YouTubeApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job asynchrone de synchronisation des vidéos YouTube.
 *
 * Ce job est dispatchable sur n'importe quel driver de queue (database, Redis…).
 * Il délègue tout le travail à YouTubeApiService pour rester léger.
 *
 * Comportement en cas d'échec :
 *  - Quota dépassé (code 429) → le job est abandonné sans re-tentative
 *    pour ne pas consommer davantage de quota.
 *  - Toute autre erreur → jusqu'à $tries tentatives avec backoff exponentiel.
 */
class SyncYouTubeVideosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Nombre de tentatives avant de marquer le job comme échoué. */
    public int $tries = 3;

    /** Délai (secondes) avant la première tentative après un échec. */
    public int $backoff = 60;

    /** Timeout total du job (secondes). Évite un blocage infini. */
    public int $timeout = 120;

    // -------------------------------------------------------------------------

    public function __construct(
        private readonly string $channelId,
        private readonly int    $maxResults = 10,
    ) {}

    // -------------------------------------------------------------------------

    public function handle(): void
    {
        Log::info('SyncYouTubeVideosJob: démarrage.', [
            'channel'    => $this->channelId,
            'maxResults' => $this->maxResults,
        ]);

        // Créer une nouvelle instance du service avec les paramètres du constructeur
        $service = new YouTubeApiService(
            apiKey:     config('services.youtube.api_key'),
            channelId:  $this->channelId,
            maxResults: $this->maxResults,
        );

        try {
            $count = $service->syncLatestVideos();

            Log::info("SyncYouTubeVideosJob: {$count} vidéo(s) synchronisée(s).", [
                'channel' => $this->channelId,
            ]);

        } catch (\RuntimeException $e) {
            // Quota dépassé → on abandonne proprement sans réessayer aujourd'hui
            if ($e->getCode() === 429) {
                $this->fail($e);
                return;
            }

            // Autre erreur métier → on laisse le système réessayer ($tries)
            Log::error('SyncYouTubeVideosJob: erreur inattendue.', [
                'message' => $e->getMessage(),
            ]);

            throw $e; // relance pour que la queue le marque failed après $tries
        }
    }

    /**
     * Appelé quand toutes les tentatives sont épuisées.
     * Idéal pour envoyer une alerte (Slack, e-mail, etc.).
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('SyncYouTubeVideosJob: échec définitif.', [
            'channel' => $this->channelId,
            'error'   => $exception->getMessage(),
        ]);

        // TODO : dispatcher un event ou envoyer une notification admin ici
    }
}
