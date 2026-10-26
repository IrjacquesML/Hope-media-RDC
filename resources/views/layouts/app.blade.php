@php
    // Les sections courtes (@section('x', '...')) sont déjà échappées par Blade : on les décode avant de les réafficher.
    $section = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle = $section('title');
    $metaDescription = $section('meta_description') ?: "Hope Media RDC : vidéos, enseignements et actualités de la mission, en continu sur YouTube, Facebook, Instagram et TikTok.";
    $ogImage = $section('og_image') ?: asset('images/hero-bible.jpg');
    $youtubeChannel = 'https://www.youtube.com/@Hopemedia-u4b';
    // Menu principal : uniquement les contenus (comme une chaîne). « À propos » et « Contact » sont dans le tiroir et le pied de page.
    $nav = [
        ['home', 'home', 'Accueil', 'fa-house'],
        ['videos.index', 'videos.*', 'Vidéos', 'fa-circle-play'],
        ['posts.index', 'posts.*', 'Enseignements', 'fa-book-open'],
        ['news.index', 'news.*', 'Écho de la mission', 'fa-newspaper'],
        ['ressources.index', 'ressources.*', 'Ressources', 'fa-book'],
    ];
    $navMore = [
        ['about', 'about', 'La chaîne', 'fa-tv'],
        ['contact.create', 'contact.create', 'Écrire à la rédaction', 'fa-paper-plane'],
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle ? $pageTitle . ' - ' : '' }}Hope Media RDC</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $section('meta_keywords') ?: 'enseignements bibliques, église adventiste, prières, vidéos religieuses' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B1530">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Partage WhatsApp / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Hope Media RDC">
    <meta property="og:title" content="{{ $pageTitle ?: 'Hope Media RDC — La Parole qui éclaire' }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="fr_CD">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle ?: 'Hope Media RDC — La Parole qui éclaire' }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@700;800&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>try { if (localStorage.getItem('hm:theme') === '"light"') document.documentElement.setAttribute('data-theme', 'light'); } catch (e) {}</script>

    @vite(['resources/css/site.css', 'resources/js/site.js'])
    @stack('head')
</head>

