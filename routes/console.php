<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ---------------------------------------------------------------------------
// Scheduler : synchronisation automatique des contenus sociaux
//
// Quota indicatif :
//   YouTube   : ~3 unités/synchro · quota gratuit 10 000/jour → toutes les 6h
//   Facebook  : 200 appels/heure par token → toutes les 3h
//   Instagram : 200 appels/heure par token → toutes les 3h
//   TikTok    : limite par app (voir dashboard TikTok for Developers) → toutes les 6h
//
// Pour activer le scheduler, ajouter cette ligne au cron du serveur :
//   * * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1
// ---------------------------------------------------------------------------

// ── YouTube ──────────────────────────────────────────────────────────────────
Schedule::command('youtube:sync', [
    '--max=' . config('services.youtube.max_results', 10),
    '--now',
])
    ->everySixHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/youtube-sync.log'));

// ── Facebook ─────────────────────────────────────────────────────────────────
Schedule::command('facebook:sync', [
    '--max=' . config('services.facebook.max_results', 20),
    '--now',
])
    ->everyThreeHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/facebook-sync.log'));

// ── Instagram ─────────────────────────────────────────────────────────────────
Schedule::command('instagram:sync', [
    '--max=' . config('services.instagram.max_results', 20),
    '--now',
])
    ->everyThreeHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/instagram-sync.log'));

// ── TikTok ───────────────────────────────────────────────────────────────────
Schedule::command('tiktok:sync', [
    '--max=' . config('services.tiktok.max_results', 20),
    '--now',
])
    ->everySixHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/tiktok-sync.log'));

