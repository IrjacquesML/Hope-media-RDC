<?php

namespace Tests\Feature;

use App\Models\SocialPlatform;
use App\Models\User;
use App\Models\Video;
use App\Services\SocialSyncManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Synchronisation YouTube, Facebook, Instagram, TikTok et X, configurée depuis l'admin.
 * Toutes les réponses d'API sont simulées (Http::fake) : aucun appel réel.
 */
class SocialSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        // On isole les tests des valeurs éventuellement présentes dans le .env
        foreach (['youtube', 'facebook', 'instagram', 'tiktok', 'x'] as $p) {
            config(["services.{$p}" => ['max_results' => 20]]);
        }
    }

    private function configure(string $platform, array $credentials, bool $enabled = true): SocialPlatform
    {
        $model = SocialPlatform::for($platform);
        $model->credentials = $credentials;
        $model->enabled = $enabled;
        $model->save();

        return $model;
    }

    // ------------------------------------------------------------------ admin

    public function test_admin_page_lists_the_five_networks(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.social.index'))
            ->assertOk()
            ->assertSee('YouTube')->assertSee('Facebook')->assertSee('Instagram')->assertSee('TikTok')->assertSee('fa-x-twitter', false)
            ->assertSee('Tester la connexion')->assertSee('Synchroniser maintenant');
    }

    public function test_guest_cannot_open_the_page(): void
    {
        $this->get(route('admin.social.index'))->assertRedirect(route('auth.login'));
    }

    public function test_credentials_are_encrypted_and_secrets_kept_when_left_empty(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.social.update', 'youtube'), [
            'credentials' => ['api_key' => 'AIza-SECRET-123', 'channel_id' => '@Hopemedia-u4b'],
            'max_results' => 15, 'interval_hours' => 6, 'enabled' => '1',
        ])->assertSessionHas('success');

        $raw = DB::table('social_platforms')->where('platform', 'youtube')->value('credentials');
        $this->assertStringNotContainsString('AIza-SECRET-123', $raw, 'La clé doit être chiffrée en base.');

        // La page ne réaffiche jamais le secret
        $this->actingAs($admin)->get(route('admin.social.index'))->assertDontSee('AIza-SECRET-123');

        // Secret laissé vide : conservé ; chaîne modifiée : remplacée
        $this->actingAs($admin)->put(route('admin.social.update', 'youtube'), [
            'credentials' => ['api_key' => '', 'channel_id' => '@AutreChaine'],
            'max_results' => 15, 'interval_hours' => 12, 'enabled' => '1',
        ]);
        $model = SocialPlatform::for('youtube')->fresh();
        $this->assertSame('AIza-SECRET-123', $model->storedCredential('api_key'));
        $this->assertSame('@AutreChaine', $model->storedCredential('channel_id'));
        $this->assertSame(12, $model->interval_hours);

        // Case « Effacer »
        $this->actingAs($admin)->put(route('admin.social.update', 'youtube'), [
            'credentials' => ['api_key' => ''], 'forget' => ['api_key' => '1'],
            'max_results' => 15, 'interval_hours' => 12,
        ]);
        $this->assertNull(SocialPlatform::for('youtube')->fresh()->storedCredential('api_key'));
    }

    public function test_admin_values_override_env_values(): void
    {
        config(['services.youtube.api_key' => 'ENV-KEY', 'services.youtube.channel_id' => '@env']);
        $manager = app(SocialSyncManager::class);
        $this->assertSame('ENV-KEY', $manager->credentials('youtube')['api_key']);
        $this->assertSame('env', $manager->source('youtube', 'api_key'));

        $this->configure('youtube', ['api_key' => 'ADMIN-KEY']);
        $this->assertSame('ADMIN-KEY', $manager->credentials('youtube')['api_key']);
        $this->assertSame('@env', $manager->credentials('youtube')['channel_id']);
    }

    // ---------------------------------------------------------------- YouTube

    public function test_youtube_test_and_sync(): void
    {
        $this->configure('youtube', ['api_key' => 'AIza', 'channel_id' => '@Hopemedia-u4b']);
        Http::fake([
            'www.googleapis.com/youtube/v3/channels*' => fn (Request $r) => str_contains($r->url(), 'part=snippet')
                ? Http::response(['items' => [['snippet' => ['title' => 'Hope Media network RDC']]]])
                : Http::response(['items' => [['contentDetails' => ['relatedPlaylists' => ['uploads' => 'UU123']]]]]),
            'www.googleapis.com/youtube/v3/playlistItems*' => Http::response(['items' => [['contentDetails' => ['videoId' => 'abc123XYZ00']]]]),
            'www.googleapis.com/youtube/v3/videos*' => Http::response(['items' => [[
                'id' => 'abc123XYZ00',
                'snippet' => ['title' => 'Une voix peut-elle faire la différence ?', 'channelId' => 'UC1', 'channelTitle' => 'Hope Media', 'publishedAt' => '2026-10-20T10:00:00Z', 'thumbnails' => ['high' => ['url' => 'https://i.ytimg.com/x.jpg']]],
                'contentDetails' => ['duration' => 'PT17M18S'],
                'statistics' => ['viewCount' => '120'],
            ]]]),
        ]);

        $this->actingAs(User::factory()->create())->post(route('admin.social.test', 'youtube'))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Hope Media network RDC'));

        $this->actingAs(User::factory()->create())->post(route('admin.social.sync', 'youtube'))->assertSessionHas('success');
        $this->assertDatabaseHas('videos', ['platform' => 'youtube', 'video_id' => 'abc123XYZ00', 'view_count' => 120]);
        $this->assertSame('ok', SocialPlatform::for('youtube')->last_status);
        $this->assertSame(1, SocialPlatform::for('youtube')->last_count);
    }

    // --------------------------------------------------------------- Facebook

    public function test_facebook_imports_page_videos_with_a_working_player(): void
    {
        $this->configure('facebook', ['page_id' => '111', 'page_access_token' => 'EAAG']);
        Http::fake(['graph.facebook.com/v21.0/111/videos*' => Http::response(['data' => [[
            'id' => '999', 'title' => 'Culte du sabbat', 'description' => 'Culte', 'permalink_url' => '/hopemedia/videos/999/',
            'created_time' => '2026-10-20T10:00:00+0000', 'length' => 125.4,
            'thumbnails' => ['data' => [['uri' => 'https://fb/t.jpg', 'is_preferred' => true]]],
        ]]])]);

        $this->assertSame(1, app(SocialSyncManager::class)->sync('facebook'));
        $video = Video::where('platform', 'facebook')->firstOrFail();
        $this->assertSame('https://www.facebook.com/hopemedia/videos/999/', $video->post_url);
        $this->assertSame('PT125S', $video->duration);
        $this->assertStringContainsString(rawurlencode('https://www.facebook.com/hopemedia/videos/999/'), $video->embed_url);
    }

    public function test_facebook_expired_token_is_explained(): void
    {
        $this->configure('facebook', ['page_id' => '111', 'page_access_token' => 'EAAG']);
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Session has expired', 'code' => 190]], 400)]);

        $this->actingAs(User::factory()->create())->post(route('admin.social.sync', 'facebook'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'jeton expiré'));
        $this->assertSame('error', SocialPlatform::for('facebook')->last_status);
    }

    // -------------------------------------------------------------- Instagram

    public function test_instagram_login_token_imports_only_videos_and_reels(): void
    {
        $this->configure('instagram', ['access_token' => 'IGAA-token']);
        Http::fake(['graph.instagram.com/v21.0/me/media*' => Http::response(['data' => [
            ['id' => '1', 'media_type' => 'VIDEO', 'permalink' => 'https://www.instagram.com/reel/AbC123/', 'caption' => "Reel du sabbat\nsuite", 'thumbnail_url' => 'https://ig/t.jpg', 'timestamp' => '2026-10-20T10:00:00+0000', 'username' => 'hopemedia'],
            ['id' => '2', 'media_type' => 'IMAGE', 'permalink' => 'https://www.instagram.com/p/XyZ/', 'media_url' => 'https://ig/i.jpg'],
        ]])]);

        $this->assertSame(1, app(SocialSyncManager::class)->sync('instagram'));
        $this->assertDatabaseHas('videos', ['platform' => 'instagram', 'video_id' => 'AbC123', 'title' => 'Reel du sabbat']);
        $this->assertDatabaseMissing('videos', ['video_id' => 'XyZ']);
    }

    public function test_instagram_facebook_token_requires_account_id(): void
    {
        $this->configure('instagram', ['access_token' => 'EAAG-token']);
        $this->assertNotEmpty(app(SocialSyncManager::class)->missing('instagram'));
    }

    // ----------------------------------------------------------------- TikTok

    public function test_tiktok_refreshes_and_saves_tokens_then_syncs(): void
    {
        $this->configure('tiktok', ['client_key' => 'ck', 'client_secret' => 'cs', 'access_token' => 'old', 'refresh_token' => 'rft-old', 'open_id' => 'oid']);
        Http::fake([
            'open.tiktokapis.com/v2/oauth/token/' => Http::response(['access_token' => 'new-access', 'refresh_token' => 'rft-new']),
            'open.tiktokapis.com/v2/video/list/*' => Http::sequence()
                ->push(['error' => ['code' => 'access_token_invalid', 'message' => 'expired']], 401)
                ->push(['data' => ['videos' => [['id' => '7001', 'title' => 'Un verset en 30 secondes', 'cover_image_url' => 'https://tt/c.jpg', 'share_url' => 'https://www.tiktok.com/@h/video/7001', 'create_time' => 1760000000, 'duration' => 30]]], 'error' => ['code' => 'ok']]),
        ]);

        $this->assertSame(1, app(SocialSyncManager::class)->sync('tiktok'));

        $model = SocialPlatform::for('tiktok')->fresh();
        $this->assertSame('new-access', $model->storedCredential('access_token'));
        $this->assertSame('rft-new', $model->storedCredential('refresh_token'));
        $this->assertDatabaseHas('videos', ['platform' => 'tiktok', 'video_id' => '7001']);

        // Les champs demandés sont bien passés dans l'adresse (exigence de l'API v2)
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/video/list/?fields=id,title') && $r['max_count'] === 20);
    }

    // ---------------------------------------------------------------------- X

    public function test_x_imports_only_posts_with_video(): void
    {
        $this->configure('x', ['bearer_token' => 'AAAA', 'username' => '@HopeMediaRDC']);
        Http::fake([
            'api.x.com/2/users/by/username/HopeMediaRDC' => Http::response(['data' => ['id' => '42', 'name' => 'Hope Media RDC', 'username' => 'HopeMediaRDC']]),
            'api.x.com/2/users/42/tweets*' => Http::response([
                'data' => [
                    ['id' => '1001', 'text' => 'Le culte en vidéo https://t.co/abc', 'created_at' => '2026-10-20T10:00:00.000Z', 'attachments' => ['media_keys' => ['m1']], 'public_metrics' => ['like_count' => 5, 'reply_count' => 1]],
                    ['id' => '1002', 'text' => 'Simple texte'],
                    ['id' => '1003', 'text' => 'Une photo', 'attachments' => ['media_keys' => ['m2']]],
                ],
                'includes' => ['media' => [
                    ['media_key' => 'm1', 'type' => 'video', 'preview_image_url' => 'https://pbs/x.jpg', 'duration_ms' => 61000, 'public_metrics' => ['view_count' => 300]],
                    ['media_key' => 'm2', 'type' => 'photo'],
                ]],
            ]),
        ]);

        $manager = app(SocialSyncManager::class);
        $this->assertSame('Hope Media RDC (@HopeMediaRDC)', $manager->test('x'));
        $this->assertSame(1, $manager->sync('x'));

        $video = Video::where('platform', 'x')->firstOrFail();
        $this->assertSame('Le culte en vidéo', $video->title);
        $this->assertSame('https://x.com/HopeMediaRDC/status/1001', $video->post_url);
        $this->assertSame(300, $video->view_count);
        $this->assertStringContainsString('platform.twitter.com/embed/Tweet.html', $video->embed_url);

        // La vidéo X s'affiche sur le site
        $this->get(route('videos.index', ['platform' => 'x']))->assertOk()->assertSee('Le culte en vidéo');
        $this->get(route('videos.show', $video))->assertOk()->assertSee('fa-x-twitter', false);
    }

    public function test_x_free_plan_error_is_explained(): void
    {
        $this->configure('x', ['bearer_token' => 'AAAA', 'username' => 'HopeMediaRDC']);
        Http::fake([
            'api.x.com/2/users/by/username/*' => Http::response(['data' => ['id' => '42', 'name' => 'Hope', 'username' => 'HopeMediaRDC']]),
            'api.x.com/2/users/42/tweets*' => Http::response(['title' => 'Forbidden'], 403),
        ]);

        $this->expectExceptionMessage('offre « Basic »');
        app(SocialSyncManager::class)->sync('x');
    }

    // ------------------------------------------------------------ planificateur

    public function test_scheduler_syncs_only_enabled_networks_that_are_due(): void
    {
        $this->configure('youtube', ['api_key' => 'AIza', 'channel_id' => 'UC1'], enabled: true);
        $this->configure('x', ['bearer_token' => 'AAAA', 'username' => 'h'], enabled: false);
        $recent = $this->configure('facebook', ['page_id' => '1', 'page_access_token' => 'E'], enabled: true);
        $recent->forceFill(['last_synced_at' => now()->subMinutes(10), 'interval_hours' => 3])->save();

        Http::fake([
            'www.googleapis.com/youtube/v3/channels*' => Http::response(['items' => [['contentDetails' => ['relatedPlaylists' => ['uploads' => 'UU1']]]]]),
            'www.googleapis.com/youtube/v3/playlistItems*' => Http::response(['items' => []]),
        ]);

        $this->artisan('social:sync')->assertSuccessful();

        $this->assertSame('ok', SocialPlatform::for('youtube')->last_status);   // activé et dû
        $this->assertNull(SocialPlatform::for('x')->last_status);               // désactivé
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'graph.facebook.com'));   // synchronisé il y a 10 min
        $this->assertNotNull(cache(SocialSyncManager::SCHEDULER_HEARTBEAT));
    }

    public function test_incomplete_configuration_gives_a_clear_message(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.social.sync', 'tiktok'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Configuration incomplète'));
    }
}
