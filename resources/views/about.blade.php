@extends('layouts.app')

@section('title', 'La chaîne')
@section('meta_description', 'Hope Media RDC, la chaîne de la Parole qui éclaire : vidéos, enseignements, directs et actualités de la mission sur YouTube, Facebook, Instagram et TikTok.')

@section('content')
<section class="hero compact" aria-labelledby="aboutTitle">
    <div class="hero-slide is-active"><img src="{{ asset('images/hero-sunrise.jpg') }}" alt="" decoding="async"></div>
    <div class="wrap hero-inner">
        <div class="hero-copy">
            <span class="hero-slogan">La chaîne</span>
            <h1 id="aboutTitle">Hope Media RDC</h1>
            <p class="lead">Une voix pour l'espoir. Des prédications, des témoignages, des études bibliques et l'Écho de la mission, publiés chaque semaine sur toutes les grandes plateformes.</p>
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
        <div class="row-head"><h2 id="figTitle"><small>L'audience</small>Hope Media en chiffres</h2></div>
        <div class="counters">
            @foreach ([['500K+', 'Abonnés cumulés', 'fa-users'], ['4', 'Plateformes actives', 'fa-tower-broadcast'], ['1000+', 'Vidéos publiées', 'fa-circle-play'], ['50+', 'Pays touchés', 'fa-earth-africa']] as [$n, $label, $icon])
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
                ['fa-tower-broadcast', 'Diffusion en direct', 'Retransmission en live des cultes du sabbat, campagnes d\'évangélisation et événements spéciaux.', 'https://www.youtube.com/@Hopemedia-u4b/live'],
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
                <p>Proclamer le message des trois anges (Apocalypse 14) à travers les médias numériques modernes, en touchant chaque foyer, chaque cœur et chaque nation avec l'espérance de l'Évangile.</p>
            </div>
            <div class="about-card">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                <h3>Notre vision</h3>
                <p>Être la référence médiatique adventiste en Afrique Centrale, produisant du contenu spirituel de haute qualité qui prépare les gens au retour imminent de Jésus-Christ.</p>
            </div>
            <div class="about-card">
                <i class="fa-solid fa-gem" aria-hidden="true"></i>
                <h3>Nos valeurs</h3>
                <ul class="values">
                    <li>Fidélité à la Parole de Dieu</li>
                    <li>Excellence dans la production</li>
                    <li>Service communautaire</li>
                    <li>Intégrité et transparence</li>
                    <li>Innovation au service de l'Évangile</li>
                </ul>
            </div>
        </div>
        <p class="lead-text muted" style="margin-top:18px">Notre équipe de bénévoles et de techniciens dévoués travaille chaque jour pour produire du contenu de qualité qui édifie, encourage et transforme des vies. Nous croyons que la technologie est un outil puissant pour partager la Bonne Nouvelle.</p>
    </section>

    {{-- Une production de l'Église --}}
    <section class="info-card" aria-labelledby="churchTitle">
        <h2 id="churchTitle">Une production de l'Église Adventiste du 7ème Jour</h2>
        <p class="muted" style="margin:0">Hope Media RDC est la plateforme médiatique officielle de l'Église Adventiste du 7ème Jour — Union du Congo Ouest (UCO). Fondée pour répondre à la mission évangélique dans un monde de plus en plus connecté, notre plateforme diffuse des contenus spirituels, bibliques et communautaires sur toutes les grandes plateformes numériques.</p>
        <p class="muted" style="margin:0">L'Église Adventiste du 7ème Jour est un mouvement chrétien mondial présent dans plus de 200 pays avec plus de 21 millions de membres. En RDC, l'Église est organisée en plusieurs unions, dont l'Union du Congo Ouest (UCO) à laquelle Hope Media RDC est rattachée. Les adventistes du 7ème jour accordent une importance particulière à l'étude des Écritures, à la santé holistique, au sabbat (le 7ème jour) et à la préparation au retour de Christ. Hope Media RDC est fière de porter haut les valeurs de cette église et de les partager avec le monde entier à travers les outils numériques modernes.</p>
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
