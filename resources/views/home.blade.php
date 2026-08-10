@extends('layouts.app')

@section('title', 'Accueil')
@section('meta_description', 'Découvrez les enseignements bibliques de l\'Église Adventiste du 7ème Jour - Union du Congo Ouest')

@section('content')
    <!-- Héros plein écran -->
    <section class="hero-section relative flex items-center text-white overflow-hidden">
        <div class="absolute inset-0" id="hero-carousel" aria-hidden="true">
            @foreach($heroSlides as $index => $slide)
                <div class="hero-slide absolute inset-0 {{ $index === 0 ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-[1600ms] ease-in-out">
                    <div class="hero-bg absolute inset-0"
                         style="background-image: url('{{ asset($slide->image) }}');"></div>
                </div>
            @endforeach

            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/75 via-slate-950/50 to-slate-950/85 pointer-events-none"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/50 via-transparent to-transparent pointer-events-none"></div>
        </div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 md:py-28">
            <div class="max-w-3xl mx-auto text-center">
                <p class="hero-fade text-xs sm:text-sm md:text-base tracking-[0.28em] uppercase text-amber-200/90 font-semibold mb-4 sm:mb-5">
                    Hope Media RDC
                </p>

                <h1 class="hero-fade hero-delay-1 text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.08] mb-4 sm:mb-6 drop-shadow-lg">
                    La Parole qui éclaire
                </h1>

                <p class="hero-fade hero-delay-2 text-base sm:text-lg md:text-xl text-slate-100/90 max-w-2xl mx-auto mb-8 sm:mb-10 leading-relaxed">
                    Enseignements bibliques, témoignages et ressources spirituelles pour nourrir votre foi.
                </p>

                <div class="hero-fade hero-delay-3 flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                    <a href="{{ route('posts.index') }}"
                       class="inline-flex items-center justify-center gap-2 bg-white text-blue-700 px-6 sm:px-8 py-3 sm:py-3.5 rounded-lg font-semibold shadow-lg shadow-black/20 hover:bg-blue-50 transition duration-300 hover:-translate-y-0.5 text-sm sm:text-base">
                        <i class="fas fa-play-circle"></i>
                        Découvrir les enseignements
                    </a>
                    <a href="{{ route('contact.prayer') }}"
                       class="inline-flex items-center justify-center gap-2 border border-white/70 bg-white/10 backdrop-blur-sm text-white px-6 sm:px-8 py-3 sm:py-3.5 rounded-lg font-semibold hover:bg-white/20 transition duration-300 hover:-translate-y-0.5 text-sm sm:text-base">
                        <i class="fas fa-hands-praying"></i>
                        Demande de prière
                    </a>
                </div>
            </div>
        </div>

        @if($heroSlides->count() > 1)
            <div class="absolute bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-10 flex gap-2.5 sm:gap-2" id="hero-dots" aria-label="Diapositives du carrousel">
                @foreach($heroSlides as $index => $slide)
                    <button type="button"
                            class="hero-dot h-2.5 w-2.5 sm:h-2.5 sm:w-2.5 min-h-[11px] min-w-[11px] rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white/40' }} transition-all duration-300 touch-manipulation p-1 box-content"
                            data-index="{{ $index }}"
                            aria-label="Image {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    <style>
        .hero-section {
            /* svh/dvh : s'adapte à la barre d'adresse mobile */
            min-height: 70vh;
            min-height: 70svh;
        }
        @media (min-width: 640px) {
            .hero-section {
                min-height: 80vh;
                min-height: 80svh;
            }
        }
        @media (min-width: 768px) {
            .hero-section {
                min-height: 88vh;
                min-height: 88svh;
            }
        }

        @keyframes hero-zoom {
            from { transform: scale(1.05); }
            to { transform: scale(1.14); }
        }
        @keyframes hero-rise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-bg {
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            will-change: transform;
        }

        /* Desktop : zoom Ken Burns */
        @media (min-width: 768px) {
            .hero-bg {
                inset: -2%;
                width: 104%;
                height: 104%;
                animation: hero-zoom 18s ease-in-out infinite alternate;
            }
        }

        /* Mobile : cadrage stable plein écran, sans zoom */
        @media (max-width: 767px) {
            .hero-bg {
                inset: 0;
                width: 100%;
                height: 100%;
                animation: none;
                transform: none;
            }
        }

        /* Préférence réduction de mouvement */
        @media (prefers-reduced-motion: reduce) {
            .hero-bg {
                animation: none !important;
                transform: none !important;
            }
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
            const carousel = document.getElementById('hero-carousel');
            if (slides.length < 2) return;

            let current = 0;
            let timer;
            let touchStartX = 0;
            let touchEndX = 0;

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

            function prev() {
                show((current - 1 + slides.length) % slides.length);
            }

            function start() {
                clearInterval(timer);
                timer = setInterval(next, 6500);
            }

            dots.forEach((dot) => {
                dot.addEventListener('click', function () {
                    show(Number(dot.dataset.index));
                    start();
                });
            });

            // Swipe tactile smartphones
            if (carousel) {
                carousel.addEventListener('touchstart', function (e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                carousel.addEventListener('touchend', function (e) {
                    touchEndX = e.changedTouches[0].screenX;
                    const delta = touchEndX - touchStartX;
                    if (Math.abs(delta) < 40) return;
                    if (delta < 0) next();
                    else prev();
                    start();
                }, { passive: true });
            }

            show(0);
            start();
        });
    </script>

    <!-- Section Articles publiés -->
    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                        <i class="fas fa-newspaper text-blue-600 mr-3"></i>Articles
                    </h2>
                    <p class="text-slate-500 mt-1">Les publications récentes de Hope Media RDC</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('posts.index') }}"
                       class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Enseignements
                    </a>
                    <a href="{{ route('news.index') }}"
                       class="inline-flex items-center gap-1.5 bg-white border text-slate-700 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-slate-50 transition">
                        Écho de la mission
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($latestPublications ?? [] as $item)
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition">
                        <div class="relative h-48 bg-gradient-to-br from-slate-300 to-slate-400 overflow-hidden">
                            @if($item->image)
                                <img src="{{ asset($item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                    <i class="fas fa-{{ $item->icon }} text-4xl text-blue-600 opacity-30"></i>
                                </div>
                            @endif
                            <div class="absolute top-4 right-4 bg-white/90 rounded-full px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ $item->badge }}
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2 hover:text-blue-600 transition">
                                <a href="{{ $item->url }}">{{ $item->title }}</a>
                            </h3>

                            <p class="text-slate-600 text-sm mb-4 line-clamp-3">{{ $item->excerpt }}</p>

                            <div class="flex items-center justify-between text-xs text-slate-500 mb-4">
                                <span><i class="fas fa-user mr-1"></i>{{ $item->author }}</span>
                                <span><i class="fas fa-calendar mr-1"></i>{{ $item->date?->format('d/m/Y') }}</span>
                            </div>

                            <a href="{{ $item->url }}" class="text-blue-600 font-semibold hover:text-blue-700 transition">
                                Lire plus <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-slate-600">
                        <p class="mb-4"><i class="fas fa-inbox text-4xl text-slate-400"></i></p>
                        <p>Aucun article publié pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- SECTION : Flux Réseaux Sociaux --}}
    @php
        $latestVideos = $latestVideos ?? collect();
        $videosByPlatform = $videosByPlatform ?? [
            'youtube' => collect(),
            'facebook' => collect(),
            'instagram' => collect(),
            'tiktok' => collect(),
        ];
        $allVideos = $latestVideos;
        $platformGrids = [
            'youtube'   => $videosByPlatform['youtube']   ?? collect(),
            'facebook'  => $videosByPlatform['facebook']  ?? collect(),
            'instagram' => $videosByPlatform['instagram'] ?? collect(),
            'tiktok'    => $videosByPlatform['tiktok']    ?? collect(),
        ];
        $pfConfig = [
            'youtube'   => ['badge' => 'bg-red-100 text-red-600',    'icon' => 'fa-youtube',   'overlay' => 'bg-red-900', 'tab' => 'bg-red-600'],
            'facebook'  => ['badge' => 'bg-blue-100 text-blue-700',  'icon' => 'fa-facebook',  'overlay' => 'bg-blue-900', 'tab' => 'bg-blue-700'],
            'instagram' => ['badge' => 'bg-pink-100 text-pink-600',  'icon' => 'fa-instagram', 'overlay' => 'bg-gradient-to-br from-purple-700 to-pink-600', 'tab' => 'bg-pink-600'],
            'tiktok'    => ['badge' => 'bg-slate-100 text-slate-800','icon' => 'fa-tiktok',    'overlay' => 'bg-slate-900', 'tab' => 'bg-slate-900'],
        ];
    @endphp

    <section class="py-16 bg-white" id="social-feed">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                        <i class="fas fa-play-circle text-red-500 mr-3"></i>Vidéos des réseaux sociaux
                    </h2>
                    <p class="text-slate-500 mt-1">YouTube, Facebook, Instagram et TikTok — synchronisés automatiquement.</p>
                </div>
                <a href="{{ route('videos.index') }}"
                   class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Tout voir <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="flex flex-wrap gap-2 mb-8" id="social-tabs">
                <button type="button" class="social-tab-btn active px-4 py-2 rounded-full text-sm font-semibold transition bg-slate-800 text-white"
                        data-tab="all">
                    <i class="fa-solid fa-grid-2 mr-1"></i> Tout
                    <span class="text-xs bg-white/20 px-1.5 py-0.5 rounded-full ml-1">{{ $allVideos->count() }}</span>
                </button>
                @foreach($platformGrids as $platform => $items)
                <button type="button" class="social-tab-btn px-4 py-2 rounded-full text-sm font-semibold transition bg-white border text-slate-600 hover:bg-slate-50"
                        data-tab="{{ $platform }}">
                    <i class="fa-brands {{ $pfConfig[$platform]['icon'] }} mr-1"></i> {{ ucfirst($platform) }}
                    <span class="text-xs {{ $pfConfig[$platform]['badge'] }} px-1.5 py-0.5 rounded-full ml-1">{{ $items->count() }}</span>
                </button>
                @endforeach
            </div>

            <div class="social-grid" data-grid="all">
                @if($allVideos->isEmpty())
                    <div class="text-center py-16 text-slate-500 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-satellite-dish text-5xl mb-3 opacity-30"></i>
                        <p class="font-medium">Aucune vidéo synchronisée pour le moment.</p>
                        <p class="text-sm mt-1">Synchronisez YouTube depuis l’admin → Sync Réseaux.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @foreach($allVideos as $video)
                            @php
                                $platformValue = is_object($video->platform) ? $video->platform->value : $video->platform;
                                $vert = in_array($platformValue, ['tiktok','instagram'], true);
                                $p = $pfConfig[$platformValue] ?? $pfConfig['youtube'];
                            @endphp
                            <a href="{{ route('videos.show', $video) }}"
                               class="group bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-slate-100">
                                <div class="relative w-full {{ $vert ? 'aspect-[9/16]' : 'aspect-video' }} bg-slate-900 overflow-hidden flex-shrink-0">
                                    @if($video->thumbnail_url)
                                        <img src="{{ $video->thumbnail_url }}" alt="{{ e($video->title) }}"
                                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="absolute inset-0 {{ $p['overlay'] }} flex items-center justify-center">
                                            <i class="fa-brands {{ $p['icon'] }} text-white text-4xl opacity-40"></i>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300 shadow-lg">
                                            <i class="fa-solid fa-play text-slate-800 text-base ml-1"></i>
                                        </div>
                                    </div>
                                    <span class="absolute top-2 left-2 text-xs font-bold {{ $p['badge'] }} rounded-full px-2 py-0.5 shadow-sm">
                                        <i class="fa-brands {{ $p['icon'] }} mr-0.5"></i>{{ ucfirst($platformValue) }}
                                    </span>
                                </div>
                                <div class="p-3 flex-1 flex flex-col gap-1">
                                    <p class="text-sm font-semibold text-slate-800 line-clamp-2 group-hover:text-blue-600 transition leading-snug">{{ $video->title }}</p>
                                    @if($video->published_at)
                                        <p class="text-xs text-slate-400 mt-auto">{{ $video->published_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @foreach($platformGrids as $platform => $items)
                <div class="social-grid hidden" data-grid="{{ $platform }}">
                    @if($items->isEmpty())
                        <div class="text-center py-16 text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <i class="fa-brands {{ $pfConfig[$platform]['icon'] }} text-5xl mb-3 opacity-30"></i>
                            <p>Aucun contenu {{ ucfirst($platform) }} synchronisé pour le moment.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            @foreach($items as $video)
                                @php $vert = in_array($platform, ['tiktok','instagram'], true); $p = $pfConfig[$platform]; @endphp
                                <a href="{{ route('videos.show', $video) }}"
                                   class="group bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-slate-100">
                                    <div class="relative w-full {{ $vert ? 'aspect-[9/16]' : 'aspect-video' }} bg-slate-900 overflow-hidden flex-shrink-0">
                                        @if($video->thumbnail_url)
                                            <img src="{{ $video->thumbnail_url }}" alt="{{ e($video->title) }}"
                                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                        @else
                                            <div class="absolute inset-0 {{ $p['overlay'] }} flex items-center justify-center">
                                                <i class="fa-brands {{ $p['icon'] }} text-white text-4xl opacity-40"></i>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center">
                                            <div class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300 shadow-lg">
                                                <i class="fa-solid fa-play text-slate-800 text-base ml-1"></i>
                                            </div>
                                        </div>
                                        <span class="absolute top-2 left-2 text-xs font-bold {{ $p['badge'] }} rounded-full px-2 py-0.5 shadow-sm">
                                            <i class="fa-brands {{ $p['icon'] }} mr-0.5"></i>{{ ucfirst($platform) }}
                                        </span>
                                    </div>
                                    <div class="p-3 flex-1 flex flex-col gap-1">
                                        <p class="text-sm font-semibold text-slate-800 line-clamp-2 group-hover:text-blue-600 transition leading-snug">{{ $video->title }}</p>
                                        <div class="flex items-center gap-3 mt-auto text-xs text-slate-400">
                                            @if($video->view_count) <span><i class="fa-regular fa-eye mr-0.5"></i>{{ number_format($video->view_count) }}</span> @endif
                                            @if($video->likes_count) <span><i class="fa-regular fa-heart mr-0.5"></i>{{ number_format($video->likes_count) }}</span> @endif
                                            @if($video->published_at) <span class="ml-auto">{{ $video->published_at->diffForHumans() }}</span> @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

        </div>
    </section>

    <script>
    (function () {
        const tabs  = document.querySelectorAll('.social-tab-btn');
        const grids = document.querySelectorAll('.social-grid');

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const target = btn.dataset.tab;

                tabs.forEach(function (t) {
                    t.classList.remove('active', 'bg-slate-800', 'text-white', 'bg-red-600', 'bg-blue-700', 'bg-pink-600', 'bg-slate-900');
                    t.classList.add('bg-white', 'border', 'text-slate-600');
                });
                btn.classList.remove('bg-white', 'border', 'text-slate-600');
                btn.classList.add('active');
                if (target === 'youtube') btn.classList.add('bg-red-600', 'text-white');
                else if (target === 'facebook') btn.classList.add('bg-blue-700', 'text-white');
                else if (target === 'instagram') btn.classList.add('bg-pink-600', 'text-white');
                else if (target === 'tiktok') btn.classList.add('bg-slate-900', 'text-white');
                else btn.classList.add('bg-slate-800', 'text-white');

                grids.forEach(function (g) {
                    g.classList.toggle('hidden', g.dataset.grid !== target);
                });
            });
        });
    }());
    </script>

    <!-- Section CTA Appel à l'action -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Avez-vous besoin de soutien spirituel?</h2>
            <p class="text-lg text-blue-100 mb-8 max-w-2xl mx-auto">
                Nos pasteurs et membres sont là pour vous accompagner dans votre cheminement spirituel.
            </p>
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <a href="{{ route('contact.prayer') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                    <i class="fas fa-pray mr-2"></i>
                    Soumettre une demande de prière
                </a>
                <a href="{{ route('contact.create') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-envelope mr-2"></i>
                    Nous contacter
                </a>
            </div>
        </div>
    </section>

    <!-- Section Newsletter -->
    <section class="py-14 bg-gradient-to-r from-blue-700 to-blue-900 text-white">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl md:text-3xl font-bold mb-3">
                <i class="fas fa-bell mr-2"></i>Abonnez-vous à notre newsletter
            </h2>
            <p class="text-blue-100 mb-6">Recevez les derniers Échos de la mission directement dans votre boîte mail.</p>

            @if(session('newsletter_success'))
                <div class="mb-4 bg-green-500 text-white px-4 py-3 rounded-lg inline-block">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('newsletter_success') }}
                </div>
            @elseif(session('newsletter_info'))
                <div class="mb-4 bg-yellow-400 text-white px-4 py-3 rounded-lg inline-block">
                    <i class="fas fa-info-circle mr-2"></i>{{ session('newsletter_info') }}
                </div>
            @endif

            <form action="{{ route('newsletter.subscribe') }}" method="POST"
                  class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                @csrf
                <input type="text" name="name" placeholder="Votre prénom"
                       class="flex-1 px-4 py-3 rounded-lg text-slate-900 focus:outline-none">
                <input type="email" name="email" required placeholder="Votre adresse e-mail"
                       class="flex-1 px-4 py-3 rounded-lg text-slate-900 focus:outline-none">
                <button type="submit"
                        class="bg-white text-blue-700 font-bold px-6 py-3 rounded-lg hover:bg-blue-50 transition whitespace-nowrap">
                    <i class="fas fa-paper-plane mr-2"></i>S'abonner
                </button>
            </form>
            @error('email')
                <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>
    </section>
@endsection
