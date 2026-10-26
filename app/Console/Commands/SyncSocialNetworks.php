<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SocialSyncManager;
use App\Support\SocialPlatforms;
use Illuminate\Console\Command;

/**
 * Synchronisation des réseaux sociaux configurés dans l'admin.
 *
 *   php artisan social:sync              → réseaux activés dont l'intervalle est écoulé (planificateur)
 *   php artisan social:sync youtube      → un réseau précis, tout de suite
 */
class SyncSocialNetworks extends Command
{
    protected $signature = 'social:sync {platform? : youtube, facebook, instagram, tiktok ou x}';

    protected $description = 'Synchronise les vidéos des réseaux sociaux configurés dans l\'admin.';

    public function handle(SocialSyncManager $manager): int
    {
        $platform = $this->argument('platform');

        if ($platform) {
            if (! in_array($platform, SocialPlatforms::keys(), true)) {
                $this->error('Réseau inconnu. Choix possibles : ' . implode(', ', SocialPlatforms::keys()));
                return self::FAILURE;
            }
            try {
                $count = $manager->sync($platform);
                $this->info("{$platform} : {$count} vidéo(s) synchronisée(s).");
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("{$platform} : {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        $results = $manager->syncDue();
        if ($results === []) {
            $this->line('Aucun réseau à synchroniser pour le moment.');
        }
        foreach ($results as $name => $result) {
            is_int($result)
                ? $this->info("{$name} : {$result} vidéo(s) synchronisée(s).")
                : $this->error("{$name} : {$result}");
        }

        return self::SUCCESS;
    }
}
