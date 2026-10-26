@extends('layouts.app')

@section('title', 'Accueil')
@section('meta_description', 'Hope Media RDC : vidéos, enseignements et actualités de la mission, en continu sur YouTube, Facebook, Instagram et TikTok. La Parole qui éclaire.')

@php
    $latestVideos = $latestVideos ?? collect();
    $latestPublications = $latestPublications ?? collect();
    $videosByPlatform = $videosByPlatform ?? [];
    $platforms = [
        'youtube'   => ['label' => 'YouTube',   'icon' => 'fa-youtube'],
        'facebook'  => ['label' => 'Facebook',  'icon' => 'fa-facebook'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'fa-instagram'],
        'tiktok'    => ['label' => 'TikTok',    'icon' => 'fa-tiktok'],
        'x'         => ['label' => 'X',         'icon' => 'fa-x-twitter'],
    ];
    $decode = fn ($s) => html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pfOf = fn ($v) => is_object($v->platform) ? $v->platform->value : $v->platform;

    // À la une : la dernière vidéo publiée, puis les suivantes en « À suivre »
    $feature = $latestVideos->first();
    $upNext = $latestVideos->slice(1, 3);
    $featurePub = $feature ? null : $latestPublications->first();

    // Rangées
    $mostViewed = $latestVideos->filter(fn ($v) => $v->view_count > 0)->sortByDesc('view_count')->values();
    $shorts = collect($videosByPlatform['instagram'] ?? [])->concat($videosByPlatform['tiktok'] ?? [])->sortByDesc('published_at')->values();
    $teachings = $latestPublications->where('kind', 'post')->values();
    $mission = $latestPublications->where('kind', 'news')->values();

    $pubIcon = fn ($icon) => 'fa-' . (['file-text' => 'file-lines', 'play-circle' => 'circle-play'][$icon] ?? $icon);
@endphp

@section('content')

