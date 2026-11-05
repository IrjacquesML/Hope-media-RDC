<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminReportController;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\News;
use App\Models\NewsletterSubscription;
use App\Models\SocialFeed;
use App\Models\User;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAndPurgeTest extends TestCase
{
    use RefreshDatabase;

    private function news(string $title, array $extra = []): News
    {
        return News::create($extra + [
            'title' => $title, 'slug' => \Illuminate\Support\Str::slug($title) . '-' . uniqid(),
            'content' => 'Contenu', 'status' => 'published', 'published_at' => now()->subDay(),
            'views' => 10, 'likes' => 2,
        ]);
    }

    private function feed(string $platform, string $type, int $views, array $extra = []): SocialFeed
    {
        return SocialFeed::create($extra + [
            'platform' => $platform, 'platform_id' => uniqid(), 'media_type' => $type, 'title' => "Publication $platform",
            'view_count' => $views, 'likes_count' => 5, 'comments_count' => 1, 'shares_count' => 2,
            'published_at' => now()->subDays(2), 'synced_at' => now(),
        ]);
    }

    private function collect(array $sections): array
    {
        $method = new \ReflectionMethod(AdminReportController::class, 'collect');

        return $method->invoke(new AdminReportController(), now()->subMonth()->startOfDay(), now()->endOfDay(), $sections);
    }

    public function test_report_counts_every_synced_social_publication_and_gives_totals(): void
    {
        $this->feed('youtube', 'video', 100);
        $this->feed('facebook', 'image', 40);
        $this->feed('facebook', 'text', 30);
        $this->feed('instagram', 'reel', 50, ['published_at' => now()->subYear()]); // hors période
        Video::create(['title' => 'Vidéo', 'platform' => 'youtube', 'video_id' => 'abc', 'view_count' => 100, 'likes_count' => 5, 'comments_count' => 1, 'published_at' => now()->subDays(2)]);
        $article = $this->news('Culte de clôture');
        $article->comments()->create(['author_name' => 'Lecteur', 'content' => 'Amen', 'is_approved' => true]);

        $out = $this->collect(['news', 'videos', 'social', 'engagement']);

        $this->assertSame(3, $out['social']['count']);
        $this->assertSame(170, $out['social']['views']);
        $this->assertSame(1, $out['news']['comments']);

        // Les vidéos sont déjà dans les réseaux sociaux : pas de double comptage
        $this->assertTrue($out['totals']['excludesVideos']);
        $this->assertSame(1 + 3, $out['totals']['total']['items']);
        $this->assertSame(10 + 170, $out['totals']['total']['views']);
        $this->assertSame(1 + 3, $out['totals']['total']['comments']);
        $this->assertSame(1, $out['totals']['audienceTotal']);
    }

    public function test_pdf_with_all_sections_is_generated(): void
    {
        $this->feed('facebook', 'image', 40);
        $this->news('Culte de clôture');

        $this->actingAs(User::factory()->role('super_admin')->create())
            ->post(route('admin.reports.generate'), [
                'date_from' => now()->subMonth()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d'),
                'sections' => array_keys(AdminReportController::SECTIONS),
                'signatory_name' => 'Jean', 'signatory_role' => 'Directeur', 'output' => 'preview',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_purge_removes_only_test_data_and_needs_force(): void
    {
        $test = $this->news('TEST');
        $this->news('Test 2');
        $real = $this->news('Test de foi : la persévérance');
        $test->comments()->create(['author_name' => 'Jacques', 'content' => 'Bravo', 'is_approved' => true]);
        $realComment = $real->comments()->create(['author_name' => 'Marie', 'content' => 'Merci', 'is_approved' => true]);
        $real->comments()->create(['author_name' => 'Lecteur', 'content' => 'test', 'is_approved' => true]);
        NewsletterSubscription::create(['email' => 'demo@example.com', 'token' => 't1', 'is_active' => true]);
        NewsletterSubscription::create(['email' => 'fidele@gmail.com', 'token' => 't2', 'is_active' => true]);
        $this->feed('youtube', 'video', 10, ['title' => 'TEST']); // contenu synchronisé : jamais touché

        $this->artisan('content:purge-test')->assertSuccessful();
        $this->assertSame(3, News::count(), 'Sans --force, rien n\'est supprimé');

        $this->artisan('content:purge-test', ['--force' => true])->assertSuccessful();

        $this->assertSame([$real->id], News::pluck('id')->all());
        $this->assertSame([$realComment->id], Comment::pluck('id')->all());
        $this->assertSame(['fidele@gmail.com'], NewsletterSubscription::pluck('email')->all());
        $this->assertSame(1, SocialFeed::count());
    }
}
