<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncYouTubeVideosJob;
use App\Services\YouTubeApiService;
use Illuminate\Console\Command;

/**
 * Commande Artisan de synchronisation des vidéos YouTube.
 *
 * Utilisation :
 *   php artisan youtube:sync              → dispatch en queue (défaut)
 *   php artisan youtube:sync --now        → exécution synchrone (débogage)
 *   php artisan youtube:sync --channel=UCxxxxx --max=20
 *
 * Options :
 *   --channel : surcharge le YOUTUBE_CHANNEL_ID du .env
 *   --max     : nombre de vidéos à récupérer (1–50, défaut 10)
 *   --now     : exécution directe sans passer par la queue
 */
class SyncYouTubeVideos extends Command
{
    protected $signature = 'youtube:sync
        {--channel= : ID de la chaîne YouTube (surcharge .env)}
        {--max=10   : Nombre de vidéos à récupérer (max 50)}
        {--now      : Exécuter maintenant sans passer par la queue}';

    protected $description = 'Synchronise les dernières vidéos YouTube depuis l\'API Data v3.';

    // -------------------------------------------------------------------------

    public function handle(): int
    {
        $channelId  = $this->option('channel') ?: config('services.youtube.channel_id');
        $maxResults = (int) $this->option('max');

        // --- Validation des options ----------------------------------------
        if (empty($channelId)) {
            $this->error('Channel ID manquant. Définissez YOUTUBE_CHANNEL_ID dans .env ou utilisez --channel=...');
            return self::FAILURE;
        }

        if ($maxResults < 1 || $maxResults > 50) {
            $this->error('--max doit être compris entre 1 et 50.');
            return self::FAILURE;
        }

        // --- Exécution --------------------------------------------------------
        if ($this->option('now')) {
            return $this->runSynchronously($channelId, $maxResults);
        }

        // Mode par défaut : dispatch en queue pour ne pas bloquer
        SyncYouTubeVideosJob::dispatch($channelId, $maxResults)
            ->onQueue('youtube');

        $this->info("Job de synchronisation YouTube dispatchée sur la queue [youtube].");
        $this->line("  Chaîne    : {$channelId}");
        $this->line("  Max vidéos: {$maxResults}");

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    /**
     * Exécution directe (sans queue), utile pour les tests et le débogage.
     */
    private function runSynchronously(string $channelId, int $maxResults): int
    {
        $this->info("Synchronisation directe (--now) en cours…");

        // On construit le service manuellement pour pouvoir surcharger le channelId
        $service = new YouTubeApiService(
            apiKey:     config('services.youtube.api_key'),
            channelId:  $channelId,
            maxResults: $maxResults,
        );

        try {
            $count = $service->syncLatestVideos();
            $this->info("✓ {$count} vidéo(s) synchronisée(s) avec succès.");
            return self::SUCCESS;

        } catch (\RuntimeException $e) {
            // Quota dépassé : message clair, pas de stack trace
            if ($e->getCode() === 429) {
                $this->warn('Quota YouTube journalier dépassé. Réessayez demain.');
                return self::FAILURE;
            }

            $this->error("Erreur : {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