{{-- À la une : fond = images du carrousel (admin), contenu = dernière vidéo publiée --}}
<section class="hero" id="hero" aria-roledescription="carrousel" aria-label="À la une" data-slide-ms="6500">
    @foreach ($heroSlides as $index => $slide)
        <div class="hero-slide{{ $index === 0 ? ' is-active' : '' }}">
            <img src="{{ asset($slide->image) }}" alt="" @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
        </div>
    @endforeach

    <div class="wrap hero-inner hero-feature">
        <div class="hero-copy">
            @if ($feature)
                @php $fpf = $pfOf($feature); $fTitle = $decode($feature->title); $fDur = $fpf === 'youtube' && $feature->duration ? \App\Helpers\MediaHelper::formatDuration($feature->duration) : null; @endphp
                <div class="eyebrow">
                    <span class="chip gold">À la une</span>
                    <span><i class="fa-brands {{ $platforms[$fpf]['icon'] ?? 'fa-youtube' }}" aria-hidden="true"></i> {{ $platforms[$fpf]['label'] ?? ucfirst($fpf) }}</span>
                    @if ($feature->published_at)<span class="sep"></span><span>{{ $feature->published_at->diffForHumans() }}</span>@endif
                </div>
                <h1 class="{{ trim((mb_strlen($fTitle) > 45 ? 'is-long' : '') . (($__t = html_entity_decode((string) $fTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === mb_strtoupper($__t) && preg_match('/\p{Lu}{4}/u', $__t) ? ' is-caps' : '')) }}">{{ $fTitle }}</h1>
                @if ($feature->description)<p class="lead">{{ Str::limit(strip_tags($decode($feature->description)), 170) }}</p>@endif
                <div class="hero-meta">
                    @if ($fDur)<span><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $fDur }}</span>@endif
                    @if ($feature->view_count)<span><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ number_format($feature->view_count, 0, ',', ' ') }} vues</span>@endif
                    @if ($feature->channel_title)<span>{{ $feature->channel_title }}</span>@endif
                </div>
                <div class="hero-cta">
                    <a class="btn btn-gold" href="{{ route('videos.show', $feature) }}"><i class="fa-solid fa-play" aria-hidden="true"></i>Regarder</a>
                    <a class="btn btn-ghost" href="{{ route('videos.index') }}"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Toutes les vidéos</a>
                </div>
            @elseif ($featurePub)
                <div class="eyebrow"><span class="chip gold">À la une</span><span>{{ $featurePub->badge }}</span>@if ($featurePub->date)<span class="sep"></span><span>{{ $featurePub->date->diffForHumans() }}</span>@endif</div>
                <h1 class="{{ mb_strlen($featurePub->title) > 45 ? 'is-long' : '' }}">{{ $featurePub->title }}</h1>
                @if ($featurePub->excerpt)<p class="lead">{{ Str::limit($featurePub->excerpt, 170) }}</p>@endif
                <div class="hero-cta">
                    <a class="btn btn-gold" href="{{ $featurePub->url }}"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>{{ $featurePub->kind === 'news' ? 'Lire' : 'Découvrir' }}</a>
                    <a class="btn btn-ghost" href="{{ route('videos.index') }}"><i class="fa-solid fa-circle-play" aria-hidden="true"></i>Nos vidéos</a>
                </div>
            @else
                <span class="hero-slogan">Hope Media RDC</span>
                <h1>La Parole qui éclaire</h1>
                <p class="lead">Vidéos, enseignements et actualités de la mission, en continu sur nos réseaux.</p>
                <div class="hero-cta">
                    <a class="btn btn-gold" href="{{ route('videos.index') }}"><i class="fa-solid fa-play" aria-hidden="true"></i>Regarder</a>
                    <a class="btn btn-ghost" href="{{ route('posts.index') }}"><i class="fa-solid fa-book-open" aria-hidden="true"></i>Enseignements</a>
                </div>
            @endif
        </div>

        @if ($feature)
            <aside class="hero-next" aria-label="Vidéo à la une et à suivre">
                <a class="hero-poster" href="{{ route('videos.show', $feature) }}" aria-label="Regarder : {{ $fTitle }}">
                    @if ($feature->thumbnail_url)
                        <img src="{{ $feature->thumbnail_url }}" alt="" decoding="async">
                    @else
                        <x-site.art :seed="$feature->id" icon="fa-circle-play" />
                    @endif
                    <span class="big-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                </a>
                @if ($upNext->isNotEmpty())
                    <p class="hero-next-title">À suivre</p>
                    @foreach ($upNext as $v)
                        <a class="hero-next-item" href="{{ route('videos.show', $v) }}">
                            <span class="thumb">
                                @if ($v->thumbnail_url)<img src="{{ $v->thumbnail_url }}" alt="" loading="lazy">@else<x-site.art :seed="$v->id" icon="fa-circle-play" />@endif
                            </span>
                            <span>
                                <strong class="{{ trim(($__t = html_entity_decode((string) $v->title, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === mb_strtoupper($__t) && preg_match('/\p{Lu}{4}/u', $__t) ? ' is-caps' : '') }}">{{ $decode($v->title) }}</strong>
                                <small>{{ $platforms[$pfOf($v)]['label'] ?? '' }}@if ($v->published_at) · {{ $v->published_at->diffForHumans() }}@endif</small>
                            </span>
                        </a>
                    @endforeach
                @endif
            </aside>
        @endif

        @if ($heroSlides->count() > 1)
            <div class="hero-dots" role="group" aria-label="Images de fond">
                @foreach ($heroSlides as $index => $slide)
                    <button type="button" data-index="{{ $index }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Image {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Flash info --}}