<body>
    <a class="skip" href="#contenu">Aller au contenu</a>

    {{-- En-tête --}}
    <header class="header @yield('header_class')" id="header">
        <div class="wrap">
            <a class="logo" href="{{ route('home') }}" aria-label="Hope Media RDC — Accueil">
                <img class="l-dark" src="{{ asset('images/brand/logo-hope-media-negatif.webp') }}" alt="Hope Media Network-DRC" width="360" height="314">
                <img class="l-light" src="{{ asset('images/brand/logo-hope-media.webp') }}" alt="" width="360" height="314">
            </a>
            <nav class="nav" aria-label="Navigation principale">
                @foreach ($nav as [$route, $pattern, $label])
                    <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="h-actions">
                <span class="clock" aria-hidden="true">Kinshasa <b id="clock">--:--</b></span>
                <a class="live-badge" href="{{ $youtubeChannel }}/live" target="_blank" rel="noopener" aria-label="Direct sur YouTube (nouvel onglet)">
                    <span class="dot" aria-hidden="true"></span>Direct
                </a>
                <a class="btn btn-gold h-pray" href="{{ route('contact.prayer') }}"><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>Prière</a>
                @auth
                    <a class="icon-btn h-admin" href="{{ route('admin.dashboard') }}" aria-label="Tableau de bord admin"><i class="fa-solid fa-gauge" aria-hidden="true"></i></a>
                @endauth
                <button class="icon-btn" type="button" data-search-open aria-label="Rechercher un enseignement"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                <button class="icon-btn" type="button" id="themeToggle" aria-label="Passer en mode clair"><i class="fa-solid fa-sun" aria-hidden="true"></i></button>
                <button class="icon-btn menu-btn" type="button" id="menuOpen" aria-label="Ouvrir le menu" aria-controls="drawer"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
            </div>
        </div>
    </header>

    {{-- Menu complet (mobile et tablette) --}}
    <div class="drawer" id="drawer" hidden>
        <nav class="drawer-panel" aria-label="Menu">
            <div class="drawer-head">
                <strong>Menu</strong>
                <button class="icon-btn" type="button" id="menuClose" aria-label="Fermer le menu"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            @foreach ($nav as [$route, $pattern, $label, $icon])
                <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</a>
            @endforeach
            <a href="{{ $youtubeChannel }}/live" target="_blank" rel="noopener"><i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>Direct sur YouTube</a>
            <a href="{{ route('contact.prayer') }}" @if (request()->routeIs('contact.prayer')) aria-current="page" @endif><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>Ligne de prière</a>
            <hr>
            @foreach ($navMore as [$route, $pattern, $label, $icon])
                <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</a>
            @endforeach
            @auth
                <hr>
                <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge" aria-hidden="true"></i>Tableau de bord</a>
                <form action="{{ route('auth.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="link"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>Déconnexion</button>
                </form>
            @endauth
        </nav>
    </div>

    {{-- Recherche (enseignements) --}}
    <div class="search" id="search" role="dialog" aria-modal="true" aria-label="Recherche" hidden>
        <div class="wrap">
            <form class="search-box" action="{{ route('posts.search') }}" method="GET" role="search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <label class="sr-only" for="searchInput">Rechercher un enseignement</label>
                <input id="searchInput" name="q" type="search" placeholder="Rechercher un enseignement…" autocomplete="off" required minlength="3">
                <button class="icon-btn" type="button" id="searchClose" aria-label="Fermer la recherche"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </form>
            <p class="search-hint">Au moins 3 caractères, puis Entrée.</p>
        </div>
    </div>

    {{-- Messages flash --}}
    @if (session('success') || session('error'))
        <div class="flash-zone" id="flash" role="status">
            @if ($message = session('success'))
                <div class="alert ok"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>{{ $message }}</span></div>
            @endif
            @if ($message = session('error'))
                <div class="alert err"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>{{ $message }}</span></div>
            @endif
        </div>
    @endif

    <main id="contenu">
        @yield('content')

        {{-- Newsletter (toutes les pages) --}}
        <section class="wrap" id="newsletter" aria-labelledby="nlTitle">
            <div class="news-box">
                <div>
                    <h2 id="nlTitle">Ne manquez aucune sortie</h2>
                    <p>Les nouvelles vidéos, les enseignements et l'Écho de la mission, chaque semaine dans votre boîte mail.</p>
                </div>
                <form class="nl-form" action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <div class="nl-row">
                        <label class="sr-only" for="nlName">Votre prénom (optionnel)</label>
                        <input id="nlName" type="text" name="name" placeholder="Votre prénom (optionnel)" autocomplete="given-name">
                        <label class="sr-only" for="nlEmail">Votre adresse e-mail</label>
                        <input id="nlEmail" type="email" name="email" required placeholder="Votre adresse e-mail" autocomplete="email">
                        <button class="btn btn-gold" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>S'abonner</button>
                    </div>
                    @if (session('newsletter_success'))
                        <p class="nl-msg ok" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('newsletter_success') }}</p>
                    @elseif (session('newsletter_info'))
                        <p class="nl-msg" role="status"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> {{ session('newsletter_info') }}</p>
                    @elseif ($errors->has('email') && ! old('content') && ! old('message'))
                        <p class="nl-msg err">{{ $errors->first('email') }}</p>
                    @else
                        <p class="nl-msg">Désinscription en un clic depuis chaque e-mail.</p>
                    @endif
                </form>
            </div>
        </section>
    </main>

    {{-- Pied de page --}}
    <footer class="site-footer">
        <div class="wrap">
            <div class="f-grid">
                <div class="f-brand">
                    <div class="logos">
                        <img class="l-dark" src="{{ asset('images/brand/logo-hope-media-negatif.webp') }}" alt="Hope Media Network-DRC" width="360" height="314" loading="lazy">
                        <img class="l-light" src="{{ asset('images/brand/logo-hope-media.webp') }}" alt="Hope Media Network-DRC" width="360" height="314" loading="lazy">
                        <img class="l-dark" src="{{ asset('images/brand/logo-adventiste-blanc.webp') }}" alt="Église Adventiste du Septième Jour" width="160" height="133" loading="lazy">
                        <img class="l-light" src="{{ asset('images/brand/logo-adventiste.webp') }}" alt="Église Adventiste du Septième Jour" width="160" height="133" loading="lazy">
                    </div>
                    <span class="f-slogan">La Parole qui éclaire</span>
                    <p>Vidéos, enseignements et actualités de la mission, sur tous vos écrans et tous vos réseaux.</p>
                </div>
                {{-- Pas de liens de navigation ici : ils sont déjà dans le menu principal et le menu ☰ --}}
                <div>
                    <h3>La rédaction</h3>
                    <address>
                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>01 Lubumbashi, Q. Pigeon, C. Ngaliema, Kinshasa</span>
                        <span><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="tel:+243852283241">+243 852 283 241</a></span>
                        <span><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:hopemediasociaux@gmail.com">hopemediasociaux@gmail.com</a></span>
                    </address>
                    <div class="f-social">
                        <a class="p-youtube" href="{{ $youtubeChannel }}" target="_blank" rel="noopener" aria-label="Chaîne YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="f-bottom">
                <span>&copy; {{ date('Y') }} Hope Media RDC. Église Adventiste du 7ème Jour - UCO</span>
            </div>
        </div>
    </footer>

    {{-- Barre d'onglets (téléphone) --}}
    <nav class="tabbar" aria-label="Navigation mobile">
        <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif><i class="fa-solid fa-house" aria-hidden="true"></i>Accueil</a>
        <a href="{{ route('videos.index') }}" @if (request()->routeIs('videos.*')) aria-current="page" @endif><i class="fa-solid fa-circle-play" aria-hidden="true"></i>Vidéos</a>
        <a class="tab-live" href="{{ $youtubeChannel }}/live" target="_blank" rel="noopener"><i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>Direct</a>
        <a href="{{ route('posts.index') }}" @if (request()->routeIs('posts.*')) aria-current="page" @endif><i class="fa-solid fa-book-open" aria-hidden="true"></i>Enseign.</a>
        <a href="{{ route('contact.prayer') }}" @if (request()->routeIs('contact.prayer')) aria-current="page" @endif><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>Prière</a>
    </nav>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    @stack('scripts')
</body>
</html>
