<?php

namespace Tests\Feature;

use App\Mail\NewsletterDigest;
use App\Mail\NewsletterWelcome;
use App\Mail\TestMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use App\Services\NewsletterService;
use App\Support\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Newsletter automatique (Brevo) : réglages, bienvenue, envoi hebdomadaire par lots.
 */
class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Site::flush();
    }

    private function configureMail(): void
    {
        Site::save([
            'mail_username'     => 'login@smtp-brevo.com',
            'mail_password'     => 'xsmtpsib-secret',
            'mail_from_address' => 'hopemediasociaux@gmail.com',
        ]);
    }

    /** Vendredi 6 novembre 2026, 9h30 : juste après le créneau par défaut (vendredi 9h). */
    private function atSendTime(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 09:30'));
    }

    private function subscribers(int $n): void
    {
        for ($i = 1; $i <= $n; $i++) {
            NewsletterSubscription::create(['email' => "abonne{$i}@exemple.cd", 'token' => NewsletterSubscription::generateToken(), 'is_active' => true]);
        }
    }

    private function publishNews(string $title, ?Carbon $at = null): News
    {
        return News::create(['title' => $title, 'content' => 'Contenu', 'status' => 'published', 'published_at' => $at ?? now()->subDay()]);
    }

    // ------------------------------------------------------------------ réglages

    public function test_smtp_key_is_encrypted_and_never_shown(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->put(route('admin.settings.update', 'mail'), [
            'mail_host' => 'smtp-relay.brevo.com', 'mail_port' => '587', 'mail_username' => 'login@smtp-brevo.com',
            'mail_password' => 'xsmtpsib-secret', 'mail_from_address' => 'hopemediasociaux@gmail.com', 'mail_from_name' => 'Hope Media RDC',
        ])->assertSessionHas('success');

        $this->assertNotSame('xsmtpsib-secret', Setting::where('key', 'mail_password')->value('value'));
        $this->assertSame('xsmtpsib-secret', Site::get('mail_password'));

        $this->actingAs($admin)->get(route('admin.settings.index', ['onglet' => 'mail']))
            ->assertOk()->assertDontSee('xsmtpsib-secret')->assertSee('Enregistrée (chiffrée)');

        // Laisser le champ vide conserve la clé
        $this->actingAs($admin)->put(route('admin.settings.update', 'mail'), [
            'mail_host' => 'smtp-relay.brevo.com', 'mail_port' => '587', 'mail_username' => 'login@smtp-brevo.com',
            'mail_password' => '', 'mail_from_address' => 'hopemediasociaux@gmail.com', 'mail_from_name' => 'Hope Media RDC',
        ]);
        $this->assertSame('xsmtpsib-secret', Site::get('mail_password'));
    }

    public function test_admin_mail_settings_replace_env(): void
    {
        $this->configureMail();
        Site::applyMailConfig();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp-relay.brevo.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('xsmtpsib-secret', config('mail.mailers.smtp.password'));
        $this->assertSame('hopemediasociaux@gmail.com', config('mail.from.address'));
    }

    public function test_test_email_is_sent_from_settings(): void
    {
        Mail::fake();
        $this->configureMail();

        $this->actingAs(User::factory()->create())->post(route('admin.settings.mail-test'), ['to' => 'moi@exemple.cd'])
            ->assertSessionHas('success');
        Mail::assertSent(TestMail::class, fn ($m) => $m->hasTo('moi@exemple.cd'));
    }

    // ------------------------------------------------------------------ bienvenue

    public function test_new_subscriber_receives_welcome_email(): void
    {
        Mail::fake();
        $this->configureMail();

        $this->post(route('newsletter.subscribe'), ['email' => 'nouveau@exemple.cd', 'name' => 'Marie Kabila'])
            ->assertSessionHas('newsletter_success');

        Mail::assertSent(NewsletterWelcome::class, function ($m) {
            $html = $m->render();
            return $m->hasTo('nouveau@exemple.cd') && str_contains($html, 'Bienvenue, Marie') && str_contains($html, '/newsletter/desabonner/');
        });
    }

    public function test_no_email_when_mail_is_not_configured(): void
    {
        Mail::fake();
        $this->post(route('newsletter.subscribe'), ['email' => 'nouveau@exemple.cd'])->assertSessionHas('newsletter_success');
        Mail::assertNothingSent();
    }

    // ------------------------------------------------------------------ envoi hebdomadaire

    public function test_weekly_newsletter_is_sent_once_at_the_scheduled_time(): void
    {
        Mail::fake();
        $this->configureMail();
        Site::save(['newsletter_day' => 'friday', 'newsletter_hour' => '9']);
        $this->subscribers(3);
        NewsletterSubscription::create(['email' => 'parti@exemple.cd', 'token' => 'x', 'is_active' => false]);
        $this->publishNews('Campagne à Matadi', Carbon::parse('2026-11-04 10:00'));

        // Vendredi 8h59 : le créneau précédent date d'une semaine, il est sauté
        $this->travelTo(Carbon::parse('2026-11-06 08:59'));
        app(NewsletterService::class)->runDue();
        $this->assertSame(0, NewsletterCampaign::count());
        Mail::assertNothingSent();

        $this->travelTo(Carbon::parse('2026-11-06 09:05'));   // vendredi 9h05
        $result = app(NewsletterService::class)->runDue();

        $this->assertStringContainsString('envoyée', $result);
        $campaign = NewsletterCampaign::latest('id')->first();
        $this->assertSame('sent', $campaign->status);
        $this->assertSame(3, $campaign->sent_count);
        $this->assertSame(1, NewsletterCampaign::count());
        Mail::assertSent(NewsletterDigest::class, fn ($m) => str_contains($m->render(), 'Campagne à Matadi'));
        Mail::assertNotSent(NewsletterDigest::class, fn ($m) => $m->hasTo('parti@exemple.cd'));

        // Pas de second envoi la même semaine
        $this->travelTo(Carbon::parse('2026-11-06 15:00'));
        app(NewsletterService::class)->runDue();
        $this->assertSame(1, NewsletterCampaign::count());
    }

    public function test_nothing_is_sent_without_new_content(): void
    {
        $this->atSendTime();
        Mail::fake();
        $this->configureMail();
        $this->subscribers(2);

        $result = app(NewsletterService::class)->runDue();

        $this->assertStringContainsString('Aucune nouveauté', $result);
        $this->assertSame('skipped', NewsletterCampaign::first()->status);
        Mail::assertNothingSent();
    }

    public function test_large_lists_are_sent_in_batches(): void
    {
        $this->atSendTime();
        Mail::fake();
        $this->configureMail();
        $this->subscribers(NewsletterService::BATCH + 5);
        $this->publishNews('Nouveauté');

        $service = app(NewsletterService::class);
        $service->runDue();
        $campaign = NewsletterCampaign::first();
        $this->assertSame('sending', $campaign->status);
        $this->assertSame(NewsletterService::BATCH, $campaign->sent_count);

        $service->runDue();   // passage suivant : continue
        $this->assertSame('sent', $campaign->fresh()->status);
        $this->assertSame(NewsletterService::BATCH + 5, $campaign->fresh()->sent_count);
        Mail::assertSent(NewsletterDigest::class, NewsletterService::BATCH + 5);
    }

    public function test_automatic_sending_can_be_paused(): void
    {
        $this->atSendTime();
        Mail::fake();
        $this->configureMail();
        Site::save(['newsletter_auto' => false]);
        $this->subscribers(1);
        $this->publishNews('Nouveauté');

        app(NewsletterService::class)->runDue();

        $this->assertSame(0, NewsletterCampaign::count());
        Mail::assertNothingSent();
    }

    public function test_smtp_failure_is_recorded_and_can_be_retried(): void
    {
        $this->atSendTime();
        $this->configureMail();
        $this->subscribers(2);
        $this->publishNews('Nouveauté');
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Expected response code 235 but got code "535", with message "535 5.7.8 Authentication failed"'));

        app(NewsletterService::class)->runDue();

        $campaign = NewsletterCampaign::first();
        $this->assertSame('failed', $campaign->status);
        $this->assertStringContainsString('clé SMTP', $campaign->error);

        $this->actingAs(User::factory()->create())->get(route('admin.newsletter.index'))
            ->assertOk()->assertSee('Relancer l’envoi');
    }

    public function test_visits_trigger_the_newsletter_without_cron(): void
    {
        $this->atSendTime();
        Mail::fake();
        $this->configureMail();
        $this->subscribers(1);
        $this->publishNews('Nouveauté de la semaine');

        $this->get(route('home'))->assertOk();

        Mail::assertSent(NewsletterDigest::class, 1);
    }

    // ------------------------------------------------------------------ admin

    public function test_admin_page_preview_and_test_send(): void
    {
        Mail::fake();
        $this->publishNews('Article à prévisualiser');
        $admin = User::factory()->role('admin')->create(['email' => 'admin@exemple.cd']);

        $this->actingAs($admin)->get(route('admin.newsletter.index'))->assertOk()->assertSee('pas configuré', false);
        $this->actingAs($admin)->get(route('admin.newsletter.preview'))->assertOk()->assertSee('Article à prévisualiser');

        $this->configureMail();
        $this->actingAs($admin)->get(route('admin.newsletter.index'))->assertOk()->assertSee('Prochaine newsletter');
        $this->actingAs($admin)->post(route('admin.newsletter.test'))->assertSessionHas('success');
        Mail::assertSent(NewsletterDigest::class, fn ($m) => $m->hasTo('admin@exemple.cd'));
    }

    public function test_unsubscribe_link_stops_future_emails(): void
    {
        $sub = NewsletterSubscription::create(['email' => 'a@exemple.cd', 'token' => 'jeton-unique', 'is_active' => true]);
        $this->get(route('newsletter.unsubscribe', 'jeton-unique'))->assertOk();
        $this->assertFalse($sub->fresh()->is_active);
    }
}
