<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\TikTokSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job asynchrone de synchronisation des vidéos TikTok.
 *
 * Dispatch : SyncTikTokVideosJob::dispatch($openId, $accessToken);
 * Queue    : tiktok
 */
class SyncTikTokVideosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        private readonly string $openId,
        private readonly int    $maxResults = 20,
    ) {}

    public function handle(): void
    {
        Log::info('SyncTikTokVideosJob: démarrage.', [
            'openId'    => $this->openId,
            'maxResults' => $this->maxResults,
        ]);

        // Créer une nouvelle instance du service avec les paramètres du constructeur
        $service = new TikTokSyncService(
            clientKey:    config('services.tiktok.client_key'),
            clientSecret: config('services.tiktok.client_secret'),
            accessToken:  config('services.tiktok.access_token'),
            openId:       $this->openId,
            maxResults:   $this->maxResults,
        );

        $count = $service->syncLatestVideos();

        Log::info("SyncTikTokVideosJob: {$count} vidéo(s) TikTok synchronisée(s).");
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncTikTokVideosJob: toutes les tentatives ont échoué.', [
            'message' => $e->getMessage(),
        ]);
    }
}