@if ($latestPublications->isNotEmpty())
    <div class="ticker" id="ticker" role="region" aria-label="Flash info">
        <div class="ticker-label"><span class="dot" aria-hidden="true"></span>Flash info</div>
        <div class="ticker-viewport">
            <div class="ticker-track">
                @foreach ([false, true] as $dup)
                    <ul @if ($dup) aria-hidden="true" @endif>
                        @foreach ($latestPublications as $item)
                            <li>
                                @if ($item->date)<time datetime="{{ $item->date->toDateString() }}">{{ $item->date->isToday() ? $item->date->format('H:i') : $item->date->translatedFormat('d M') }}</time>@endif
                                <a href="{{ $item->url }}" @if ($dup) tabindex="-1" @endif>{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
        <button class="ticker-pause" type="button" id="tickerPause" aria-label="Mettre le défilement en pause"><i class="fa-solid fa-pause" aria-hidden="true"></i></button>
    </div>
@endif

<div class="sections">

    {{-- Vidéos --}}
    <section aria-labelledby="videosTitle">
        <div class="wrap row-head">
            <h2 id="videosTitle"><small>Sur toutes nos plateformes</small>À regarder</h2>
            <a class="more" href="{{ route('videos.index') }}">Toutes les vidéos <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
        </div>

        @if ($latestVideos->isEmpty())
            <div class="wrap">
                <div class="empty">
                    <i class="fa-solid fa-satellite-dish" aria-hidden="true"></i>
                    <strong>Les premières vidéos arrivent.</strong>
                    Elles apparaîtront ici dès leur publication sur nos réseaux.
                </div>
            </div>
        @else
            <div class="wrap sync" style="margin-bottom:22px">
                <div class="filters" role="group" aria-label="Filtrer par plateforme" id="rowFilters">
                    <button type="button" data-pf="all" aria-pressed="true">Tout <span class="count">{{ $latestVideos->count() }}</span></button>
                    @foreach ($platforms as $key => $p)
                        @if (collect($videosByPlatform[$key] ?? [])->isNotEmpty())
                            <button type="button" data-pf="{{ $key }}" aria-pressed="false"><i class="fa-brands {{ $p['icon'] }}" aria-hidden="true"></i>{{ $p['label'] }}</button>
                        @endif
                    @endforeach
                </div>
                <p class="sync-status"><span class="ok" aria-hidden="true"></span>Mis à jour automatiquement depuis nos réseaux</p>
            </div>

            <div class="rows" id="videoRows">
                <x-site.rail id="latest" title="Nouveautés" kicker="Vient de sortir" :href="route('videos.index')">
                    @foreach ($latestVideos as $video)<x-site.video-card :video="$video" :vertical="false" />@endforeach
                </x-site.rail>

                @if ($mostViewed->count() >= 3)
                    <x-site.rail id="popular" title="Les plus regardées" kicker="Tendances" :href="route('videos.index')">
                        @foreach ($mostViewed as $video)<x-site.video-card :video="$video" :vertical="false" :stats="true" />@endforeach
                    </x-site.rail>
                @endif

                @foreach (['youtube', 'facebook', 'x'] as $key)
                    @php $items = collect($videosByPlatform[$key] ?? []); @endphp
                    @if ($items->isNotEmpty())
                        <x-site.rail :id="$key" :title="'Sur ' . $platforms[$key]['label']" kicker="Nos chaînes" :href="route('videos.index', ['platform' => $key])">
                            @foreach ($items as $video)<x-site.video-card :video="$video" :vertical="false" :stats="true" />@endforeach
                        </x-site.rail>
                    @endif
                @endforeach

                @if ($shorts->isNotEmpty())
                    <x-site.rail id="shorts" title="Shorts & Reels" kicker="Format court · Instagram · TikTok" :href="route('videos.index', ['platform' => 'tiktok'])" :vertical="true">
                        @foreach ($shorts as $video)<x-site.video-card :video="$video" :vertical="true" />@endforeach
                    </x-site.rail>
                @endif
                <p class="wrap empty" id="rowsEmpty" hidden>Aucune vidéo récente sur cette plateforme.</p>
            </div>
        @endif
    </section>

    {{-- Enseignements --}}
    @if ($teachings->isNotEmpty())
        <x-site.rail id="teachings" title="Enseignements" kicker="À voir et à écouter" :href="route('posts.index')">
            @foreach ($teachings as $item)
                <a class="card" href="{{ $item->url }}">
                    <div class="thumb">
                        @if ($item->image)<img src="{{ asset($item->image) }}" alt="" loading="lazy" decoding="async">@else<x-site.art :seed="$item->title" :icon="$pubIcon($item->icon)" />@endif
                        <span class="platform"><i class="fa-solid {{ $pubIcon($item->icon) }}" aria-hidden="true"></i></span>
                        <span class="play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                        @if ($item->date && $item->date->gt(now()->subDays(7)))<span class="badges"><span class="chip gold">Nouveau</span></span>@endif
                    </div>
                    <h3>{{ $item->title }}</h3>
                    <div class="meta"><span class="cat-tag c-0">{{ $item->badge }}</span><span>{{ $item->author }}</span></div>
                </a>
            @endforeach
        </x-site.rail>
    @endif

    {{-- Écho de la mission --}}
    @if ($mission->isNotEmpty())
        <section class="wrap" aria-labelledby="missionTitle">
            <div class="row-head">
                <h2 id="missionTitle"><small>Actualités</small>Écho de la mission</h2>
                <a class="more" href="{{ route('news.index') }}">Toutes les actualités <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
            </div>
            @php $lead = $mission->first(); @endphp
            <div class="mag" @if ($mission->count() === 1) style="grid-template-columns:minmax(0,1fr);max-width:860px" @endif>
                <a class="story lead" href="{{ $lead->url }}">
                    <div class="thumb">
                        @if ($lead->image)<img src="{{ asset($lead->image) }}" alt="" loading="lazy" decoding="async">@else<x-site.art :seed="$lead->title" icon="fa-newspaper" />@endif
                    </div>
                    <div class="meta">
                        <span class="cat-tag c-1">{{ $lead->badge }}</span>
                        @if ($lead->date)<time datetime="{{ $lead->date->toDateString() }}">{{ $lead->date->diffForHumans() }}</time>@endif
                    </div>
                    <h3>{{ $lead->title }}</h3>
                    @if ($lead->excerpt)<p>{{ $lead->excerpt }}</p>@endif
                </a>
                @if ($mission->count() > 1)
                    <div class="mag-side">
                        @foreach ($mission->slice(1, 4) as $item)
                            <a class="story" href="{{ $item->url }}">
                                <div class="thumb">
                                    @if ($item->image)<img src="{{ asset($item->image) }}" alt="" loading="lazy" decoding="async">@else<x-site.art :seed="$item->title" icon="fa-newspaper" />@endif
                                </div>
                                <div>
                                    <div class="meta"><span class="cat-tag c-1">{{ $item->badge }}</span>@if ($item->date)<time datetime="{{ $item->date->toDateString() }}">{{ $item->date->diffForHumans() }}</time>@endif</div>
                                    <h3>{{ $item->title }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Ligne de prière --}}
    <section class="wrap" aria-labelledby="prayTitle">
        <div class="prayer">
            <x-site.art theme="t-dawn" icon="fa-hands-praying" style="--gx:80%;--gy:10%" />
            <div class="prayer-in">
                <span class="hero-slogan" style="font-size:.95rem">Ligne de prière</span>
                <h2 id="prayTitle">On prie avec vous</h2>
                <p>Envoyez votre sujet de prière à l'équipe d'intercession de Hope Media, en toute confidentialité.</p>
                <p class="verse">« Déchargez-vous sur lui de tous vos soucis, car lui-même prend soin de vous. » — 1 Pierre 5:7</p>
                <div class="prayer-cta">
                    <a class="btn btn-gold" href="{{ route('contact.prayer') }}"><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>Envoyer un sujet de prière</a>
                    <a class="btn btn-ghost" href="{{ route('contact.create') }}"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>Écrire à la rédaction</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
