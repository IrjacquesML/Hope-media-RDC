@php
    // Les sections courtes (@section('x', '...')) sont déjà échappées par Blade : on les décode avant de les réafficher.
    $section = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle = $section('title');
    $metaDescription = $section('meta_description') ?: $site->seo_description;
    $ogImage = $section('og_image') ?: ($site->seo_image ? asset($site->seo_image) : asset('images/hero-bible.jpg'));
    $siteName = $site->site_name;
    $liveUrl = $site->live_enabled ? $site->live_url : null;
    $socialLinks = \App\Support\Site::socialLinks();
    $partners = \App\Support\Site::partners();
    $logoDark = $site->logo_dark ? asset($site->logo_dark) : null;
    $logoLight = $site->logo_light ? asset($site->logo_light) : $logoDark;
    // Menu principal : uniquement les contenus (comme une chaîne). « À propos » et « Contact » sont dans le tiroir et le pied de page.
    $nav = [
        ['home', 'home', $site->nav_home, 'fa-house'],
        ['videos.index', 'videos.*', $site->nav_videos, 'fa-circle-play'],
        ['posts.index', 'posts.*', $site->nav_posts, 'fa-book-open'],
        ['news.index', 'news.*', $site->nav_news, 'fa-newspaper'],
        ['ressources.index', 'ressources.*', $site->nav_resources, 'fa-book'],
    ];
    $navMore = [
        ['about', 'about', $site->nav_about, 'fa-tv'],
        ['contact.create', 'contact.create', $site->nav_contact, 'fa-paper-plane'],
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle ? $pageTitle . ' - ' : '' }}{{ $siteName }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $section('meta_keywords') ?: $site->seo_keywords }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $site->theme_color ?: '#0B1530' }}">
    @if ($site->favicon)<link rel="icon" href="{{ asset($site->favicon) }}">@endif
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Partage WhatsApp / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle ?: $siteName . ' — ' . $site->slogan }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="fr_CD">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle ?: $siteName . ' — ' . $site->slogan }}">
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
            <a class="logo" href="{{ route('home') }}" aria-label="{{ $siteName }} — Accueil">
                @if ($logoDark)<img class="l-dark" src="{{ $logoDark }}" alt="{{ $site->site_full_name }}" width="360" height="314">@endif
                @if ($logoLight)<img class="l-light" src="{{ $logoLight }}" alt="" width="360" height="314">@endif
                @unless ($logoDark || $logoLight)<strong>{{ $siteName }}</strong>@endunless
            </a>
            <nav class="nav" aria-label="Navigation principale">
                @foreach ($nav as [$route, $pattern, $label])
                    <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="h-actions">
                @if ($site->clock_enabled)
                    <span class="clock" aria-hidden="true">{{ $site->clock_city }} <b id="clock">--:--</b></span>
                @endif
                @if ($liveUrl)
                    <a class="live-badge" href="{{ $liveUrl }}" target="_blank" rel="noopener" aria-label="{{ $site->live_label }} (nouvel onglet)">
                        <span class="dot" aria-hidden="true"></span>{{ $site->live_label }}
                    </a>
                @endif
                @if ($site->prayer_enabled)
                    <a class="btn btn-gold h-pray" href="{{ route('contact.prayer') }}"><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>{{ $site->prayer_label }}</a>
                @endif
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
            @if ($liveUrl)
                <a href="{{ $liveUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>{{ $site->live_label }}</a>
            @endif
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
        @if ($site->newsletter_enabled)
        <section class="wrap" id="newsletter" aria-labelledby="nlTitle">
            <div class="news-box">
                <div>
                    <h2 id="nlTitle">{{ $site->newsletter_title }}</h2>
                    <p>{{ $site->newsletter_text }}</p>
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
        @endif
    </main>

    {{-- Pied de page (contenu géré dans Admin → Paramètres) --}}
    <footer class="site-footer">
        <div class="wrap">
            <div class="f-grid">
                <div class="f-brand">
                    <div class="logos">
                        @if ($logoDark)<img class="l-dark" src="{{ $logoDark }}" alt="{{ $site->site_full_name }}" width="360" height="314" loading="lazy">@endif
                        @if ($logoLight)<img class="l-light" src="{{ $logoLight }}" alt="{{ $site->site_full_name }}" width="360" height="314" loading="lazy">@endif
                        @php
                            $logo2Dark = $site->footer_logo2_dark ? asset($site->footer_logo2_dark) : null;
                            $logo2Light = $site->footer_logo2_light ? asset($site->footer_logo2_light) : $logo2Dark;
                            $logo2Dark = $logo2Dark ?: $logo2Light;
                        @endphp
                        @if ($logo2Dark)<img class="l-dark" src="{{ $logo2Dark }}" alt="{{ $site->footer_logo2_alt }}" width="160" height="133" loading="lazy">@endif
                        @if ($logo2Light)<img class="l-light" src="{{ $logo2Light }}" alt="{{ $site->footer_logo2_alt }}" width="160" height="133" loading="lazy">@endif
                    </div>
                    @if ($site->slogan)<span class="f-slogan">{{ $site->slogan }}</span>@endif
                    @if ($site->footer_text)<p>{{ $site->footer_text }}</p>@endif
                </div>
                {{-- Pas de liens de navigation ici : ils sont déjà dans le menu principal et le menu ☰ --}}
                <div>
                    <h3>{{ $site->footer_contact_title }}</h3>
                    <address>
                        @if ($site->contact_address)
                            <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>@if ($site->contact_map_url)<a href="{{ $site->contact_map_url }}" target="_blank" rel="noopener">{{ $site->contact_address }}</a>@else{{ $site->contact_address }}@endif</span>
                        @endif
                        @foreach (array_filter([$site->contact_phone, $site->contact_phone2]) as $phone)
                            <span><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="{{ \App\Support\Site::telHref($phone) }}">{{ $phone }}</a></span>
                        @endforeach
                        @if ($site->contact_whatsapp)
                            <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><a href="{{ \App\Support\Site::whatsappHref($site->contact_whatsapp) }}" target="_blank" rel="noopener">{{ $site->contact_whatsapp }}</a></span>
                        @endif
                        @if ($site->contact_email)
                            <span><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $site->contact_email }}">{{ $site->contact_email }}</a></span>
                        @endif
                    </address>
                    @if ($socialLinks)
                        <div class="f-social">
                            @foreach ($socialLinks as $key => [$label, $icon, $url])
                                <a class="p-{{ $key }}" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><i class="fa-brands {{ $icon }}" aria-hidden="true"></i></a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if ($partners->isNotEmpty())
                    <div class="f-partners">
                        <h3>{{ $site->footer_partners_title }}</h3>
                        <ul>
                            @foreach ($partners as $partner)
                                <li>
                                    @if ($partner->url)
                                        <a href="{{ $partner->url }}" target="_blank" rel="noopener">
                                            @if ($partner->logo)<img src="{{ asset($partner->logo) }}" alt="" loading="lazy">@endif
                                            <span>{{ $partner->name }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <span class="f-partner-plain">
                                            @if ($partner->logo)<img src="{{ asset($partner->logo) }}" alt="" loading="lazy">@endif
                                            <span>{{ $partner->name }}</span>
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="f-bottom">
                <span>&copy; {{ date('Y') }} {{ $site->footer_copyright }}</span>
            </div>
        </div>
    </footer>

    {{-- Barre d'onglets (téléphone) --}}
    <nav class="tabbar" aria-label="Navigation mobile">
        <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif><i class="fa-solid fa-house" aria-hidden="true"></i>Accueil</a>
        <a href="{{ route('videos.index') }}" @if (request()->routeIs('videos.*')) aria-current="page" @endif><i class="fa-solid fa-circle-play" aria-hidden="true"></i>{{ $site->nav_videos }}</a>
        @if ($liveUrl)
            <a class="tab-live" href="{{ $liveUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>{{ $site->live_label }}</a>
        @endif
        <a href="{{ route('posts.index') }}" @if (request()->routeIs('posts.*')) aria-current="page" @endif><i class="fa-solid fa-book-open" aria-hidden="true"></i>Enseign.</a>
        <a href="{{ route('contact.prayer') }}" @if (request()->routeIs('contact.prayer')) aria-current="page" @endif><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>{{ $site->prayer_label }}</a>
    </nav>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    @stack('scripts')
</body>
</html>
