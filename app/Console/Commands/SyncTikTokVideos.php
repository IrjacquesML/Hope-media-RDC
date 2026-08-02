<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncTikTokVideosJob;
use App\Services\TikTokSyncService;
use Illuminate\Console\Command;

/**
 * Commande Artisan de synchronisation des vidéos TikTok.
 *
 * Utilisation :
 *   php artisan tiktok:sync          → dispatch en queue (défaut)
 *   php artisan tiktok:sync --now    → exécution synchrone (débogage)
 *   php artisan tiktok:sync --max=20
 *
 * Prérequis .env :
 *   TIKTOK_CLIENT_KEY=...
 *   TIKTOK_CLIENT_SECRET=...
 *   TIKTOK_ACCESS_TOKEN=...   (token OAuth 2.0 du propriétaire du compte)
 *   TIKTOK_OPEN_ID=...        (open_id obtenu lors du flux OAuth)
 */
class SyncTikTokVideos extends Command
{
    protected $signature = 'tiktok:sync
        {--max=20  : Nombre de vidéos à récupérer (max 20 par appel TikTok)}
        {--now     : Exécuter maintenant sans passer par la queue}';

    protected $description = 'Synchronise les dernières vidéos TikTok via TikTok Content API v2.';

    public function handle(): int
    {
        $clientKey    = config('services.tiktok.client_key');
        $clientSecret = config('services.tiktok.client_secret');
        $accessToken  = config('services.tiktok.access_token');
        $openId       = config('services.tiktok.open_id');
        $maxResults   = (int) $this->option('max');

        if (empty($clientKey) || empty($accessToken) || empty($openId)) {
            $this->error(
                'Identifiants TikTok manquants. Définissez TIKTOK_CLIENT_KEY, '
                . 'TIKTOK_ACCESS_TOKEN et TIKTOK_OPEN_ID dans .env'
            );
            $this->line('');
            $this->line('Pour obtenir ces identifiants :');
            $this->line('  1. Créez une app sur https://developers.tiktok.com/');
            $this->line('  2. Activez Login Kit + scopes video.list + user.info.basic');
            $this->line('  3. Faites passer le compte TikTok par le flux OAuth 2.0');
            return self::FAILURE;
        }

        if ($this->option('now')) {
            return $this->runSynchronously($clientKey, $clientSecret, $accessToken, $openId, $maxResults);
        }

        SyncTikTokVideosJob::dispatch($openId, $maxResults)->onQueue('tiktok');

        $this->info('Job de synchronisation TikTok dispatchée sur la queue [tiktok].');
        $this->line("  Open ID    : {$openId}");
        $this->line("  Max vidéos : {$maxResults}");

        return self::SUCCESS;
    }

    private function runSynchronously(
        string $clientKey,
        string $clientSecret,
        string $accessToken,
        string $openId,
        int    $maxResults
    ): int {
        $this->info('Synchronisation directe TikTok (--now) en cours…');

        $service = new TikTokSyncService(
            clientKey:    $clientKey,
            clientSecret: $clientSecret,
            accessToken:  $accessToken,
            openId:       $openId,
            maxResults:   $maxResults,
        );

        try {
            $count = $service->syncLatestVideos();
            $this->info("✓ {$count} vidéo(s) TikTok synchronisée(s).");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur : ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
