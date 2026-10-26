<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Media;
use App\Models\Contact;
use App\Models\Comment;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\NewsletterSubscription;
use App\Models\Resource;
use App\Models\Video;
use App\Helpers\MediaHelper;
use App\Services\SocialMediaFetchService;
use App\Services\YouTubeApiService;
use App\Services\FacebookSyncService;
use App\Services\InstagramSyncService;
use App\Services\TikTokSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware simple pour vérifier l'accès admin
        // À améliorer avec une authentification real
    }

    // DASHBOARD
    public function dashboard()
    {
        $stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'total_categories' => Category::count(),
            'total_medias' => Media::count(),
            'total_videos' => Video::count(),
            'new_contacts' => Contact::where('status', 'new')->count(),
            'total_news' => News::count(),
            'published_news' => News::where('status', 'published')->count(),
            'newsletter_subscribers' => NewsletterSubscription::where('is_active', true)->count(),
        ];

        $recent_posts = Post::with('category')->orderBy('created_at', 'desc')->take(5)->get();
        $recent_contacts = Contact::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_posts', 'recent_contacts'));
    }

    // POSTS
    public function posts()
    {
        $posts = Post::with('category')->paginate(15);
        return view('admin.posts.index', compact('posts'));
    }

    public function createPost()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Endpoint AJAX : récupère les métadonnées d'un contenu depuis un réseau social.
     * Retourne du JSON.
     */
    public function fetchSocialMedia(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url|max:2048',
        ]);

        $service = new SocialMediaFetchService();
        $result  = $service->fetchContent($request->input('url'));

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'author' => 'nullable|string|max:255',
            'published_at' => 'nullable|date_format:Y-m-d\\TH:i',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'content_type' => 'required|in:text,video,audio,image',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg|max:512000',
            'video_description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'image_file' => 'nullable|file|mimes:jpeg,png,webp|max:51200',
            'image_description' => 'nullable|string',
            'audio_url' => 'nullable|url',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:102400',
            'audio_description' => 'nullable|string',
        ]);

        // Gérer l'upload de featured_image
        if ($request->hasFile('featured_image')) {
            $this->ensureDirectory(public_path('images/posts'));
            $file = $request->file('featured_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('images/posts'), $filename);
            $validated['featured_image'] = 'images/posts/' . $filename;
        }

        // Contenu obligatoire en DB : jamais null
        if ($validated['content_type'] !== 'text') {
            $validated['content'] = $validated['content_type'] === 'video'
                ? ($validated['video_description'] ?? '')
                : ($validated['content_type'] === 'image'
                    ? ($validated['image_description'] ?? '')
                    : ($validated['audio_description'] ?? ''));
        }
        $validated['content'] = $validated['content'] ?? '';

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now()->format('Y-m-d\TH:i');
        }

        $post = Post::create($validated);

        $this->syncPostMedia($request, $post, $validated);

        return redirect()->route('admin.posts.index')->with('success', 'Post créé avec succès!');
    }

    public function editPost(Post $post)
    {
        $categories = Category::all();
        $post->load('medias');
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function updatePost(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'author' => 'nullable|string|max:255',
            'published_at' => 'nullable|date_format:Y-m-d\\TH:i',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'content_type' => 'required|in:text,video,audio,image',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg|max:512000',
            'video_description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'image_file' => 'nullable|file|mimes:jpeg,png,webp|max:51200',
            'image_description' => 'nullable|string',
            'audio_url' => 'nullable|url',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:102400',
            'audio_description' => 'nullable|string',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image && file_exists(public_path($post->featured_image))) {
                @unlink(public_path($post->featured_image));
            }
            $this->ensureDirectory(public_path('images/posts'));
            $file = $request->file('featured_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('images/posts'), $filename);
            $validated['featured_image'] = 'images/posts/' . $filename;
        }

        if ($validated['content_type'] !== 'text') {
            $validated['content'] = $validated['content_type'] === 'video'
                ? ($validated['video_description'] ?? '')
                : ($validated['content_type'] === 'image'
                    ? ($validated['image_description'] ?? '')
                    : ($validated['audio_description'] ?? ''));
        }
        $validated['content'] = $validated['content'] ?? '';

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now()->format('Y-m-d\TH:i');
        }

        $post->update($validated);

        // Remplacer les médias uniquement si une nouvelle source est fournie
        $hasNewMedia = $request->hasFile('video_file')
            || $request->filled('video_url')
            || $request->hasFile('image_file')
            || $request->filled('image_url')
            || $request->hasFile('audio_file')
            || $request->filled('audio_url')
            || $validated['content_type'] === 'text';

        if ($hasNewMedia) {
            $this->deleteLocalMediaFiles($post);
            $post->medias()->delete();
            $this->syncPostMedia($request, $post, $validated);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post mis à jour!');
    }

    public function deletePost(Post $post)
    {
        if ($post->featured_image && file_exists(public_path($post->featured_image))) {
            @unlink(public_path($post->featured_image));
        }
        $this->deleteLocalMediaFiles($post);
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Post supprimé!');
    }

    // CATEGORIES
    public function categories()
    {
        $categories = Category::withCount('allPosts as posts_count')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'type' => 'required|in:enseignement,service',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée!');
    }

    public function editCategory(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'type' => 'required|in:enseignement,service',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour!');
    }

    public function deleteCategory(Category $category)
    {
        if ($category->allPosts()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Impossible de supprimer une catégorie qui contient des posts.');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée!');
    }

    // MEDIAS
    public function medias()
    {
        $medias = Media::with('post')->paginate(15);
        return view('admin.medias.index', compact('medias'));
    }

    public function deleteMedia(Media $media)
    {
        if ($media->media_type === 'local' && $media->url && ! str_starts_with($media->url, 'http')) {
            Storage::disk('public')->delete($media->url);
        }
        $media->delete();
        return redirect()->route('admin.medias.index')->with('success', 'Média supprimé!');
    }

    // VIDEOS / SYNC RESEAUX
    public function videos(Request $request)
    {
        $query = Video::query()->orderByDesc('published_at');
        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }
        $videos = $query->paginate(15)->withQueryString();
        $counts = Video::query()
            ->selectRaw('platform, COUNT(*) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform')
            ->toArray();

        return view('admin.videos.index', compact('videos', 'counts'));
    }

    public function syncVideos(string $platform)
    {
        if (! in_array($platform, \App\Support\SocialPlatforms::keys(), true)) {
            return back()->with('error', 'Plateforme inconnue.');
        }
        $label = \App\Support\SocialPlatforms::get($platform)['label'];

        try {
            // Identifiants saisis dans Admin → Réseaux sociaux (repli .env) ; le résultat y est enregistré.
            $count = app(\App\Services\SocialSyncManager::class)->sync($platform);

            return back()->with('success', "{$label} : {$count} vidéo(s) synchronisée(s).");
        } catch (\Throwable $e) {
            return back()->with('error', "Échec de la synchronisation {$label} : " . $e->getMessage());
        }
    }

    public function deleteVideo(Video $video)
    {
        $video->delete();
        return back()->with('success', 'Vidéo supprimée!');
    }

    // CONTACTS
    public function contacts()
    {
        $contacts = Contact::orderByDesc('created_at')->paginate(15);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function viewContact(Contact $contact)
    {
        $contact->markAsRead();
        return view('admin.contacts.show', compact('contact'));
    }

    public function replyContact(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'reply' => 'required|string',
        ]);

        $contact->markAsReplied($validated['reply']);

        $mailSent = false;
        if (filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::raw(
                    "Bonjour {$contact->name},\n\n{$validated['reply']}\n\n— Hope Media RDC",
                    function ($message) use ($contact) {
                        $message->to($contact->email, $contact->name)
                            ->subject('Réponse — Hope Media RDC');
                    }
                );
                $mailSent = true;
            } catch (\Throwable $e) {
                Log::warning('Contact reply mail failed', ['error' => $e->getMessage()]);
            }
        }

        $msg = $mailSent
            ? 'Réponse enregistrée et e-mail envoyé!'
            : 'Réponse enregistrée (e-mail non envoyé — vérifiez la config mail).';

        return redirect()->route('admin.contacts.index')->with('success', $msg);
    }

    public function archiveContact(Contact $contact)
    {
        $contact->archive();
        return redirect()->route('admin.contacts.index')->with('success', 'Contact archivé!');
    }

    // ÉCHO DE LA MISSION (NEWS)
    public function news(Request $request)
    {
        $query = News::orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('statut')) {
            $query->where('status', $status);
        }

        $news = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => News::count(),
            'published' => News::where('status', 'published')->count(),
            'draft'     => News::where('status', 'draft')->count(),
            'archived'  => News::where('status', 'archived')->count(),
            'views'     => News::sum('views'),
        ];

        return view('admin.news.index', compact('news', 'stats'));
    }

    public function createNews()
    {
        return view('admin.news.create');
    }

    public function storeNews(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'excerpt'        => 'nullable|string|max:500',
            'author'         => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'tags'           => 'nullable|string|max:500',
            'status'         => 'required|in:draft,published,archived',
            'published_at'   => 'nullable|date_format:Y-m-d\TH:i',
            'featured_image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'is_featured'    => 'nullable|boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            $this->ensureDirectory(public_path('images/news'));
            $file = $request->file('featured_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('images/news'), $filename);
            $validated['featured_image'] = 'images/news/' . $filename;
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        } else {
            $validated['tags'] = null;
        }

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now()->format('Y-m-d\TH:i');
        }

        try {
            News::create($validated);
        } catch (\Exception $e) {
            Log::error('storeNews failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Une erreur est survenue lors de la création de l\'article. Veuillez réessayer.');
        }

        return redirect()->route('admin.news.index')->with('success', 'Article créé avec succès !');
    }

    public function editNews(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    public function updateNews(Request $request, News $news)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'excerpt'        => 'nullable|string|max:500',
            'author'         => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'tags'           => 'nullable|string|max:500',
            'status'         => 'required|in:draft,published,archived',
            'published_at'   => 'nullable|date_format:Y-m-d\TH:i',
            'featured_image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'is_featured'    => 'nullable|boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($news->featured_image && file_exists(public_path($news->featured_image))) {
                @unlink(public_path($news->featured_image));
            }
            $this->ensureDirectory(public_path('images/news'));
            $file = $request->file('featured_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('images/news'), $filename);
            $validated['featured_image'] = 'images/news/' . $filename;
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        } else {
            $validated['tags'] = null;
        }

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now()->format('Y-m-d\TH:i');
        }

        try {
            $news->update($validated);
        } catch (\Exception $e) {
            Log::error('updateNews failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.');
        }

        return redirect()->route('admin.news.index')->with('success', 'Article mis à jour avec succès !');
    }

    public function deleteNews(News $news)
    {
        if ($news->featured_image && file_exists(public_path($news->featured_image))) {
            @unlink(public_path($news->featured_image));
        }
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'Article supprimé!');
    }

    // CAROUSEL ACCUEIL (Hero slides)
    public function heroSlides()
    {
        $slides = HeroSlide::ordered()->paginate(20);
        $stats = [
            'total'  => HeroSlide::count(),
            'active' => HeroSlide::where('is_active', true)->count(),
        ];

        return view('admin.hero-slides.index', compact('slides', 'stats'));
    }

    public function createHeroSlide()
    {
        return view('admin.hero-slides.create');
    }

    public function storeHeroSlide(Request $request)
    {
        $validated = $request->validate([
            'image'      => 'required|image|mimes:jpeg,png,webp|max:5120',
            'title'      => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active'  => 'nullable|boolean',
        ]);

        $this->ensureDirectory(public_path('images/hero'));
        $file = $request->file('image');
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $file->move(public_path('images/hero'), $filename);
        $validated['image'] = 'images/hero/' . $filename;
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        HeroSlide::create($validated);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Image ajoutée au carrousel !');
    }

    public function editHeroSlide(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', compact('heroSlide'));
    }

    public function updateHeroSlide(Request $request, HeroSlide $heroSlide)
    {
        $validated = $request->validate([
            'image'      => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'title'      => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active'  => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($heroSlide->image && file_exists(public_path($heroSlide->image))) {
                @unlink(public_path($heroSlide->image));
            }
            $this->ensureDirectory(public_path('images/hero'));
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('images/hero'), $filename);
            $validated['image'] = 'images/hero/' . $filename;
        }

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        $heroSlide->update($validated);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Image du carrousel mise à jour !');
    }

    public function deleteHeroSlide(HeroSlide $heroSlide)
    {
        if ($heroSlide->image && file_exists(public_path($heroSlide->image))) {
            @unlink(public_path($heroSlide->image));
        }
        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')->with('success', 'Image du carrousel supprimée !');
    }

    // COMMENTAIRES (modération + réponses admin)
    public function comments(Request $request)
    {
        $query = Comment::query()
            ->root()
            ->with(['commentable', 'replies'])
            ->orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->get('sans_reponse') === '1') {
            $query->whereDoesntHave('replies');
        }

        $comments = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Comment::root()->count(),
            'unanswered' => Comment::root()->whereDoesntHave('replies')->count(),
            'replies' => Comment::whereNotNull('parent_id')->count(),
        ];

        return view('admin.comments.index', compact('comments', 'stats'));
    }

    public function replyComment(Request $request, Comment $comment)
    {
        if ($comment->parent_id) {
            return back()->with('error', 'Vous ne pouvez répondre qu\'à un commentaire principal.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $adminName = Auth::user()?->name ?: 'Hope Media RDC';

        Comment::create([
            'parent_id'        => $comment->id,
            'commentable_type' => $comment->commentable_type,
            'commentable_id'   => $comment->commentable_id,
            'author_name'      => $adminName,
            'author_email'     => Auth::user()?->email,
            'content'          => $validated['content'],
            'is_approved'      => true,
            'is_admin_reply'   => true,
        ]);

        return back()->with('success', 'Réponse publiée avec succès !');
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.comments.index')->with('success', 'Commentaire supprimé !');
    }

    // NEWSLETTER
    public function newsletter()
    {
        $subscribers = NewsletterSubscription::orderByDesc('created_at')->paginate(20);
        return view('admin.newsletter.index', compact('subscribers'));
    }

    public function deleteSubscriber(NewsletterSubscription $subscriber)
    {
        $subscriber->delete();
        return redirect()->route('admin.newsletter.index')->with('success', 'Abonné supprimé!');
    }

    // RESSOURCES (livres & ouvrages)
    public function resources()
    {
        $resources = Resource::orderByDesc('created_at')->paginate(15);
        return view('admin.resources.index', compact('resources'));
    }

    public function createResource()
    {
        $categories = Resource::categoryLabels();
        return view('admin.resources.create', compact('categories'));
    }

    public function storeResource(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'author'         => 'nullable|string|max:255',
            'publisher'      => 'nullable|string|max:255',
            'category'       => 'required|in:livre,ouvrage,manuel,brochure,autre',
            'published_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'status'         => 'required|in:draft,published',
            'published_at'   => 'nullable|date_format:Y-m-d\TH:i',
            'is_featured'    => 'nullable|boolean',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'file'           => 'required|file|mimes:pdf,epub,docx|max:51200',
        ]);

        if ($request->hasFile('cover_image')) {
            $this->ensureDirectory(public_path('images/resources'));
            $img = $request->file('cover_image');
            $imgName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $img->getClientOriginalName());
            $img->move(public_path('images/resources'), $imgName);
            $validated['cover_image'] = 'images/resources/' . $imgName;
        }

        $file = $request->file('file');
        $filePath = $file->store('resources', 'public');
        $validated['file_path'] = $filePath;
        $validated['file_name'] = $file->getClientOriginalName();
        $validated['file_size'] = $file->getSize();
        $validated['file_type'] = $file->getClientOriginalExtension();
        $validated['is_featured'] = $request->boolean('is_featured');

        Resource::create($validated);

        return redirect()->route('admin.resources.index')->with('success', 'Ressource ajoutée avec succès!');
    }

    public function editResource(Resource $resource)
    {
        $categories = Resource::categoryLabels();
        return view('admin.resources.edit', compact('resource', 'categories'));
    }

    public function updateResource(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'author'         => 'nullable|string|max:255',
            'publisher'      => 'nullable|string|max:255',
            'category'       => 'required|in:livre,ouvrage,manuel,brochure,autre',
            'published_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'status'         => 'required|in:draft,published',
            'published_at'   => 'nullable|date_format:Y-m-d\TH:i',
            'is_featured'    => 'nullable|boolean',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'file'           => 'nullable|file|mimes:pdf,epub,docx|max:51200',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($resource->cover_image && file_exists(public_path($resource->cover_image))) {
                @unlink(public_path($resource->cover_image));
            }
            $this->ensureDirectory(public_path('images/resources'));
            $img = $request->file('cover_image');
            $imgName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $img->getClientOriginalName());
            $img->move(public_path('images/resources'), $imgName);
            $validated['cover_image'] = 'images/resources/' . $imgName;
        }

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($resource->file_path);
            $file = $request->file('file');
            $filePath = $file->store('resources', 'public');
            $validated['file_path'] = $filePath;
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $validated['file_type'] = $file->getClientOriginalExtension();
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $resource->update($validated);

        return redirect()->route('admin.resources.index')->with('success', 'Ressource mise à jour!');
    }

    public function deleteResource(Resource $resource)
    {
        Storage::disk('public')->delete($resource->file_path);
        if ($resource->cover_image && file_exists(public_path($resource->cover_image))) {
            @unlink(public_path($resource->cover_image));
        }
        $resource->delete();
        return redirect()->route('admin.resources.index')->with('success', 'Ressource supprimée!');
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function syncPostMedia(Request $request, Post $post, array $validated): void
    {
        if ($validated['content_type'] === 'video') {
            $mediaUrl = null;
            $mediaType = 'local';

            if ($request->hasFile('video_file')) {
                $mediaUrl = $request->file('video_file')->store('medias/videos', 'public');
                $mediaType = 'local';
            } elseif (! empty($validated['video_url'])) {
                $mediaUrl = $validated['video_url'];
                $mediaType = MediaHelper::getMediaSourceType($mediaUrl);
            }

            if ($mediaUrl) {
                Media::create([
                    'post_id' => $post->id,
                    'media_type' => $mediaType,
                    'title' => $post->title,
                    'description' => $validated['video_description'] ?? '',
                    'url' => $mediaUrl,
                    'is_active' => true,
                ]);
            }
        } elseif ($validated['content_type'] === 'image') {
            $mediaUrl = null;
            if ($request->hasFile('image_file')) {
                $mediaUrl = $request->file('image_file')->store('medias/images', 'public');
            } elseif (! empty($validated['image_url'])) {
                $mediaUrl = $validated['image_url'];
            }

            if ($mediaUrl) {
                Media::create([
                    'post_id' => $post->id,
                    'media_type' => 'local',
                    'title' => $post->title,
                    'description' => $validated['image_description'] ?? '',
                    'url' => $mediaUrl,
                    'is_active' => true,
                ]);
            }
        } elseif ($validated['content_type'] === 'audio') {
            $mediaUrl = null;
            if ($request->hasFile('audio_file')) {
                $mediaUrl = $request->file('audio_file')->store('medias/audios', 'public');
            } elseif (! empty($validated['audio_url'])) {
                $mediaUrl = $validated['audio_url'];
            }

            if ($mediaUrl) {
                Media::create([
                    'post_id' => $post->id,
                    'media_type' => 'local',
                    'title' => $post->title,
                    'description' => $validated['audio_description'] ?? '',
                    'url' => $mediaUrl,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function deleteLocalMediaFiles(Post $post): void
    {
        foreach ($post->medias as $media) {
            if ($media->media_type === 'local' && $media->url && ! str_starts_with($media->url, 'http')) {
                Storage::disk('public')->delete($media->url);
            }
        }
    }
}
