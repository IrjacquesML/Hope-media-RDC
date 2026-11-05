<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\NewsletterDigest;
use App\Mail\NewsletterWelcome;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use App\Models\News;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Video;
use App\Support\Site;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Newsletter automatique hebdomadaire.
 *
 *  - Au jour et à l'heure réglés (Paramètres → Newsletter), une « campagne » est créée avec
 *    les contenus publiés depuis le dernier envoi. Sans nouveauté, rien n'est envoyé.
 *  - Les e-mails partent par lots (BATCH par passage) : les passages suivants continuent
 *    là où le précédent s'est arrêté. Déclenché par le cron (newsletter:send) ou par les visites.
 */
final class NewsletterService
{
    public const BATCH = 40;
    public const ROOT_URL_CACHE = 'site.public-root-url';
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /** Créneau hebdomadaire le plus récent déjà atteint (heure de Kinshasa). */
    public function currentSlot(?Carbon $now = null): Carbon
    {
        $now = ($now ?? now())->copy()->timezone('Africa/Kinshasa');
        $dayIndex = array_search(Site::get('newsletter_day', 'friday'), self::DAYS, true);
        $slot = $now->copy()->startOfWeek(Carbon::MONDAY)
            ->addDays($dayIndex === false ? 4 : $dayIndex)
            ->setTime((int) Site::get('newsletter_hour', 9), 0);

        if ($slot->gt($now)) {
            $slot->subWeek();
        }

        return $slot->timezone(config('app.timezone'));
    }

    /** Prochain envoi prévu. */
    public function nextSlot(): Carbon
    {
        return $this->currentSlot()->copy()->addWeek();
    }

    /**
     * Point d'entrée du planificateur : continue l'envoi en cours, ou démarre
     * l'envoi de la semaine s'il est dû.
     *
     * @return string résumé de ce qui a été fait (journal)
     */
    public function runDue(): string
    {
        if (! Site::mailConfigured()) {
            return 'E-mails non configurés.';
        }

        // Un seul envoi à la fois (cron et visites) : personne ne reçoit l'e-mail deux fois
        $lock = Cache::lock('newsletter:running', 600);
        if (! $lock->get()) {
            return 'Un envoi est déjà en cours.';
        }

        try {
            return $this->runDueUnlocked();
        } finally {
            $lock->release();
        }
    }

    private function runDueUnlocked(): string
    {
        Site::applyMailConfig();
        $this->useSiteRootUrl();

        if ($campaign = NewsletterCampaign::where('status', 'sending')->latest('id')->first()) {
            return $this->sendBatch($campaign);
        }

        if (! Site::get('newsletter_auto')) {
            return 'Newsletter automatique désactivée.';
        }

        $slot = $this->currentSlot();
        // Créneau manqué de plus de 24 h (première configuration, site sans visites…) :
        // on n'envoie pas en retard, on attend le créneau suivant.
        if (NewsletterCampaign::where('scheduled_for', '>=', $slot)->exists() || $slot->lt(now()->subDay())) {
            return 'Rien à envoyer avant le ' . $this->nextSlot()->translatedFormat('l d F à H:i') . '.';
        }

        // Nouveautés depuis le précédent envoi (au plus 14 jours en arrière)
        $previous = NewsletterCampaign::whereIn('status', ['sent', 'skipped'])->latest('scheduled_for')->first();
        $since = $previous?->period_end ?? $slot->copy()->subWeek();
        if ($since->lt($slot->copy()->subDays(14))) {
            $since = $slot->copy()->subDays(14);
        }
        $items = $this->collectItems($since, now());

        $campaign = NewsletterCampaign::create([
            'subject'          => $this->subject(),
            'scheduled_for'    => $slot,
            'period_start'     => $since,
            'period_end'       => now(),
            'items'            => $items,
            'status'           => $items ? 'sending' : 'skipped',
            'recipients_total' => $items ? NewsletterSubscription::where('is_active', true)->count() : 0,
            'finished_at'      => $items ? null : now(),
        ]);

        if (! $items) {
            return 'Aucune nouveauté cette semaine : pas d’envoi.';
        }

        return $this->sendBatch($campaign);
    }

