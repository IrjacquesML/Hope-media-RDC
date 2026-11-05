<?php

namespace App\Http\Middleware;

use App\Services\NewsletterService;
use App\Services\SocialSyncManager;
use App\Support\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tâches automatiques sans cron, lancées APRÈS l'envoi de la page au visiteur :
 *  - synchronisation des réseaux (si le planificateur n'a pas tourné depuis 15 minutes) ;
 *  - newsletter hebdomadaire (vérifiée au plus toutes les 5 minutes, envoi par lots).
 *
 * Désactivable dans Paramètres → Système. Sans effet gênant si un cron existe aussi
 * (verrous partagés : jamais deux envois en même temps).
 */
class RunScheduledTasks
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        try {
            if (!$request->isMethod('GET')) {
                return;
            }

            // Adresse publique du site, pour les liens des e-mails envoyés par le cron
            $root = $request->root();
            if (!preg_match('#//(localhost|127\.0\.0\.1)#', $root) && Cache::get(NewsletterService::ROOT_URL_CACHE) !== $root) {
                Cache::forever(NewsletterService::ROOT_URL_CACHE, $root);
            }

            if (!Site::get('sync_without_cron')) {
                return;
            }

            @set_time_limit(300);
            ignore_user_abort(true);

            $this->socialSync();
            $this->newsletter();
        } catch (\Throwable $e) {
            Log::warning('Tâches automatiques (sans cron) interrompues.', ['error' => $e->getMessage()]);
        }
    }

    private function socialSync(): void
    {
        $last = Cache::get(SocialSyncManager::SCHEDULER_HEARTBEAT);
        if ($last && Carbon::parse($last)->gt(now()->subMinutes(15))) {
            return;
        }
        if (Cache::add('social-sync:auto-lock', now()->toIso8601String(), now()->addMinutes(15))) {
            app(SocialSyncManager::class)->syncDue();
        }
    }

    private function newsletter(): void
    {
        if (!Site::mailConfigured()) {
            return;
        }
        if (Cache::add('newsletter:auto-lock', now()->toIso8601String(), now()->addMinutes(5))) {
            app(NewsletterService::class)->runDue();
        }
    }
}
