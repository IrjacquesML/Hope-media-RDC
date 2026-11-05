<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\AdminSocialController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminNewsletterController;
use App\Http\Controllers\AdminProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes publiques de Hope Media DRC
*/

// Page d'accueil = articles admin + contenus sociaux
Route::get('/', [HomeController::class, 'index'])->name('home');

// À propos
Route::get('/a-propos', [AboutController::class, 'index'])->name('about');

// Routes vidéos (affichage public)
Route::prefix('videos')->name('videos.')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('index');
    Route::get('/{video}', [VideoController::class, 'show'])->name('show');
});

// Enregistrement d'une vidéo (admin uniquement — conservé pour compatibilité)
Route::post('/admin/videos', [VideoController::class, 'store'])
    ->name('videos.store')
    ->middleware('auth');

// Routes enseignements (posts publics)
Route::prefix('enseignements')->name('posts.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('/recherche', [PostController::class, 'search'])->name('search');
    Route::get('/type/{type}', [PostController::class, 'byType'])->name('by-type');
    Route::get('/categorie/{slug}', [PostController::class, 'category'])->name('category');
    Route::get('/{slug}', [PostController::class, 'show'])->name('show');
});

Route::post('/enseignements/{post}/commentaires', [CommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('comments.store');

Route::post('/actualites/{news}/commentaires', [CommentController::class, 'storeNews'])
    ->middleware('throttle:10,1')
    ->name('comments.news.store');

// Likes (internautes, sans compte)
Route::post('/likes/toggle/{post}', [LikeController::class, 'toggleLike'])
    ->middleware('throttle:30,1')
    ->name('likes.toggle');
Route::get('/likes/check/{post}', [LikeController::class, 'checkLike'])
    ->name('likes.check');
Route::post('/likes/news/toggle/{news}', [LikeController::class, 'toggleNewsLike'])
    ->middleware('throttle:30,1')
    ->name('likes.news.toggle');
Route::get('/likes/news/check/{news}', [LikeController::class, 'checkNewsLike'])
    ->name('likes.news.check');

// Routes Actualités / Écho de la mission (News)
Route::prefix('actualites')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('show');
});

// Routes Ressources (livres & ouvrages)
Route::prefix('ressources')->name('ressources.')->group(function () {
    Route::get('/', [ResourceController::class, 'index'])->name('index');
    Route::get('/{resource}/telecharger', [ResourceController::class, 'download'])->name('download');
    Route::get('/{slug}', [ResourceController::class, 'show'])->name('show');
});

// Newsletter
Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::post('/abonner', [NewsletterController::class, 'subscribe'])->name('subscribe');
    Route::get('/desabonner/{token}', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
});

// Routes de contact et prière
Route::prefix('contact')->group(function () {
    // Formulaire de contact
    Route::get('/', [ContactController::class, 'create'])->name('contact.create');
    Route::post('/', [ContactController::class, 'store'])->name('contact.store');
    
    // Page de prière
    Route::get('/priere', [ContactController::class, 'prayer'])->name('contact.prayer');
    Route::post('/priere', [ContactController::class, 'submitPrayerRequest'])->name('contact.prayer.submit');
});

// ROUTES D'AUTHENTIFICATION
Route::prefix('auth')->name('auth.')->group(function () {
    // Routes accessibles seulement si non connecté
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    });
    
    // Routes accessibles seulement si connecté
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// Raccourci de connexion admin : /login (aucun lien visible sur le site public)
Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login');

