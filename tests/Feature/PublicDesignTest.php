<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\News;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que toutes les pages publiques s'affichent avec le design « Lumière dans la nuit »,
 * et que la logique existante (likes, commentaires, prière, newsletter) fonctionne toujours.
 */
class PublicDesignTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;
    private News $news;
    private Resource $resource;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['name' => 'Études bibliques', 'slug' => 'etudes-bibliques', 'type' => 'enseignement', 'is_active' => true]);

        $this->post = Post::create([
            'category_id' => $this->category->id, 'title' => 'Le Sabbat, un cadeau', 'content' => '<p>Contenu de l’enseignement</p>',
            'excerpt' => 'Résumé', 'content_type' => 'video', 'status' => 'published', 'published_at' => now()->subDay(),
            'author' => 'Pr. Daniel', 'teaching_date' => now()->subDays(2), 'is_featured' => true,
        ]);
        Media::create(['post_id' => $this->post->id, 'media_type' => 'youtube', 'title' => 'Prédication', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'is_active' => true, 'order' => 1]);
        $comment = $this->post->comments()->create(['author_name' => 'Marie', 'content' => 'Merci pour ce message', 'is_approved' => true]);
        $this->post->comments()->create(['parent_id' => $comment->id, 'author_name' => 'Hope Media', 'content' => 'Merci Marie', 'is_approved' => true, 'is_admin_reply' => true]);

        $this->news = News::create([
            'title' => 'Campagne à Matadi', 'content' => 'Compte rendu de la campagne', 'status' => 'published',
            'published_at' => now()->subHour(), 'category' => 'Évangélisation', 'tags' => ['mission'], 'is_featured' => true, 'author' => 'Rédaction',
        ]);
        News::create(['title' => 'Kits scolaires', 'content' => 'Texte', 'status' => 'published', 'published_at' => now()->subHours(3), 'category' => 'Communauté']);

        $this->resource = Resource::create([
            'title' => 'Vers Christ', 'author' => 'Ellen G. White', 'category' => 'livre', 'status' => 'published',
            'file_path' => 'ressources/vers-christ.pdf', 'file_name' => 'vers-christ.pdf', 'file_type' => 'pdf', 'file_size' => 2048000,
            'published_at' => now()->subDay(),
        ]);

        // Instagram omis : la contrainte CHECK SQLite de la migration d'origine ne l'accepte pas en base de test
        foreach (['youtube' => 'PT17M18S', 'facebook' => null, 'tiktok' => null] as $pf => $duration) {
            Video::create([
                'title' => "Vidéo {$pf} d&#39;espérance", 'platform' => $pf, 'video_id' => 'vid' . $pf, 'post_url' => "https://example.org/{$pf}",
                'thumbnail_url' => 'https://i.ytimg.com/vi/x/mqdefault.jpg', 'duration' => $duration, 'view_count' => 120,
                'published_at' => now()->subDays(3),
            ]);
        }

        HeroSlide::create(['image' => 'images/hero-bible.jpg', 'title' => 'Bible', 'sort_order' => 1, 'is_active' => true]);
        HeroSlide::create(['image' => 'images/hero-hope.jpg', 'title' => 'Espérance', 'sort_order' => 2, 'is_active' => true]);
    }

    public function test_every_public_page_renders_with_the_new_design(): void
    {
        $video = Video::where('platform', 'youtube')->first();

        foreach ([
            route('home'), route('posts.index'), route('posts.show', $this->post->slug),
            route('posts.category', $this->category->slug), route('posts.by-type', 'video'),
            route('posts.search', ['q' => 'Sabbat']), route('videos.index'), route('videos.index', ['platform' => 'tiktok']),
            route('videos.show', $video), route('news.index'), route('news.index', ['categorie' => 'Communauté']),
            route('news.show', $this->news->slug), route('ressources.index'), route('ressources.show', $this->resource->slug),
            route('about'), route('contact.create'), route('contact.prayer'),
        ] as $url) {
            $this->get($url)->assertOk()->assertSee('La Parole qui éclaire')->assertSee('og:image', false);
        }
    }

    public function test_home_shows_videos_publications_and_hero_slides(): void
    {
        $this->get(route('home'))
            ->assertSee('À la une')
            ->assertSee('Regarder')
            ->assertSee('Nouveautés')
            ->assertSee('Enseignements')
            ->assertSee('Écho de la mission')
            ->assertSee("Vidéo youtube d'espérance")
            ->assertDontSee('&amp;#039;', false)
            ->assertSee('17:18')
            ->assertSee('Campagne à Matadi')
            ->assertSee('Le Sabbat, un cadeau')
            ->assertSee('images/hero-hope.jpg', false)
            ->assertSee('Flash info');
    }

    public function test_post_page_keeps_media_comments_replies_and_likes(): void
    {
        $this->get(route('posts.show', $this->post->slug))
            ->assertSee('youtube.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('Merci pour ce message')
            ->assertSee('Merci Marie')
            ->assertSee(route('likes.toggle', $this->post), false)
            ->assertSee(route('comments.store', $this->post), false);
    }

    public function test_likes_and_comments_still_work(): void
    {
        $this->postJson(route('likes.toggle', $this->post))->assertOk()->assertJson(['success' => true, 'likes' => 1, 'isLiked' => true]);
        $this->postJson(route('likes.news.toggle', $this->news))->assertOk()->assertJson(['success' => true, 'isLiked' => true]);

        $this->post(route('comments.news.store', $this->news), ['author_name' => 'Jean', 'content' => 'Belle nouvelle'])
            ->assertRedirect()->assertSessionHas('comment_success');
        $this->followingRedirects()->get(route('news.show', $this->news->slug))->assertSee('Belle nouvelle');
    }

    public function test_prayer_and_newsletter_still_work(): void
    {
        $this->postJson(route('contact.prayer.submit'), ['name' => 'Marie', 'email' => 'marie@example.com', 'message' => 'Priez pour ma famille.'])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('contacts', ['email' => 'marie@example.com', 'request_type' => 'prayer']);

        $this->from(route('home'))->post(route('newsletter.subscribe'), ['email' => 'lecteur@example.com'])
            ->assertRedirect(route('home'))->assertSessionHas('newsletter_success');
    }

    public function test_contact_form_shows_errors_in_the_new_design(): void
    {
        $this->from(route('contact.create'))->post(route('contact.store'), [])->assertRedirect(route('contact.create'));
        $this->followingRedirects()->from(route('contact.create'))->post(route('contact.store'), [])
            ->assertSee('Vérifiez les champs signalés');
    }
}
