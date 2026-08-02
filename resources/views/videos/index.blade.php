@extends('layouts.app')

@section('title', 'Contenus Sociaux')
@section('meta_description', 'Retrouvez tous nos contenus publiés sur YouTube, Facebook, Instagram et TikTok.')

@section('content')

{{-- Hero plein écran avec image de fond --}}
<section class="relative min-h-[78vh] flex items-center text-white overflow-hidden">
    <div class="absolute inset-0" aria-hidden="true">
        <div class="hero-slide absolute inset-0 opacity-100 transition-opacity duration-1000">
            <div class="hero-bg absolute inset-0 bg-cover bg-center"
                 style="background-image: url('/images/hero-sunrise.jpg');"></div>
        </div>
        <div class="hero-slide absolute inset-0 opacity-0 transition-opacity duration-1000">
            <div class="hero-bg absolute inset-0 bg-cover bg-center"
                 style="background-image: url('/images/hero-bible.jpg');"></div>
        </div>
        <div class="hero-slide absolute inset-0 opacity-0 transition-opacity duration-1000">
            <div class="hero-bg absolute inset-0 bg-cover bg-center"
                 style="background-image: url('/images/hero-hope.jpg');"></div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-950/65 via-slate-950/40 to-slate-950/80"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/40 via-transparent to-transparent"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <p class="hero-fade text-sm md:text-base tracking-[0.28em] uppercase text-amber-200/90 font-semibold mb-5">
            Hope Media RDC
        </p>
        <h1 class="hero-fade hero-delay-1 text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.08] mb-6 drop-shadow-lg">
            La Parole qui éclaire
        </h1>
        <p class="hero-fade hero-delay-2 text-lg md:text-xl text-slate-100/90 max-w-2xl mx-auto mb-10 leading-relaxed">
            Enseignements, témoignages et contenus spirituels sur YouTube, Facebook, Instagram et TikTok.
        </p>
        <div class="hero-fade hero-delay-3 flex flex-wrap justify-center gap-4 text-sm">
            <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2 rounded-lg">
                <i class="fa-brands fa-youtube text-red-400"></i> YouTube
            </span>
            <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2 rounded-lg">
                <i class="fa-brands fa-facebook text-blue-400"></i> Facebook
            </span>
            <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2 rounded-lg">
                <i class="fa-brands fa-instagram text-pink-400"></i> Instagram
            </span>
            <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2 rounded-lg">
                <i class="fa-brands fa-tiktok"></i> TikTok
            </span>
        </div>
    </div>

    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex gap-2" id="hero-dots">
        <button type="button" class="hero-dot h-2.5 w-2.5 rounded-full bg-white transition-all duration-300" data-index="0" aria-label="Image 1"></button>
        <button type="button" class="hero-dot h-2.5 w-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="1" aria-label="Image 2"></button>
        <button type="button" class="hero-dot h-2.5 w-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="2" aria-label="Image 3"></button>
    </div>
</section>

