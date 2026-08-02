<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\FacebookSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job asynchrone de synchronisation des posts Facebook.
 *
 * Dispatch : SyncFacebookPostsJob::dispatch($pageId, $pageAccessToken);
 * Queue    : facebook
 */
class SyncFacebookPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        private readonly string $pageId,
        private readonly int    $maxResults = 20,
    ) {}

    public function handle(): void
    {
        Log::info('SyncFacebookPostsJob: démarrage.', [
            'pageId'    => $this->pageId,
            'maxResults' => $this->maxResults,
        ]);

        // Créer une nouvelle instance du service avec les paramètres du constructeur
        $service = new FacebookSyncService(
            pageId:          $this->pageId,
            pageAccessToken: config('services.facebook.page_access_token'),
            maxResults:      $this->maxResults,
        );

        $count = $service->syncLatestPosts();

        Log::info("SyncFacebookPostsJob: {$count} post(s) synchronisé(s).");
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncFacebookPostsJob: toutes les tentatives ont échoué.', [
            'message' => $e->getMessage(),
        ]);
    }
}
