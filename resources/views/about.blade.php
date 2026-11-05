@extends('layouts.app')

@section('title', 'La chaîne')
@section('meta_description', $site->site_name . ' : ' . \Illuminate\Support\Str::limit($site->about_lead, 140))

@section('content')
<section class="hero compact" aria-labelledby="aboutTitle">
    <div class="hero-slide is-active"><img src="{{ asset('images/hero-sunrise.jpg') }}" alt="" decoding="async"></div>
    <div class="wrap hero-inner">
        <div class="hero-copy">
            <span class="hero-slogan">La chaîne</span>
            <h1 id="aboutTitle">{{ $site->about_title }}</h1>
            @if ($site->about_lead)<p class="lead">{{ $site->about_lead }}</p>@endif
            <div class="hero-cta">
                <a class="btn btn-gold" href="{{ route('videos.index') }}"><i class="fa-solid fa-play" aria-hidden="true"></i>Regarder nos vidéos</a>
                <a class="btn btn-ghost" href="{{ route('contact.create') }}"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>Écrire à la rédaction</a>
            </div>
        </div>
    </div>
</section>

<div class="wrap page-body">
    {{-- Où nous regarder --}}
    <section aria-labelledby="joinTitle">
        <div class="row-head"><h2 id="joinTitle"><small>Rejoignez-nous</small>Où nous regarder</h2></div>
        <p class="muted" style="margin:0 0 14px">Nos contenus sont disponibles gratuitement sur toutes les grandes plateformes. Abonnez-vous pour ne rien manquer.</p>
        <div class="counters">
            @foreach ([['youtube', 'YouTube', 'Vidéos & Lives'], ['facebook', 'Facebook', 'Posts & Stories'], ['instagram', 'Instagram', 'Reels & Photos'], ['tiktok', 'TikTok', 'Courtes vidéos']] as [$key, $label, $sub])
                <a class="counter p-{{ $key }}" href="{{ route('videos.index', ['platform' => $key]) }}">
                    <span class="p-ico"><i class="fa-brands fa-{{ $key }}" aria-hidden="true"></i></span>
                    <span><strong style="font-size:1.3rem">{{ $label }}</strong><small>{{ $sub }}</small></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- En chiffres --}}
    <section aria-labelledby="figTitle">
        <div class="row-head"><h2 id="figTitle"><small>L'audience</small>{{ $site->site_name }} en chiffres</h2></div>
        <div class="counters">
            @php $figureIcons = ['fa-users', 'fa-tower-broadcast', 'fa-circle-play', 'fa-earth-africa']; @endphp
            @foreach (\App\Support\Site::pairs('about_figures') as $i => [$n, $label])
                @php $icon = $figureIcons[$i % 4]; @endphp
                <div class="counter" style="--pc:var(--hope)">
                    <span class="p-ico"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <span><strong>{{ $n }}</strong><small>{{ $label }}</small></span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Programmes --}}
    <section aria-labelledby="doTitle">
        <div class="row-head"><h2 id="doTitle"><small>À l'antenne</small>Nos programmes</h2></div>
        <div class="about-grid">
            @foreach ([
                ['fa-video', 'Productions vidéo', 'Prédications, études bibliques, témoignages et programmes spéciaux filmés et diffusés chaque semaine.', route('videos.index')],
                ['fa-tower-broadcast', 'Diffusion en direct', 'Retransmission en live des cultes du sabbat, campagnes d\'évangélisation et événements spéciaux.', ($site->live_enabled && $site->live_url) ? $site->live_url : route('videos.index')],
                ['fa-headphones', 'Podcasts & Audio', 'Sermons et dévotions en format audio pour vous accompagner dans vos déplacements quotidiens.', route('posts.by-type', 'audio')],
                ['fa-newspaper', 'Écho de la mission', 'Informations et nouvelles de la communauté adventiste en RDC et dans la région.', route('news.index')],
                ['fa-hands-praying', 'Intercession & Prière', 'Service de demandes de prière disponible 24h/24 pour accompagner chaque fidèle dans ses besoins.', route('contact.prayer')],
                ['fa-book-bible', 'Formation biblique', 'Cours et séries thématiques pour approfondir votre connaissance des Écritures et grandir spirituellement.', route('posts.index')],
            ] as [$icon, $title, $text, $href])
                <a class="about-card" href="{{ $href }}" @if (str_starts_with($href, 'http') && ! str_starts_with($href, url('/'))) target="_blank" rel="noopener" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                    <span class="more" style="min-height:0">Voir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Ligne éditoriale --}}
    <section aria-labelledby="adnTitle">
        <div class="row-head"><h2 id="adnTitle"><small>Notre ADN</small>Ligne éditoriale</h2></div>
        <div class="about-grid">
            <div class="about-card">
                <i class="fa-solid fa-bullseye" aria-hidden="true"></i>
                <h3>Notre mission</h3>
                <p>{{ $site->about_mission }}</p>
            </div>
            <div class="about-card">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                <h3>Notre vision</h3>
                <p>{{ $site->about_vision }}</p>
            </div>
            <div class="about-card">
                <i class="fa-solid fa-gem" aria-hidden="true"></i>
                <h3>Nos valeurs</h3>
                <ul class="values">
                    @foreach (\App\Support\Site::lines('about_values') as $value)
                        <li>{{ $value }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @if ($site->about_team)
            <p class="lead-text muted" style="margin-top:18px">{{ $site->about_team }}</p>
        @endif
    </section>

    {{-- Une production de l'Église --}}
    <section class="info-card" aria-labelledby="churchTitle">
        <h2 id="churchTitle">{{ $site->about_church_title }}</h2>
        @foreach (\App\Support\Site::paragraphs('about_church_text') as $paragraph)
            <p class="muted" style="margin:0">{{ $paragraph }}</p>
        @endforeach
        <div class="about-grid">
            @foreach ([
                ['fa-book-bible', 'Fondée sur la Bible', 'La Sainte Bible est notre seule règle de foi et de pratique.'],
                ['fa-calendar-day', 'Le Sabbat du 7ème jour', 'Nous observons le samedi (sabbat) comme jour saint selon le 4ème commandement.'],
                ['fa-heart-pulse', 'Santé & Bien-être', 'Nous promouvons un mode de vie sain comme témoignage de notre foi.'],
                ['fa-earth-africa', 'Mission mondiale', 'Présents dans plus de 200 pays, nous œuvrons pour une évangélisation globale.'],
            ] as [$icon, $title, $text])
                <div class="about-card" style="background:var(--bg)"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i><h3>{{ $title }}</h3><p>{{ $text }}</p></div>
            @endforeach
        </div>
    </section>
</div>
@endsection