<style>
    @keyframes hero-zoom {
        from { transform: scale(1.05); }
        to { transform: scale(1.14); }
    }
    @keyframes hero-rise {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hero-bg {
        animation: hero-zoom 18s ease-in-out infinite alternate;
        will-change: transform;
    }
    .hero-fade {
        opacity: 0;
        animation: hero-rise 0.9s ease-out forwards;
    }
    .hero-delay-1 { animation-delay: 0.15s; }
    .hero-delay-2 { animation-delay: 0.3s; }
    .hero-delay-3 { animation-delay: 0.45s; }
    .hero-dot.is-active {
        width: 1.75rem;
        background-color: #fff;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        let current = 0;
        let timer;

        function show(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('opacity-100', i === index);
                slide.classList.toggle('opacity-0', i !== index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === index);
                dot.classList.toggle('bg-white', i === index);
                dot.classList.toggle('bg-white/40', i !== index);
            });
            current = index;
        }

        function next() {
            show((current + 1) % slides.length);
        }

        function start() {
            timer = setInterval(next, 6500);
        }

        dots.forEach((dot) => {
            dot.addEventListener('click', function () {
                clearInterval(timer);
                show(Number(dot.dataset.index));
                start();
            });
        });

        show(0);
        start();
    });
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Onglets plateforme --}}
    <div class="flex flex-wrap justify-center gap-2 mb-8">
        {{-- Tout --}}
        <a href="{{ route('videos.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition
                  {{ ! $currentPlatform ? 'bg-slate-800 text-white shadow' : 'bg-white text-slate-600 border hover:bg-slate-50' }}">
            <i class="fa-solid fa-grid-2"></i>
            Tout
            <span class="bg-white/20 text-xs px-1.5 py-0.5 rounded-full {{ ! $currentPlatform ? '' : 'bg-slate-100 text-slate-500' }}">
                {{ $total }}
            </span>
        </a>

        {{-- YouTube --}}
        <a href="{{ route('videos.index', ['platform' => 'youtube']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition
                  {{ $currentPlatform === 'youtube' ? 'bg-red-600 text-white shadow' : 'bg-white text-slate-600 border hover:bg-red-50' }}">
            <i class="fa-brands fa-youtube"></i>
            YouTube
            @if($counts['youtube'] ?? 0)
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentPlatform === 'youtube' ? 'bg-white/20' : 'bg-red-100 text-red-600' }}">
                    {{ $counts['youtube'] }}
                </span>
            @endif
        </a>

        {{-- Facebook --}}
        <a href="{{ route('videos.index', ['platform' => 'facebook']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition
                  {{ $currentPlatform === 'facebook' ? 'bg-blue-700 text-white shadow' : 'bg-white text-slate-600 border hover:bg-blue-50' }}">
            <i class="fa-brands fa-facebook"></i>
            Facebook
            @if($counts['facebook'] ?? 0)
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentPlatform === 'facebook' ? 'bg-white/20' : 'bg-blue-100 text-blue-700' }}">
                    {{ $counts['facebook'] }}
                </span>
            @endif
        </a>

        {{-- Instagram --}}
        <a href="{{ route('videos.index', ['platform' => 'instagram']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition
                  {{ $currentPlatform === 'instagram' ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow' : 'bg-white text-slate-600 border hover:bg-pink-50' }}">
            <i class="fa-brands fa-instagram"></i>
            Instagram
            @if($counts['instagram'] ?? 0)
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentPlatform === 'instagram' ? 'bg-white/20' : 'bg-pink-100 text-pink-600' }}">
                    {{ $counts['instagram'] }}
                </span>
            @endif
        </a>

        {{-- TikTok --}}
        <a href="{{ route('videos.index', ['platform' => 'tiktok']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition
                  {{ $currentPlatform === 'tiktok' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border hover:bg-slate-50' }}">
            <i class="fa-brands fa-tiktok"></i>
            TikTok
            @if($counts['tiktok'] ?? 0)
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentPlatform === 'tiktok' ? 'bg-white/20' : 'bg-slate-100 text-slate-700' }}">
                    {{ $counts['tiktok'] }}
                </span>
            @endif
        </a>
    </div>

    {{-- Grille de contenus --}}
    @if ($videos->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-slate-400">
            <i class="fa-solid fa-satellite-dish text-6xl mb-4 opacity-30"></i>
            <p class="text-lg font-medium">Aucun contenu synchronisé pour le moment.</p>
            <p class="text-sm mt-1">Les contenus seront automatiquement importés depuis les réseaux sociaux.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($videos as $video)
                @php
                    $isVertical = in_array($video->platform->value, ['tiktok', 'instagram']);
                    $platformColors = [
                        'youtube'   => ['badge' => 'bg-red-100 text-red-600',   'icon' => 'fa-youtube',   'overlay' => 'bg-red-900'],
                        'facebook'  => ['badge' => 'bg-blue-100 text-blue-700', 'icon' => 'fa-facebook',  'overlay' => 'bg-blue-900'],
                        'instagram' => ['badge' => 'bg-pink-100 text-pink-600', 'icon' => 'fa-instagram', 'overlay' => 'bg-gradient-to-br from-purple-700 to-pink-600'],
                        'tiktok'    => ['badge' => 'bg-slate-100 text-slate-800','icon' => 'fa-tiktok',   'overlay' => 'bg-slate-900'],
                    ];
                    $pf = $platformColors[$video->platform->value] ?? $platformColors['youtube'];
                @endphp

                <a href="{{ route('videos.show', $video) }}"
                   class="group bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300 hover:-translate-y-1">

                    {{-- Miniature / Placeholder --}}
                    <div class="relative w-full {{ $isVertical ? 'aspect-[9/16]' : 'aspect-video' }} bg-slate-900 overflow-hidden flex-shrink-0">
                        @if ($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}"
                                 alt="{{ e($video->title) }}"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                        @else
                            <div class="absolute inset-0 {{ $pf['overlay'] }} flex flex-col items-center justify-center">
                                <i class="fa-brands {{ $pf['icon'] }} text-white text-5xl mb-3 opacity-60"></i>
                                <p class="text-white/50 text-xs text-center px-4 line-clamp-2">{{ $video->title }}</p>
                            </div>
                        @endif

                        {{-- Bouton play overlay --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center">
                            <div class="w-14 h-14 rounded-full bg-white/90 shadow-xl flex items-center justify-center
                                        opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100
                                        transition-all duration-300">
                                <i class="fa-solid fa-play text-slate-800 text-lg ml-1"></i>
                            </div>
                        </div>

                        {{-- Badge plateforme --}}
                        <span class="absolute top-2 left-2 inline-flex items-center gap-1 text-xs font-bold {{ $pf['badge'] }} rounded-full px-2.5 py-1 shadow-sm">
                            <i class="fa-brands {{ $pf['icon'] }}"></i>
                            {{ ucfirst($video->platform->value) }}
                        </span>

                        {{-- Durée YouTube --}}
                        @if($video->platform->value === 'youtube' && $video->duration)
                            <span class="absolute bottom-2 right-2 bg-black/75 text-white text-xs font-mono px-1.5 py-0.5 rounded">
                                {{ \App\Helpers\MediaHelper::formatDuration($video->duration) }}
                            </span>
                        @endif
                    </div>

                    {{-- Infos --}}
                    <div class="p-4 flex flex-col gap-1 flex-1">
                        <h2 class="font-semibold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $video->title }}
                        </h2>

                        @if($video->channel_title)
                            <p class="text-xs text-slate-400 truncate">
                                <i class="fa-solid fa-circle-user mr-1"></i>{{ $video->channel_title }}
                            </p>
                        @endif

                        <div class="flex items-center gap-3 mt-auto pt-2 text-xs text-slate-400">
                            @if($video->published_at)
                                <span><i class="fa-regular fa-calendar mr-1"></i>{{ $video->published_at->diffForHumans() }}</span>
                            @endif
                            @if($video->view_count)
                                <span><i class="fa-regular fa-eye mr-1"></i>{{ number_format($video->view_count) }}</span>
                            @endif
                            @if($video->likes_count)
                                <span><i class="fa-regular fa-heart mr-1"></i>{{ number_format($video->likes_count) }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            {{ $videos->links() }}
        </div>
    @endif

</div>
@endsection

