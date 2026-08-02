<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncInstagramMediaJob;
use App\Services\InstagramSyncService;
use Illuminate\Console\Command;

/**
 * Commande Artisan de synchronisation des médias Instagram.
 *
 * Utilisation :
 *   php artisan instagram:sync         → dispatch en queue (défaut)
 *   php artisan instagram:sync --now   → exécution synchrone (débogage)
 *   php artisan instagram:sync --max=30
 *
 * Prérequis .env :
 *   INSTAGRAM_USER_ID=votre_ig_user_id
 *   INSTAGRAM_ACCESS_TOKEN=votre_long_lived_token
 */
class SyncInstagramMedia extends Command
{
    protected $signature = 'instagram:sync
        {--max=20  : Nombre de médias à récupérer (max 100)}
        {--now     : Exécuter maintenant sans passer par la queue}';

    protected $description = 'Synchronise les derniers médias Instagram via Instagram Graph API.';

    public function handle(): int
    {
        $igUserId    = config('services.instagram.user_id');
        $accessToken = config('services.instagram.access_token');
        $maxResults  = (int) $this->option('max');

        if (empty($igUserId) || empty($accessToken)) {
            $this->error('Identifiants Instagram manquants. Définissez INSTAGRAM_USER_ID et INSTAGRAM_ACCESS_TOKEN dans .env');
            return self::FAILURE;
        }

        if ($this->option('now')) {
            return $this->runSynchronously($igUserId, $accessToken, $maxResults);
        }

        SyncInstagramMediaJob::dispatch($igUserId, $maxResults)->onQueue('instagram');

        $this->info('Job de synchronisation Instagram dispatchée sur la queue [instagram].');
        $this->line("  IG User ID : {$igUserId}");
        $this->line("  Max médias : {$maxResults}");

        return self::SUCCESS;
    }

    private function runSynchronously(string $igUserId, string $accessToken, int $maxResults): int
    {
        $this->info('Synchronisation directe Instagram (--now) en cours…');

        $service = new InstagramSyncService(
            igUserId:    $igUserId,
            accessToken: $accessToken,
            maxResults:  $maxResults,
        );

        try {
            $count = $service->syncLatestMedia();
            $this->info("✓ {$count} média(s) Instagram synchronisé(s).");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur : ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
