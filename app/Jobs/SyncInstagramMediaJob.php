<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\InstagramSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job asynchrone de synchronisation des médias Instagram.
 *
 * Dispatch : SyncInstagramMediaJob::dispatch($igUserId, $accessToken);
 * Queue    : instagram
 */
class SyncInstagramMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        private readonly string $igUserId,
        private readonly int    $maxResults = 20,
    ) {}

    public function handle(): void
    {
        Log::info('SyncInstagramMediaJob: démarrage.', [
            'igUserId'    => $this->igUserId,
            'maxResults' => $this->maxResults,
        ]);

        // Créer une nouvelle instance du service avec les paramètres du constructeur
        $service = new InstagramSyncService(
            igUserId:    $this->igUserId,
            accessToken: config('services.instagram.access_token'),
            maxResults:  $this->maxResults,
        );

        $count = $service->syncLatestMedia();

        Log::info("SyncInstagramMediaJob: {$count} média(s) synchronisé(s).");
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncInstagramMediaJob: toutes les tentatives ont échoué.', [
            'message' => $e->getMessage(),
        ]);
    }
}
