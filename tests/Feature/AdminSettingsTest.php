<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\SocialPlatform;
use App\Models\User;
use App\Services\SocialSyncManager;
use App\Support\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Paramètres du site, partenaires, comptes et rôles, profil,
 * et améliorations de la synchronisation (TikTok OAuth, jeton Instagram, mode sans cron).
 */
class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Site::flush();
        foreach (['youtube', 'facebook', 'instagram', 'tiktok', 'x'] as $p) {
            config(["services.{$p}" => ['max_results' => 20]]);
        }
    }

    private function superAdmin(): User
    {
        return User::factory()->role('super_admin')->create();
    }

    // ------------------------------------------------------------------ rôles

    public function test_each_role_only_reaches_its_sections(): void
    {
        $editor = User::factory()->role('editor')->create();
        $this->actingAs($editor)->get(route('admin.posts.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.news.index'))->assertOk();
        foreach (['admin.social.index', 'admin.contacts.index', 'admin.reports.index', 'admin.settings.index', 'admin.users.index'] as $route) {
            $this->actingAs($editor)->get(route($route))->assertRedirect(route('admin.dashboard'))->assertSessionHas('error');
        }

        $admin = User::factory()->role('admin')->create();
        $this->actingAs($admin)->get(route('admin.social.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get(route('admin.users.index'))->assertRedirect(route('admin.dashboard'));

        $super = $this->superAdmin();
        $this->actingAs($super)->get(route('admin.settings.index'))->assertOk();
        $this->actingAs($super)->get(route('admin.users.index'))->assertOk();
    }

    public function test_menu_hides_sections_the_role_cannot_open(): void
    {
        $this->actingAs(User::factory()->role('editor')->create())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Écho de la mission')
            ->assertDontSee('Paramètres du site')
            ->assertDontSee('Synchronisation des réseaux');

        $this->actingAs($this->superAdmin())->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Paramètres du site')->assertSee('Comptes');
    }

    public function test_all_admin_pages_render_for_super_admin(): void
    {
        $user = $this->superAdmin();
        foreach (array_merge(array_keys(Site::groups()), ['partners']) as $tab) {
            $this->actingAs($user)->get(route('admin.settings.index', ['onglet' => $tab]))->assertOk();
        }
        foreach (['admin.dashboard', 'admin.users.index', 'admin.users.create', 'admin.profile.edit', 'admin.social.index', 'admin.videos.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
        $this->actingAs($user)->get(route('admin.users.edit', $user))->assertOk();
    }

    // ------------------------------------------------------------------ paramètres

    public function test_site_shows_original_values_until_changed(): void
    {
        $this->get(route('contact.create'))->assertOk()
            ->assertSee('+243 852 283 241')
            ->assertSee('hopemediasociaux@gmail.com')
            ->assertSee('https://www.youtube.com/@Hopemedia-u4b/live', false);
    }

    public function test_super_admin_edits_contact_details_without_code(): void
    {
        $this->actingAs($this->superAdmin())->put(route('admin.settings.update', 'contact'), [
            'contact_address'  => '12 avenue de la Paix, Kinshasa',
            'contact_phone'    => '+243 999 000 111',
            'contact_whatsapp' => '+243 999 000 222',
            'contact_email'    => 'redaction@hopemedia.cd',
            'contact_hours'    => "Lundi - Samedi : 8h - 18h",
        ])->assertRedirect()->assertSessionHas('success');

        $this->get(route('home'))->assertOk()
            ->assertSee('12 avenue de la Paix, Kinshasa')
            ->assertSee('tel:+243999000111', false)
            ->assertSee('https://wa.me/243999000222', false)
            ->assertSee('redaction@hopemedia.cd')
            ->assertDontSee('hopemediasociaux@gmail.com');

        $this->get(route('contact.create'))->assertSee('Lundi - Samedi : 8h - 18h');
    }

    public function test_header_live_link_and_buttons_are_configurable(): void
    {
        $this->actingAs($this->superAdmin())->put(route('admin.settings.update', 'header'), [
            'live_enabled'   => '1',
            'live_url'       => 'https://www.facebook.com/hopemedia/live',
            'live_label'     => 'En direct',
            'prayer_enabled' => '0',
            'prayer_label'   => 'Prière',
            'clock_enabled'  => '1',
            'clock_city'     => 'Kinshasa',
            'nav_home' => 'Accueil', 'nav_videos' => 'Replay', 'nav_posts' => 'Enseignements', 'nav_news' => 'Actualités',
            'nav_resources' => 'Ressources', 'nav_about' => 'La chaîne', 'nav_contact' => 'Écrire à la rédaction',
        ])->assertSessionHas('success');

        $this->get(route('home'))->assertOk()
            ->assertSee('https://www.facebook.com/hopemedia/live', false)
            ->assertSee('En direct')
            ->assertSee('Replay')
            ->assertSee('Actualités')
            ->assertDontSee('h-pray', false);
    }

    public function test_invalid_values_are_rejected_and_nothing_is_saved(): void
    {
        $this->actingAs($this->superAdmin())->put(route('admin.settings.update', 'contact'), [
            'contact_email' => 'pas-un-email',
        ])->assertSessionHasErrors('contact_email');

        $this->assertSame('hopemediasociaux@gmail.com', Site::get('contact_email'));
    }

    public function test_emptying_a_field_hides_it_and_original_can_be_restored(): void
    {
        $user = $this->superAdmin();
        $base = ['site_name' => 'Hope Media RDC', 'site_full_name' => 'Hope Media Network RDC', 'theme_color' => '#0B1530'];

        $this->actingAs($user)->put(route('admin.settings.update', 'identity'), $base + ['slogan' => 'Nouveau slogan']);
        $this->assertSame('Nouveau slogan', Site::get('slogan'));
        $this->get(route('home'))->assertSee('Nouveau slogan');

        // Vider le champ masque l'élément
        $this->actingAs($user)->put(route('admin.settings.update', 'identity'), $base + ['slogan' => '']);
        $this->assertNull(Site::get('slogan'));
        $this->get(route('home'))->assertDontSee('f-slogan', false);

        // « Rétablir la valeur d'origine »
        $this->actingAs($user)->put(route('admin.settings.update', 'identity'), $base + ['slogan' => '', 'slogan_default' => '1']);
        $this->assertSame('La Parole qui éclaire', Site::get('slogan'));
    }

    public function test_essential_texts_cannot_be_emptied(): void
    {
        $this->actingAs($this->superAdmin())->put(route('admin.settings.update', 'identity'), ['site_name' => '', 'site_full_name' => 'X', 'theme_color' => '#0B1530'])
            ->assertSessionHasErrors('site_name');
        $this->assertSame('Hope Media RDC', Site::get('site_name'));
    }

    public function test_social_links_appear_in_footer(): void
    {
        $this->actingAs($this->superAdmin())->put(route('admin.settings.update', 'social'), [
            'social_youtube'  => 'https://www.youtube.com/@Hopemedia-u4b',
            'social_facebook' => 'https://www.facebook.com/hopemediardc',
            'social_tiktok'   => 'https://www.tiktok.com/@hopemediardc',
        ]);

        $this->get(route('home'))->assertSee('https://www.facebook.com/hopemediardc', false)
            ->assertSee('https://www.tiktok.com/@hopemediardc', false);
    }

    // ------------------------------------------------------------------ partenaires

    public function test_partners_are_listed_in_footer_with_clickable_names(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user)->post(route('admin.settings.partners.store'), [
            'name' => 'Hope Channel International', 'url' => 'https://www.hopechannel.com', 'is_active' => '1',
        ])->assertSessionHas('success');
        $this->actingAs($user)->post(route('admin.settings.partners.store'), [
            'name' => 'Partenaire masqué', 'url' => 'https://example.org',
        ]);

        $this->get(route('home'))->assertOk()
            ->assertSee('Nos partenaires')
            ->assertSee('<a href="https://www.hopechannel.com" target="_blank" rel="noopener">', false)
            ->assertSee('Hope Channel International')
            ->assertDontSee('https://example.org', false);

        $partner = Partner::where('name', 'Hope Channel International')->first();
        $this->actingAs($user)->delete(route('admin.settings.partners.delete', $partner))->assertSessionHas('success');
        $this->get(route('home'))->assertDontSee('https://www.hopechannel.com', false);
    }

    // ------------------------------------------------------------------ comptes

    public function test_super_admin_creates_an_account_that_can_log_in(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.users.store'), [
            'name' => 'Marie Rédactrice', 'email' => 'marie@hopemedia.cd', 'role' => 'editor',
            'password' => 'Motdepasse1', 'password_confirmation' => 'Motdepasse1', 'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $marie = User::where('email', 'marie@hopemedia.cd')->first();
        $this->assertSame('editor', $marie->role);
        $this->assertTrue(Hash::check('Motdepasse1', $marie->password));

        auth()->logout();
        $this->post(route('auth.login.post'), ['email' => 'marie@hopemedia.cd', 'password' => 'Motdepasse1'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertNotNull($marie->fresh()->last_login_at);
    }

    public function test_deactivated_account_cannot_log_in_or_stay_logged_in(): void
    {
        $user = User::factory()->role('admin')->create(['email' => 'a@b.cd', 'password' => 'Motdepasse1', 'is_active' => false]);

        $this->post(route('auth.login.post'), ['email' => 'a@b.cd', 'password' => 'Motdepasse1'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('auth.login'));
        $this->assertGuest();
    }

    public function test_cannot_demote_or_delete_yourself_nor_the_last_super_admin(): void
    {
        $me = $this->superAdmin();

        $this->actingAs($me)->put(route('admin.users.update', $me), [
            'name' => $me->name, 'email' => $me->email, 'role' => 'editor', 'is_active' => '1',
        ])->assertSessionHas('error');
        $this->assertSame('super_admin', $me->fresh()->role);

        $this->actingAs($me)->delete(route('admin.users.delete', $me))->assertSessionHas('error');
        $this->assertNotNull($me->fresh());

        // Un autre super admin désactivé : $me reste le seul actif
        $other = User::factory()->role('super_admin')->create(['is_active' => false]);
        $this->actingAs($other->forceFill(['is_active' => true]))->put(route('admin.users.update', $me), [
            'name' => $me->name, 'email' => $me->email, 'role' => 'admin', 'is_active' => '1',
        ])->assertSessionHas('error');
    }

    public function test_profile_password_change_requires_current_password(): void
    {
        $user = User::factory()->role('editor')->create(['password' => 'Ancien123']);

        $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'mauvais', 'password' => 'Nouveau123', 'password_confirmation' => 'Nouveau123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'Ancien123', 'password' => 'Nouveau123', 'password_confirmation' => 'Nouveau123',
        ])->assertSessionHas('success');
        $this->assertTrue(Hash::check('Nouveau123', $user->fresh()->password));
    }

    // ------------------------------------------------------------------ synchronisation

    public function test_tiktok_connect_stores_tokens_from_oauth(): void
    {
        $user = $this->superAdmin();
        app(SocialSyncManager::class)->storeCredentials('tiktok', ['client_key' => 'ck', 'client_secret' => 'cs']);

        $response = $this->actingAs($user)->get(route('admin.social.tiktok.connect'));
        $response->assertRedirect();
        $this->assertStringStartsWith('https://www.tiktok.com/v2/auth/authorize/?', $response->headers->get('Location'));
        $state = session('tiktok_oauth_state');

        Http::fake([
            'open.tiktokapis.com/v2/oauth/token/' => Http::response(['access_token' => 'act.new', 'refresh_token' => 'rft.new', 'open_id' => 'oid-1']),
            'open.tiktokapis.com/v2/user/info/*'  => Http::response(['data' => ['user' => ['display_name' => 'Hope Media RDC']], 'error' => ['code' => 'ok']]),
        ]);

        $this->actingAs($user)->get(route('admin.social.tiktok.callback', ['code' => 'abc', 'state' => $state]))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Hope Media RDC'));

        $model = SocialPlatform::for('tiktok')->fresh();
        $this->assertSame('act.new', $model->storedCredential('access_token'));
        $this->assertSame('rft.new', $model->storedCredential('refresh_token'));
        $this->assertSame('oid-1', $model->storedCredential('open_id'));
    }

    public function test_tiktok_callback_rejects_a_forged_state(): void
    {
        $this->actingAs($this->superAdmin())->withSession(['tiktok_oauth_state' => 'vrai'])
            ->get(route('admin.social.tiktok.callback', ['code' => 'abc', 'state' => 'faux']))
            ->assertSessionHas('error');
    }

    public function test_instagram_token_is_extended_before_sync(): void
    {
        $model = SocialPlatform::for('instagram');
        $model->credentials = ['access_token' => 'IGAA-old'];
        $model->save();

        Http::fake([
            'graph.instagram.com/refresh_access_token*' => Http::response(['access_token' => 'IGAA-new', 'expires_in' => 5184000]),
            'graph.instagram.com/v21.0/me/media*'       => Http::response(['data' => []]),
        ]);

        app(SocialSyncManager::class)->sync('instagram');

        $fresh = SocialPlatform::for('instagram')->fresh();
        $this->assertSame('IGAA-new', $fresh->storedCredential('access_token'));
        $this->assertNotEmpty($fresh->credentials['token_refreshed_at']);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'me/media') && str_contains($r->url(), 'IGAA-new'));
    }

    public function test_visits_trigger_due_syncs_when_no_cron_runs(): void
    {
        Cache::forget(SocialSyncManager::SCHEDULER_HEARTBEAT);
        $model = SocialPlatform::for('x');
        $model->credentials = ['bearer_token' => 't', 'username' => 'hopemedia'];
        $model->enabled = true;
        $model->save();

        Http::fake([
            'api.x.com/2/users/by/username/*' => Http::response(['data' => ['id' => '42', 'name' => 'Hope', 'username' => 'hopemedia']]),
            'api.x.com/2/users/42/tweets*'    => Http::response(['data' => []]),
        ]);

        $this->get(route('home'))->assertOk();

        $this->assertNotNull(Cache::get(SocialSyncManager::SCHEDULER_HEARTBEAT));
        $this->assertSame('ok', SocialPlatform::for('x')->fresh()->last_status);
    }

    public function test_no_cron_mode_can_be_turned_off(): void
    {
        Cache::forget(SocialSyncManager::SCHEDULER_HEARTBEAT);
        Site::save(['sync_without_cron' => false]);

        $this->get(route('home'))->assertOk();

        $this->assertNull(Cache::get(SocialSyncManager::SCHEDULER_HEARTBEAT));
    }
}
