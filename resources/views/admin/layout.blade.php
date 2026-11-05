@php
    $me = Auth::user();
    $badges = [];
    if ($me?->hasRole('admin')) {
        try {
            $badges = \Illuminate\Support\Facades\Cache::remember('admin.badges', 60, fn () => [
                'contacts' => \App\Models\Contact::where('status', 'new')->count(),
                'comments' => \App\Models\Comment::where('is_approved', false)->where('is_admin_reply', false)->count(),
            ]);
        } catch (\Throwable) {
            $badges = [];
        }
    }

    // Menu : [section, [[route, motif actif, icône, libellé, rôle minimum, badge, paramètres]]]
    $menu = [
        [null, [
            ['admin.dashboard', 'admin.dashboard', 'fa-gauge-high', 'Tableau de bord', 'editor'],
        ]],
        ['Contenus', [
            ['admin.posts.index', 'admin.posts*', 'fa-book-open', 'Enseignements', 'editor'],
            ['admin.news.index', 'admin.news*', 'fa-newspaper', 'Écho de la mission', 'editor'],
            ['admin.videos.index', 'admin.videos*', 'fa-circle-play', 'Vidéos', 'editor'],
            ['admin.resources.index', 'admin.resources*', 'fa-book', 'Ressources', 'editor'],
            ['admin.hero-slides.index', 'admin.hero-slides*', 'fa-images', 'Carrousel d’accueil', 'editor'],
            ['admin.categories.index', 'admin.categories*', 'fa-folder-tree', 'Catégories', 'editor'],
            ['admin.medias.index', 'admin.medias*', 'fa-photo-film', 'Médias', 'editor'],
        ]],
        ['Audience', [
            ['admin.contacts.index', 'admin.contacts*', 'fa-envelope', 'Messages et prières', 'admin', $badges['contacts'] ?? 0],
            ['admin.comments.index', 'admin.comments*', 'fa-comments', 'Commentaires', 'admin', $badges['comments'] ?? 0],
            ['admin.newsletter.index', 'admin.newsletter*', 'fa-envelope-open-text', 'Newsletter', 'admin'],
        ]],
        ['Diffusion', [
            ['admin.social.index', 'admin.social*', 'fa-share-nodes', 'Synchronisation des réseaux', 'admin'],
            ['admin.reports.index', 'admin.reports*', 'fa-file-pdf', 'Rapports PDF', 'admin'],
        ]],
        ['Administration', [
            ['admin.settings.index', 'admin.settings*', 'fa-sliders', 'Paramètres du site', 'super_admin', 0, []],
            ['admin.users.index', 'admin.users*', 'fa-users-gear', 'Comptes', 'super_admin'],
        ]],
    ];
    $logo = $site->logo_dark ? asset($site->logo_dark) : null;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Admin {{ $site->site_name }}</title>
    @if ($site->favicon)<link rel="icon" href="{{ asset($site->favicon) }}">@endif

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Téléphone : chaque ligne de tableau devient une fiche empilée (aucun défilement horizontal).
           Les colonnes « hidden md:table-cell » restent masquées (la classe .hidden l’emporte). */
        @media (max-width: 767px) {
            main table, main tbody, main tr { display: block; width: 100%; }
            main thead { display: none; }
            main tr { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; }
            main tr > td { display: block; padding: 3px 0 !important; border: 0; max-width: none; white-space: normal; overflow-wrap: anywhere; }
            main tr > td:last-child { padding-top: 8px !important; }
            main td .truncate { white-space: normal; }
        }
        @media (max-width: 1023px) {
            main td a, main td button { display: inline-flex; min-width: 36px; min-height: 36px; align-items: center; justify-content: center; }
            /* Liens texte et petites icônes : zone tactile d'au moins 36 px */
            main a:not([class*="flex"]):not([class*="grid"]):not(.block):not(.hidden) { display: inline-flex; align-items: center; min-height: 36px; }
            main a[class*="inline-flex"] { min-height: 36px; }
            main button { min-height: 36px; }
            main button:not([class*="w-full"]) { min-width: 36px; }
            input[type="checkbox"], input[type="radio"] { width: 20px !important; height: 20px !important; }
        }
        img, video, iframe { max-width: 100%; }
        .side-link[aria-current="page"] { background: rgba(252, 163, 17, .14); color: #fff; box-shadow: inset 3px 0 0 #FCA311; }
        .side-link[aria-current="page"] i { color: #FCA311; }
        #adminSidebar::-webkit-scrollbar { width: 6px; }
        #adminSidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 3px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="flex h-screen bg-slate-100">
        <!-- Fond sombre derrière le menu ouvert (mobile) -->
        <div id="adminOverlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

        <!-- Menu latéral : coulissant sous 1024 px, fixe au-dessus -->
        <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-40 w-72 flex-shrink-0 overflow-y-auto flex flex-col transform -translate-x-full transition-transform duration-200 lg:relative lg:translate-x-0 bg-[#0B1530] text-slate-300">
            <div class="px-5 pt-5 pb-4 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 hover:opacity-90">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="" class="h-11 w-auto">
                    @else
                        <span class="w-11 h-11 rounded-lg bg-amber-400 text-[#0B1530] flex items-center justify-center font-black">HM</span>
                    @endif
                    <span class="leading-tight">
                        <span class="block text-white font-bold">{{ $site->site_name }}</span>
                        <span class="block text-xs text-slate-400">Administration</span>
                    </span>
                </a>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-5" aria-label="Menu d'administration">
                @foreach ($menu as [$section, $items])
                    @php $visible = array_filter($items, fn ($i) => $me?->hasRole($i[4])); @endphp
                    @if ($visible)
                        <div>
                            @if ($section)
                                <p class="px-3 mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $section }}</p>
                            @endif
                            <div class="space-y-0.5">
                                @foreach ($visible as $item)
                                    @php [$route, $pattern, $icon, $label] = $item; $badge = $item[5] ?? 0; @endphp
                                    <a href="{{ route($route) }}" class="side-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/5 hover:text-white transition"
                                       @if (request()->routeIs($pattern)) aria-current="page" @endif>
                                        <i class="fas {{ $icon }} w-5 text-center text-slate-400"></i>
                                        <span class="flex-1">{{ $label }}</span>
                                        @if ($badge > 0)
                                            <span class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-amber-400 text-[#0B1530] text-xs font-bold flex items-center justify-center">{{ $badge > 99 ? '99+' : $badge }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            <div class="p-3 border-t border-white/10">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/5 hover:text-white transition">
                    <i class="fas fa-arrow-up-right-from-square w-5 text-center text-slate-400"></i>
                    <span>Voir le site public</span>
                </a>
            </div>
        </aside>

        <!-- Contenu principal -->
        <main class="flex-1 min-w-0 overflow-auto">
            <!-- Barre du haut -->
            <div class="bg-white/95 backdrop-blur border-b border-slate-200 px-4 lg:px-8 py-3 flex justify-between items-center gap-3 sticky top-0 z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="adminMenuBtn" class="lg:hidden w-11 h-11 -ml-2 rounded-lg flex items-center justify-center text-slate-700 hover:bg-slate-100" aria-label="Ouvrir le menu" aria-controls="adminSidebar" aria-expanded="false">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg lg:text-2xl font-bold text-slate-900 truncate">@yield('page_title')</h1>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition">
                        <i class="fas fa-globe"></i> Voir le site
                    </a>
                    <div class="relative group">
                        <button type="button" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-slate-100 transition" aria-label="Mon compte" aria-haspopup="true">
                            <span class="w-9 h-9 rounded-full bg-[#1F3A93] text-white flex items-center justify-center text-sm font-bold">{{ $me?->initials }}</span>
                            <span class="hidden sm:flex flex-col items-start leading-tight text-left">
                                <span class="text-sm font-semibold text-slate-800 max-w-[160px] truncate">{{ $me?->name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $me?->role_label }}</span>
                            </span>
                            <i class="fas fa-chevron-down text-xs text-slate-400 hidden sm:inline"></i>
                        </button>
                        <div class="absolute right-0 mt-1 w-60 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus-within:opacity-100 group-focus-within:visible transition duration-150 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm text-slate-900 font-semibold truncate">{{ $me?->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $me?->email }}</p>
                                <span class="inline-block mt-2 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $me?->role_color }}">{{ $me?->role_label }}</span>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-lg">
                                    <i class="fas fa-user-pen w-4"></i> Mon profil et mot de passe
                                </a>
                                @if ($me?->isSuperAdmin())
                                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-lg">
                                        <i class="fas fa-sliders w-4"></i> Paramètres du site
                                    </a>
                                @endif
                                <form action="{{ route('auth.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fas fa-right-from-bracket w-4"></i> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu de la page -->
            <div class="p-4 lg:p-8">
                @if ($message = Session::get('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-300 text-green-800 rounded-xl flex items-start gap-3" role="status">
                        <i class="fas fa-circle-check mt-0.5"></i>
                        <span class="flex-1">{{ $message }}</span>
                        <button type="button" class="text-green-700/60 hover:text-green-900" onclick="this.parentElement.remove()" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
                    </div>
                @endif

                @if ($message = Session::get('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl flex items-start gap-3" role="alert">
                        <i class="fas fa-circle-exclamation mt-0.5"></i>
                        <span class="flex-1">{{ $message }}</span>
                        <button type="button" class="text-red-700/60 hover:text-red-900" onclick="this.parentElement.remove()" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    <script>
        // Menu admin coulissant (mobile)
        (() => {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('adminOverlay');
            const btn = document.getElementById('adminMenuBtn');
            const toggle = open => {
                sidebar.classList.toggle('-translate-x-full', !open);
                overlay.classList.toggle('hidden', !open);
                btn.setAttribute('aria-expanded', open);
                document.body.style.overflow = open ? 'hidden' : '';
            };
            btn?.addEventListener('click', () => toggle(sidebar.classList.contains('-translate-x-full')));
            overlay.addEventListener('click', () => toggle(false));
            document.addEventListener('keydown', e => { if (e.key === 'Escape') toggle(false); });
        })();
    </script>
    @stack('scripts')
</body>
</html>
