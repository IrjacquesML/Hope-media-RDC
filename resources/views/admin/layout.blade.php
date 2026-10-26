<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Hope Media RDC</title>
    
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
    </style>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="flex h-screen bg-slate-100">
        <!-- Fond sombre derrière le menu ouvert (mobile) -->
        <div id="adminOverlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

        <!-- Sidebar : menu coulissant sous 1024 px, fixe au-dessus -->
        <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-40 w-64 flex-shrink-0 overflow-y-auto pb-24 transform -translate-x-full transition-transform duration-200 lg:relative lg:translate-x-0 bg-gradient-to-b from-blue-600 to-blue-800 text-white shadow-lg">
            <div class="p-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-2xl font-bold hover:opacity-80">
                    <i class="fas fa-shield-alt"></i>
                    <span>Admin Panel</span>
                </a>
            </div>
            
            <nav class="mt-8 space-y-2 px-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.posts*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-file-alt"></i>
                    <span>Posts</span>
                </a>

                <a href="{{ route('admin.hero-slides.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.hero-slides*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-images"></i>
                    <span>Carrousel Accueil</span>
                </a>
                
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.categories*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-folder"></i>
                    <span>Catégories</span>
                </a>
                
                <a href="{{ route('admin.medias.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.medias*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-video"></i>
                    <span>Médias</span>
                </a>

                <a href="{{ route('admin.videos.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.videos*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-sync-alt"></i>
                    <span>Sync Réseaux</span>
                </a>

                <a href="{{ route('admin.social.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.social*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-share-nodes"></i>
                    <span>Réseaux sociaux</span>
                </a>
                
                <a href="{{ route('admin.contacts.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.contacts*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>

                <a href="{{ route('admin.comments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.comments*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-comments"></i>
                    <span>Commentaires</span>
                </a>
                
                <a href="{{ route('admin.news.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.news*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-newspaper"></i>
                    <span>Écho de la mission</span>
                </a>
                
                <a href="{{ route('admin.newsletter.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.newsletter*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Newsletter</span>
                </a>
                
                <a href="{{ route('admin.resources.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.resources*') ? 'bg-blue-900' : 'hover:bg-blue-700' }} transition">
                    <i class="fas fa-book-open"></i>
                    <span>Ressources</span>
                </a>
            </nav>
            
            <div class="absolute bottom-6 left-4 right-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition w-full">
                    <i class="fas fa-arrow-left"></i>
                    <span>Site Public</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 min-w-0 overflow-auto">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm border-b border-slate-200 px-4 lg:px-8 py-3 lg:py-4 flex justify-between items-center gap-3 sticky top-0 z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="adminMenuBtn" class="lg:hidden w-11 h-11 -ml-2 rounded-lg flex items-center justify-center text-slate-700 hover:bg-slate-100" aria-label="Ouvrir le menu" aria-controls="adminSidebar" aria-expanded="false">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg lg:text-2xl font-bold text-slate-900 truncate">@yield('page_title')</h1>
                </div>
                <div class="flex items-center gap-4 flex-shrink-0">
                    <span class="text-slate-600 hidden sm:inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                    <div class="relative group">
                        <button type="button" class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold cursor-pointer hover:bg-blue-700 transition" aria-label="Compte">
                            <i class="fas fa-user"></i>
                        </button>
                        <!-- Dropdown menu (survol sur ordinateur, toucher sur mobile) -->
                        <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus-within:opacity-100 group-focus-within:visible transition duration-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-200">
                                <p class="text-sm text-slate-900 font-semibold">{{ Auth::user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-500">{{ Auth::user()->email ?? 'admin@hopemedia.cd' }}</p>
                            </div>
                            <form action="{{ route('auth.logout') }}" method="POST" class="p-2">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition text-sm">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Déconnexion</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="p-4 lg:p-8">
                <!-- Flash Messages -->
                @if($message = Session::get('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center gap-3">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $message }}</span>
                    </div>
                @endif
                
                @if($message = Session::get('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-3">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $message }}</span>
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
