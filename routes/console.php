<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ---------------------------------------------------------------------------
// Synchronisation automatique des réseaux sociaux (YouTube, Facebook, Instagram, TikTok, X).
// La fréquence de chaque réseau se règle dans Admin → Réseaux sociaux ; cette tâche vérifie
// toutes les 15 minutes quels réseaux sont à synchroniser.
// Fréquences conseillées : YouTube 6 h (quota 10 000 unités/jour), Facebook et Instagram 3 h,
// TikTok 6 h, X 12 h (l'API X limite fortement le nombre de requêtes).
//
// Pour activer le planificateur, ajoutez cette ligne au cron du serveur (crontab -e) :
//   * * * * * cd /chemin/du/site && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('social:sync')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/social-sync.log'));