    /** Envoie le lot suivant d'une campagne. */
    public function sendBatch(NewsletterCampaign $campaign): string
    {
        $subscribers = NewsletterSubscription::where('is_active', true)
            ->where('id', '>', (int) $campaign->last_subscriber_id)
            ->orderBy('id')
            ->limit(self::BATCH)
            ->get();

        $sentNow = 0;
        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email, $subscriber->name)->send(new NewsletterDigest($campaign, $subscriber));
                $sentNow++;
                $campaign->increment('sent_count');
            } catch (\Throwable $e) {
                // Serveur d'envoi inaccessible ou refusé dès le premier e-mail : on arrête et on signale
                if ($sentNow === 0 && (int) $campaign->sent_count === 0) {
                    $campaign->update(['status' => 'failed', 'error' => self::explainMailError($e), 'finished_at' => now()]);
                    Log::error('Newsletter : envoi impossible.', ['error' => $e->getMessage()]);

                    return 'Échec : ' . self::explainMailError($e);
                }
                $campaign->increment('failed_count');
                Log::warning('Newsletter : un e-mail n’a pas pu partir.', ['email' => $subscriber->email, 'error' => $e->getMessage()]);
            }
            $campaign->update(['last_subscriber_id' => $subscriber->id]);
        }

        if ($subscribers->count() < self::BATCH) {
            $campaign->update(['status' => 'sent', 'finished_at' => now()]);

            return "Newsletter envoyée ({$campaign->sent_count} e-mail(s)).";
        }

        return "Envoi en cours : {$campaign->sent_count} / {$campaign->recipients_total}.";
    }

    /** Relance une campagne en échec (après correction des réglages). */
    public function retry(NewsletterCampaign $campaign): void
    {
        $campaign->update(['status' => 'sending', 'error' => null, 'finished_at' => null]);
    }

    /**
     * Contenus publiés entre deux dates, prêts pour l'e-mail.
     *
     * @return array<int, array{type: string, title: string, url: string, image: ?string, excerpt: ?string, date: ?string}>
     */
    public function collectItems(Carbon $from, Carbon $to, int $perType = 6): array
    {
        $between = fn ($q, string $table = '') => $q->where(function ($w) use ($from, $to, $table) {
            $p = $table ? "{$table}." : '';
            $w->whereBetween("{$p}published_at", [$from, $to])
              ->orWhere(fn ($x) => $x->whereNull("{$p}published_at")->whereBetween("{$p}created_at", [$from, $to]));
        });
        $img = fn (?string $path) => $path ? (Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path)) : null;
        $items = [];

        foreach ($between(News::published(), 'news')->orderByDesc('published_at')->limit($perType)->get() as $n) {
            $items[] = ['type' => 'Écho de la mission', 'title' => $n->title, 'url' => route('news.show', $n->slug),
                'image' => $img($n->featured_image), 'excerpt' => Str::limit(strip_tags((string) $n->excerpt), 160), 'date' => optional($n->published_at)->toIso8601String()];
        }
        foreach ($between(Post::published(), 'posts')->limit($perType)->get() as $p) {
            $items[] = ['type' => 'Enseignement', 'title' => $p->title, 'url' => route('posts.show', $p->slug),
                'image' => $img($p->featured_image), 'excerpt' => Str::limit(strip_tags((string) ($p->excerpt ?: $p->content)), 160), 'date' => optional($p->published_at)->toIso8601String()];
        }
        foreach ($between(Video::query())->orderByDesc('published_at')->limit($perType)->get() as $v) {
            $items[] = ['type' => 'Vidéo ' . ($v->platform?->label() ?? ''), 'title' => $v->title, 'url' => route('videos.show', $v),
                'image' => $v->thumbnail_url, 'excerpt' => null, 'date' => optional($v->published_at)->toIso8601String()];
        }
        foreach ($between(Resource::published(), 'resources')->orderByDesc('published_at')->limit($perType)->get() as $r) {
            $items[] = ['type' => 'Ressource', 'title' => $r->title, 'url' => route('ressources.show', $r->slug),
                'image' => $img($r->cover_image), 'excerpt' => Str::limit(strip_tags((string) $r->description), 160), 'date' => optional($r->published_at)->toIso8601String()];
        }

        return $items;
    }

    /** Aperçu : la newsletter telle qu'elle partirait maintenant (ou les derniers contenus). */
    public function previewCampaign(): NewsletterCampaign
    {
        $previous = NewsletterCampaign::whereIn('status', ['sent', 'skipped'])->latest('scheduled_for')->first();
        $since = $previous?->period_end ?? now()->subWeek();
        $items = $this->collectItems($since, now());
        if (! $items) {
            $items = $this->collectItems(now()->subYears(5), now(), 2);   // exemple avec les derniers contenus
        }

        return new NewsletterCampaign(['subject' => $this->subject(), 'items' => $items, 'scheduled_for' => now()]);
    }

    public function subject(): string
    {
        return strtr((string) Site::get('newsletter_subject', 'Cette semaine sur {site}'), [
            '{site}' => (string) Site::get('site_name'),
            '{date}' => now()->translatedFormat('d F Y'),
        ]);
    }

    /** E-mail de bienvenue (envoyé après la réponse, sans ralentir le visiteur). */
    public function welcome(NewsletterSubscription $subscriber): void
    {
        if (! Site::get('newsletter_welcome') || ! Site::mailConfigured()) {
            return;
        }

        try {
            Mail::to($subscriber->email, $subscriber->name)->send(new NewsletterWelcome($subscriber));
        } catch (\Throwable $e) {
            Log::warning('Newsletter : e-mail de bienvenue non envoyé.', ['email' => $subscriber->email, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Les liens des e-mails doivent pointer vers le vrai site, même quand l'envoi
     * part du cron (APP_URL peut valoir « localhost » sur le serveur).
     */
    public function useSiteRootUrl(): void
    {
        $appUrl = (string) config('app.url');
        $root = Cache::get(self::ROOT_URL_CACHE);
        if ($root && (app()->runningInConsole() || $appUrl === '') && preg_match('#//(localhost|127\.0\.0\.1)(:\d+)?/?$#', $appUrl)) {
            URL::forceRootUrl($root);
        }
    }

    /** Message d'erreur d'envoi compréhensible. */
    public static function explainMailError(\Throwable $e): string
    {
        $m = $e->getMessage();

        return match (true) {
            str_contains($m, '535') || stripos($m, 'authentication') !== false
                => 'identifiant ou clé SMTP refusés. Vérifiez le login et la clé SMTP Brevo.',
            stripos($m, 'sender') !== false || str_contains($m, '550') || str_contains($m, '553')
                => 'adresse d’expéditeur refusée. Validez-la dans Brevo → Expéditeurs. (' . Str::limit($m, 120) . ')',
            stripos($m, 'Connection') !== false || stripos($m, 'timed out') !== false
                => 'impossible de joindre le serveur SMTP. Vérifiez le serveur et le port (essayez 2525). (' . Str::limit($m, 120) . ')',
            default => Str::limit($m, 250),
        };
    }
}
