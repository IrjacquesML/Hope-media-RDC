<?php

namespace App\Http\Middleware;

use App\Services\SocialSyncManager;
use App\Support\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Synchronisation des réseaux sans cron : si la tâche planifiée n'a pas tourné depuis
 * 15 minutes, la prochaine visite lance la synchronisation des réseaux « dus »,
 * APRÈS l'envoi de la page au visiteur (aucun ralentissement visible avec PHP-FPM).
 *
 * Désactivable dans Paramètres → Système.
 */
class TriggerSocialSync
{
    private const LOCK = 'social-sync:auto-lock';

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        try {
            if (!$request->isMethod('GET') || !Site::get('sync_without_cron')) {
                return;
            }

            $last = Cache::get(SocialSyncManager::SCHEDULER_HEARTBEAT);
            if ($last && Carbon::parse($last)->gt(now()->subMinutes(15))) {
                return;                                   // le cron (ou une visite récente) s'en est chargé
            }

            // Un seul lancement à la fois, au plus toutes les 15 minutes
            if (!Cache::add(self::LOCK, now()->toIso8601String(), now()->addMinutes(15))) {
                return;
            }

            @set_time_limit(300);
            ignore_user_abort(true);
            app(SocialSyncManager::class)->syncDue();
        } catch (\Throwable $e) {
            Log::warning('Synchronisation automatique (sans cron) interrompue.', ['error' => $e->getMessage()]);
        }
    }
}
