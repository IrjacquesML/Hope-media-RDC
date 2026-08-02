<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Hope Media RDC</title>
    <meta name="description" content="@yield('meta_description', 'Hope Media RDC - Plateforme officielle de l\'Église Adventiste du 7ème Jour (Union du Congo Ouest)')">
    <meta name="keywords" content="@yield('meta_keywords', 'enseignements bibliques, église adventiste, prières, vidéos religieuses')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite Assets (Tailwind CSS + JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-900">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="font-bold text-2xl text-blue-600 flex items-center gap-2 hover:opacity-80 transition">
                        <img src="{{ asset('images/logo (2).png') }}" alt="Hope Media RDC" class="h-16 w-auto">
                    </a>
                </div>

                <!-- Menu principal (Desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="hover:text-blue-600 transition font-semibold {{ request()->routeIs('home') ? 'text-blue-600' : '' }}">
                        Accueil
                    </a>
                    <a href="{{ route('posts.index') }}" class="hover:text-blue-600 transition {{ request()->routeIs('posts.*') ? 'text-blue-600 font-semibold' : '' }}">
                        <i class="fas fa-book-open mr-1"></i>Enseignements
                    </a>
                    <a href="{{ route('news.index') }}" class="hover:text-blue-600 transition {{ request()->routeIs('news.*') ? 'text-blue-600 font-semibold' : '' }}">
                        Écho de la mission
                    </a>
                    <a href="{{ route('ressources.index') }}" class="hover:text-blue-600 transition {{ request()->routeIs('ressources.*') ? 'text-blue-600 font-semibold' : '' }}">
                        <i class="fas fa-book-open mr-1"></i>Ressources
                    </a>
                    <a href="{{ route('about') }}" class="hover:text-blue-600 transition {{ request()->routeIs('about') ? 'text-blue-600 font-semibold' : '' }}">
                        À propos
                    </a>
                    <a href="{{ route('contact.create') }}" class="hover:text-blue-600 transition">
                        Contact
                    </a>
                </div>

                <!-- Bouton Direct/Live et Mobile Menu -->
                <div class="flex items-center gap-4">
                    @if(Auth::check())
                    <a href="{{ route('admin.dashboard') }}" class="hidden md:block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                        <i class="fas fa-th-large mr-2"></i> Dashboard
                    </a>
                    <form action="{{ route('auth.logout') }}" method="POST" class="hidden md:block">
                        @csrf
                        <button type="submit" class="text-slate-600 hover:text-red-600 transition">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('contact.prayer') }}" class="hidden md:block bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-pray mr-2"></i> Direct/Prière
                    </a>

                    <!-- Menu Mobile Toggle -->
                    <button id="mobile-menu-btn" class="md:hidden text-slate-600 hover:text-blue-600">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Menu Mobile -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2 border-t">
                <a href="{{ route('home') }}" class="block px-4 py-2 hover:bg-red-50 rounded font-semibold">
                    Accueil
                </a>
                <a href="{{ route('posts.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">
                    <i class="fas fa-book-open mr-1"></i>Enseignements
                </a>
                <a href="{{ route('news.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Écho de la mission</a>
                <a href="{{ route('ressources.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded"><i class="fas fa-book-open mr-1"></i>Ressources</a>
                <a href="{{ route('about') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">À propos</a>
                <a href="{{ route('contact.create') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Contact</a>
                @if(Auth::check())
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 bg-blue-50 hover:bg-blue-100 rounded font-semibold">
                    <i class="fas fa-th-large mr-2"></i> Dashboard Admin
                </a>
                <form action="{{ route('auth.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left block px-4 py-2 text-red-600 hover:bg-red-50 rounded">
                        <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                    </button>
                </form>
                @endif
                <a href="{{ route('contact.prayer') }}" class="block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Prière</a>
            </div>
        </div>
    </nav>

    <!-- Messages Flash -->
    @if ($message = Session::get('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        {{ $message }}
    </div>
    @endif

    @if ($message = Session::get('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ $message }}
    </div>
    @endif

    <!-- Contenu principal -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- À propos -->
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-church text-blue-400"></i>
                        Hope Media RDC
                    </h3>
                    <p class="text-slate-300 text-sm">
                        Plateforme officielle de l'Église Adventiste du 7ème Jour - Union du Congo Ouest
                    </p>
                </div>

                <!-- Liens rapides -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Liens rapides</h4>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><a href="{{ route('home') }}" class="hover:text-blue-400 transition">Accueil</a></li>
                        <li><a href="{{ route('news.index') }}" class="hover:text-blue-400 transition">Actualités</a></li>
                        <li><a href="{{ route('contact.create') }}" class="hover:text-blue-400 transition">Nous contacter</a></li>
                        <li><a href="{{ route('contact.prayer') }}" class="hover:text-blue-400 transition">Demande de prière</a></li>
                    </ul>
                </div>

                <!-- Plateformes -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Nos plateformes</h4>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><a href="{{ route('videos.index', ['platform' => 'youtube']) }}" class="hover:text-red-400 transition"><i class="fa-brands fa-youtube mr-1 text-red-400"></i> YouTube</a></li>
                        <li><a href="{{ route('videos.index', ['platform' => 'facebook']) }}" class="hover:text-blue-400 transition"><i class="fa-brands fa-facebook mr-1 text-blue-400"></i> Facebook</a></li>
                        <li><a href="{{ route('videos.index', ['platform' => 'instagram']) }}" class="hover:text-pink-400 transition"><i class="fa-brands fa-instagram mr-1 text-pink-400"></i> Instagram</a></li>
                        <li><a href="{{ route('videos.index', ['platform' => 'tiktok']) }}" class="hover:text-slate-300 transition"><i class="fa-brands fa-tiktok mr-1"></i> TikTok</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Nous suivre</h4>
                    <div class="flex gap-4">
                        <a href="#" class="text-white hover:text-blue-400 transition">
                            <i class="fab fa-facebook text-2xl"></i>
                        </a>
                        <a href="#" class="text-white hover:text-blue-400 transition">
                            <i class="fab fa-youtube text-2xl"></i>
                        </a>
                        <a href="#" class="text-white hover:text-blue-400 transition">
                            <i class="fab fa-whatsapp text-2xl"></i>
                        </a>
                        <a href="#" class="text-white hover:text-blue-400 transition">
                            <i class="fab fa-instagram text-2xl"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-8 flex justify-between items-center">
                <p class="text-slate-300 text-sm">&copy; {{ date('Y') }} Hope Media RDC. Tous droits réservés. Église Adventiste du 7ème Jour - UCO.</p>
                <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-blue-400 transition text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-lock"></i>
                    Admin
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Toggle Mobile Menu
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('mobile-menu').classList.add('hidden');
            });
        });
    </script>

    <!-- Vite JS -->
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>