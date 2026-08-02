<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncFacebookPostsJob;
use App\Services\FacebookSyncService;
use Illuminate\Console\Command;

/**
 * Commande Artisan de synchronisation des posts Facebook.
 *
 * Utilisation :
 *   php artisan facebook:sync          → dispatch en queue (défaut)
 *   php artisan facebook:sync --now    → exécution synchrone (débogage)
 *   php artisan facebook:sync --max=50 → nombre de posts à récupérer
 *
 * Prérequis .env :
 *   FACEBOOK_PAGE_ID=votre_page_id
 *   FACEBOOK_PAGE_ACCESS_TOKEN=votre_token
 */
class SyncFacebookPosts extends Command
{
    protected $signature = 'facebook:sync
        {--max=20  : Nombre de posts à récupérer (max 100)}
        {--now     : Exécuter maintenant sans passer par la queue}';

    protected $description = 'Synchronise les derniers posts de la Page Facebook via Graph API.';

    public function handle(): int
    {
        $pageId      = config('services.facebook.page_id');
        $accessToken = config('services.facebook.page_access_token');
        $maxResults  = (int) $this->option('max');

        if (empty($pageId) || empty($accessToken)) {
            $this->error('Identifiants Facebook manquants. Définissez FACEBOOK_PAGE_ID et FACEBOOK_PAGE_ACCESS_TOKEN dans .env');
            return self::FAILURE;
        }

        if ($this->option('now')) {
            return $this->runSynchronously($pageId, $accessToken, $maxResults);
        }

        SyncFacebookPostsJob::dispatch($pageId, $maxResults)->onQueue('facebook');

        $this->info('Job de synchronisation Facebook dispatchée sur la queue [facebook].');
        $this->line("  Page ID   : {$pageId}");
        $this->line("  Max posts : {$maxResults}");

        return self::SUCCESS;
    }

    private function runSynchronously(string $pageId, string $accessToken, int $maxResults): int
    {
        $this->info('Synchronisation directe Facebook (--now) en cours…');

        $service = new FacebookSyncService(
            pageId:          $pageId,
            pageAccessToken: $accessToken,
            maxResults:      $maxResults,
        );

        try {
            $count = $service->syncLatestPosts();
            $this->info("✓ {$count} post(s) Facebook synchronisé(s).");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur : ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