// ROUTES ADMINISTRATION
// Trois niveaux d'accès (voir App\Models\User::ROLES) :
//   tous les comptes (rédacteur et plus) → contenus ; role:admin → audience, diffusion, rapports ;
//   role:super_admin → paramètres du site, partenaires, comptes.
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Dashboard et profil (tous les comptes)
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/mon-profil', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mon-profil', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/mon-profil/mot-de-passe', [AdminProfileController::class, 'password'])->name('profile.password');

    // ---------------- CONTENUS (rédacteur et plus) ----------------

    // Posts
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [AdminController::class, 'posts'])->name('index');
        Route::get('/create', [AdminController::class, 'createPost'])->name('create');
        Route::post('/', [AdminController::class, 'storePost'])->name('store');
        Route::get('/{post}/edit', [AdminController::class, 'editPost'])->name('edit');
        Route::put('/{post}', [AdminController::class, 'updatePost'])->name('update');
        Route::delete('/{post}', [AdminController::class, 'deletePost'])->name('delete');
    });

    // Import contenu réseaux sociaux (AJAX)
    Route::get('/social-media/fetch', [AdminController::class, 'fetchSocialMedia'])->name('social-media.fetch');

    // Catégories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminController::class, 'categories'])->name('index');
        Route::get('/create', [AdminController::class, 'createCategory'])->name('create');
        Route::post('/', [AdminController::class, 'storeCategory'])->name('store');
        Route::get('/{category}/edit', [AdminController::class, 'editCategory'])->name('edit');
        Route::put('/{category}', [AdminController::class, 'updateCategory'])->name('update');
        Route::delete('/{category}', [AdminController::class, 'deleteCategory'])->name('delete');
    });

    // Médias
    Route::prefix('medias')->name('medias.')->group(function () {
        Route::get('/', [AdminController::class, 'medias'])->name('index');
        Route::delete('/{media}', [AdminController::class, 'deleteMedia'])->name('delete');
    });

    // Vidéos (liste et suppression)
    Route::prefix('videos')->name('videos.')->group(function () {
        Route::get('/', [AdminController::class, 'videos'])->name('index');
        Route::delete('/{video}', [AdminController::class, 'deleteVideo'])->name('delete');
    });

    // Actualités (News)
    Route::prefix('news')->name('news.')->group(function () {
        Route::get('/', [AdminController::class, 'news'])->name('index');
        Route::get('/create', [AdminController::class, 'createNews'])->name('create');
        Route::post('/', [AdminController::class, 'storeNews'])->name('store');
        Route::get('/{news}/edit', [AdminController::class, 'editNews'])->name('edit');
        Route::put('/{news}', [AdminController::class, 'updateNews'])->name('update');
        Route::delete('/{news}', [AdminController::class, 'deleteNews'])->name('delete');
    });

    // Carrousel page d'accueil
    Route::prefix('carousel')->name('hero-slides.')->group(function () {
        Route::get('/', [AdminController::class, 'heroSlides'])->name('index');
        Route::get('/create', [AdminController::class, 'createHeroSlide'])->name('create');
        Route::post('/', [AdminController::class, 'storeHeroSlide'])->name('store');
        Route::get('/{heroSlide}/edit', [AdminController::class, 'editHeroSlide'])->name('edit');
        Route::put('/{heroSlide}', [AdminController::class, 'updateHeroSlide'])->name('update');
        Route::delete('/{heroSlide}', [AdminController::class, 'deleteHeroSlide'])->name('delete');
    });

    // Ressources (livres & ouvrages)
    Route::prefix('ressources')->name('resources.')->group(function () {
        Route::get('/', [AdminController::class, 'resources'])->name('index');
        Route::get('/create', [AdminController::class, 'createResource'])->name('create');
        Route::post('/', [AdminController::class, 'storeResource'])->name('store');
        Route::get('/{resource}/edit', [AdminController::class, 'editResource'])->name('edit');
        Route::put('/{resource}', [AdminController::class, 'updateResource'])->name('update');
        Route::delete('/{resource}', [AdminController::class, 'deleteResource'])->name('delete');
    });

    // ---------------- AUDIENCE ET DIFFUSION (administrateur et plus) ----------------
    Route::middleware('role:admin')->group(function () {
        // Rapports PDF
        Route::get('/rapports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::post('/rapports', [AdminReportController::class, 'generate'])->middleware('throttle:20,1')->name('reports.generate');

        // Synchronisation manuelle (ancien écran Vidéos)
        Route::post('/videos/sync/{platform}', [AdminController::class, 'syncVideos'])->name('videos.sync');

        // Réseaux sociaux : identifiants, test de connexion, synchronisation
        Route::prefix('reseaux-sociaux')->name('social.')->group(function () {
            Route::get('/', [AdminSocialController::class, 'index'])->name('index');
            Route::get('/tiktok/connecter', [AdminSocialController::class, 'tiktokConnect'])->name('tiktok.connect');
            Route::get('/tiktok/retour', [AdminSocialController::class, 'tiktokCallback'])->name('tiktok.callback');
            Route::post('/tout-synchroniser', [AdminSocialController::class, 'syncAll'])->name('sync-all');
            Route::put('/{platform}', [AdminSocialController::class, 'update'])->name('update');
            Route::post('/{platform}/test', [AdminSocialController::class, 'test'])->name('test');
            Route::post('/{platform}/sync', [AdminSocialController::class, 'sync'])->name('sync');
        });

        // Contacts
        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::get('/', [AdminController::class, 'contacts'])->name('index');
            Route::get('/{contact}', [AdminController::class, 'viewContact'])->name('show');
            Route::post('/{contact}/reply', [AdminController::class, 'replyContact'])->name('reply');
            Route::post('/{contact}/archive', [AdminController::class, 'archiveContact'])->name('archive');
        });

        // Commentaires
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::get('/', [AdminController::class, 'comments'])->name('index');
            Route::post('/{comment}/reply', [AdminController::class, 'replyComment'])->name('reply');
            Route::delete('/{comment}', [AdminController::class, 'deleteComment'])->name('delete');
        });

        // Newsletter
        Route::prefix('newsletter')->name('newsletter.')->group(function () {
            Route::get('/', [AdminNewsletterController::class, 'index'])->name('index');
            Route::get('/apercu', [AdminNewsletterController::class, 'preview'])->name('preview');
            Route::post('/test', [AdminNewsletterController::class, 'test'])->middleware('throttle:10,1')->name('test');
            Route::post('/envois/{campaign}/relancer', [AdminNewsletterController::class, 'retry'])->name('retry');
            Route::delete('/{subscriber}', [AdminController::class, 'deleteSubscriber'])->name('delete');
        });
    });

    // ---------------- PARAMÈTRES (super administrateur) ----------------
    Route::middleware('role:super_admin')->group(function () {
        Route::prefix('parametres')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
            Route::put('/groupe/{group}', [AdminSettingsController::class, 'update'])->name('update');
            Route::post('/email-de-test', [AdminSettingsController::class, 'testMail'])->middleware('throttle:10,1')->name('mail-test');
            Route::post('/partenaires', [AdminSettingsController::class, 'storePartner'])->name('partners.store');
            Route::put('/partenaires/{partner}', [AdminSettingsController::class, 'updatePartner'])->name('partners.update');
            Route::delete('/partenaires/{partner}', [AdminSettingsController::class, 'deletePartner'])->name('partners.delete');
        });

        Route::prefix('comptes')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/nouveau', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}/modifier', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('delete');
        });
    });
});
