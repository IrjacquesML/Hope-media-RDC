<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Hope Media RDC</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="flex h-screen bg-slate-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white shadow-lg">
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
        <main class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm border-b border-slate-200 px-8 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-slate-900">@yield('page_title')</h1>
                <div class="flex items-center gap-4">
                    <span class="text-slate-600">{{ Auth::user()->name ?? 'Admin' }}</span>
                    <div class="relative group">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold cursor-pointer hover:bg-blue-700 transition">
                            <i class="fas fa-user"></i>
                        </div>
                        <!-- Dropdown menu -->
                        <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200 z-50">
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
            <div class="p-8">
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
    @stack('scripts')
</body>
</html>
