@extends('layouts.app')

@section('title', $video->title)
@section('meta_description', Str::limit(strip_tags($video->description ?? $video->title), 160))

@section('content')
@php
    $isVertical = in_array($video->platform->value, ['tiktok', 'instagram']);
    $platformConfig = [
        'youtube'   => ['color' => 'red',    'hex' => '#dc2626', 'icon' => 'fa-youtube',   'label' => 'YouTube'],
        'facebook'  => ['color' => 'blue',   'hex' => '#1d4ed8', 'icon' => 'fa-facebook',  'label' => 'Facebook'],
        'instagram' => ['color' => 'pink',   'hex' => '#db2777', 'icon' => 'fa-instagram', 'label' => 'Instagram'],
        'tiktok'    => ['color' => 'slate',  'hex' => '#1e293b', 'icon' => 'fa-tiktok',    'label' => 'TikTok'],
    ];
    $pf = $platformConfig[$video->platform->value] ?? $platformConfig['youtube'];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Fil d'Ariane --}}
    <nav class="flex items-center gap-2 text-sm text-slate-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-blue-600 transition">Accueil</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <a href="{{ route('videos.index') }}" class="hover:text-blue-600 transition">Contenus</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <a href="{{ route('videos.index', ['platform' => $video->platform->value]) }}" class="hover:text-blue-600 transition">
            {{ $pf['label'] }}
        </a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-slate-600 line-clamp-1">{{ $video->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ── Colonne principale ──────────────────────────────────────────── --}}
        <div class="lg:col-span-2">

            {{-- Lecteur --}}
            <div class="rounded-2xl overflow-hidden bg-slate-900 shadow-xl"
                 id="player-wrapper">
                <div class="relative {{ $isVertical ? 'max-w-xs mx-auto' : 'w-full' }}">
                    {{-- Click-to-play : miniature + bouton --}}
                    <div id="video-player"
                         class="relative w-full {{ $isVertical ? 'aspect-[9/16]' : 'aspect-video' }} cursor-pointer"
                         data-src="{{ $video->embed_url_autoplay }}"
                         data-platform="{{ $video->platform->value }}"
                         role="button"
                         tabindex="0"
                         aria-label="Lire : {{ e($video->title) }}">

                        @if ($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}"
                                 alt="{{ e($video->title) }}"
                                 class="absolute inset-0 w-full h-full object-cover"
                                 id="thumb-img">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-800">
                                <i class="fa-brands {{ $pf['icon'] }} text-white text-7xl opacity-30"></i>
                            </div>
                        @endif

                        {{-- Overlay play --}}
                        <div class="absolute inset-0 bg-black/20 flex flex-col items-center justify-center gap-3">
                            <div class="w-20 h-20 rounded-full bg-white/90 shadow-2xl flex items-center justify-center
                                        hover:scale-110 transition-transform">
                                <i class="fa-solid fa-play text-slate-800 text-3xl ml-2"></i>
                            </div>
                            <span class="text-white/80 text-sm font-medium">
                                Cliquez pour lire
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Titre + meta --}}
            <div class="mt-6">
                {{-- Badge plateforme --}}
                <div class="flex items-center gap-3 mb-3">
                    <a href="{{ route('videos.index', ['platform' => $video->platform->value]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full
                              @if($video->platform->value === 'youtube') bg-red-100 text-red-600
                              @elseif($video->platform->value === 'facebook') bg-blue-100 text-blue-700
                              @elseif($video->platform->value === 'instagram') bg-pink-100 text-pink-600
                              @else bg-slate-100 text-slate-800
                              @endif">
                        <i class="fa-brands {{ $pf['icon'] }}"></i>
                        {{ $pf['label'] }}
                    </a>
                    @if($video->published_at)
                        <span class="text-xs text-slate-400">
                            <i class="fa-regular fa-calendar mr-1"></i>
                            {{ $video->published_at->isoFormat('D MMMM YYYY') }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold text-slate-900 leading-snug mb-2">
                    {{ $video->title }}
                </h1>

                {{-- Stats --}}
                <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 py-3 border-y mb-4">
                    @if($video->channel_title)
                        <span class="font-medium text-slate-700">
                            <i class="fa-solid fa-circle-user mr-1 text-slate-400"></i>{{ $video->channel_title }}
                        </span>
                    @endif
                    @if($video->view_count)
                        <span><i class="fa-regular fa-eye mr-1"></i>{{ number_format($video->view_count) }} vues</span>
                    @endif
                    @if($video->likes_count)
                        <span><i class="fa-regular fa-heart mr-1 text-red-400"></i>{{ number_format($video->likes_count) }}</span>
                    @endif
                    @if($video->comments_count)
                        <span><i class="fa-regular fa-comment mr-1 text-blue-400"></i>{{ number_format($video->comments_count) }}</span>
                    @endif
                    @if($video->duration && $video->platform->value === 'youtube')
                        <span><i class="fa-regular fa-clock mr-1"></i>{{ \App\Helpers\MediaHelper::formatDuration($video->duration) }}</span>
                    @endif

                    {{-- Lien vers la source originale --}}
                    @if($video->post_url)
                        <a href="{{ $video->post_url }}" target="_blank" rel="noopener noreferrer"
                           class="ml-auto inline-flex items-center gap-1.5 text-xs font-semibold
                                  @if($video->platform->value === 'youtube') text-red-600 hover:text-red-700
                                  @elseif($video->platform->value === 'facebook') text-blue-700 hover:text-blue-800
                                  @elseif($video->platform->value === 'instagram') text-pink-600 hover:text-pink-700
                                  @else text-slate-700 hover:text-slate-900
                                  @endif transition">
                            <i class="fa-brands {{ $pf['icon'] }}"></i>
                            Voir sur {{ $pf['label'] }}
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                    @endif
                </div>

                {{-- Description --}}
                @if($video->description)
                    <div class="prose prose-sm max-w-none text-slate-600" id="description-container">
                        <p class="whitespace-pre-line line-clamp-4" id="description-text">{{ $video->description }}</p>
                    </div>
                    @if(strlen($video->description) > 300)
                        <button id="desc-toggle"
                                class="mt-2 text-blue-600 text-sm hover:underline font-medium">
                            Afficher plus
                        </button>
                    @endif
                @endif
            </div>

            {{-- Partage --}}
            <div class="mt-6 p-4 bg-slate-50 rounded-xl">
                <p class="text-sm font-semibold text-slate-700 mb-3">
                    <i class="fa-solid fa-share-nodes mr-2 text-blue-500"></i>Partager
                </p>
                <div class="flex flex-wrap gap-2">
                    @php $shareUrl = urlencode(request()->url()); $shareTitle = urlencode($video->title); @endphp
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 bg-[#1877F2] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition">
                        <i class="fa-brands fa-facebook"></i> Facebook
                    </a>
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 bg-[#25D366] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareTitle }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 bg-[#229ED9] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition">
                        <i class="fa-brands fa-telegram"></i> Telegram
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ request()->url() }}').then(() => this.textContent = '✓ Copié !')"
                            class="inline-flex items-center gap-2 bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-slate-300 transition">
                        <i class="fa-regular fa-copy"></i> Copier le lien
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Colonne latérale : suggestions ─────────────────────────────── --}}
        <aside class="lg:col-span-1">
            <h3 class="font-bold text-slate-800 mb-4 text-lg">
                <i class="fa-solid fa-list mr-2 text-blue-500"></i>
                Autres contenus {{ $pf['label'] }}
            </h3>

            @forelse($related as $rel)
                @php $relVertical = in_array($rel->platform->value, ['tiktok', 'instagram']); @endphp
                <a href="{{ route('videos.show', $rel) }}"
                   class="flex gap-3 mb-4 group hover:bg-slate-50 rounded-xl p-2 transition">

                    {{-- Miniature suggestion --}}
                    <div class="flex-shrink-0 relative overflow-hidden rounded-lg bg-slate-800
                                {{ $relVertical ? 'w-16 aspect-[9/16]' : 'w-32 aspect-video' }}">
                        @if($rel->thumbnail_url)
                            <img src="{{ $rel->thumbnail_url }}"
                                 alt="{{ e($rel->title) }}"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fa-brands {{ $pf['icon'] }} text-white text-xl opacity-40"></i>
                            </div>
                        @endif
                        @if($rel->duration && $rel->platform->value === 'youtube')
                            <span class="absolute bottom-1 right-1 bg-black/70 text-white text-xs px-1 rounded font-mono">
                                {{ \App\Helpers\MediaHelper::formatDuration($rel->duration) }}
                            </span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 line-clamp-2 group-hover:text-blue-600 transition">
                            {{ $rel->title }}
                        </p>
                        @if($rel->channel_title)
                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $rel->channel_title }}</p>
                        @endif
                        @if($rel->published_at)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $rel->published_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-400">Aucune autre suggestion disponible.</p>
            @endforelse

            <a href="{{ route('videos.index', ['platform' => $video->platform->value]) }}"
               class="block text-center mt-4 text-blue-600 text-sm font-semibold hover:underline">
                Voir tous les contenus {{ $pf['label'] }} <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </aside>
    </div>
</div>

{{-- ── Scripts ──────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    // ── Click-to-play ────────────────────────────────────────────────────────
    const player = document.getElementById('video-player');
    if (player) {
        function loadPlayer() {
            const src = player.dataset.src;
            if (!src) return;

            const iframe = document.createElement('iframe');
            iframe.src             = src;
            iframe.title           = player.getAttribute('aria-label') ?? 'Lecteur vidéo';
            iframe.className       = 'absolute inset-0 w-full h-full border-0';
            iframe.allow           = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            iframe.allowFullscreen = true;

            player.innerHTML = '';
            player.appendChild(iframe);
            player.style.cursor = 'default';
            player.removeAttribute('role');
            player.removeAttribute('tabindex');
        }

        player.addEventListener('click', loadPlayer);
        player.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); loadPlayer(); }
        });
    }

    // ── Afficher/Masquer la description ─────────────────────────────────────
    const toggleBtn  = document.getElementById('desc-toggle');
    const descText   = document.getElementById('description-text');
    if (toggleBtn && descText) {
        let expanded = false;
        toggleBtn.addEventListener('click', function () {
            expanded = !expanded;
            descText.classList.toggle('line-clamp-4', !expanded);
            toggleBtn.textContent = expanded ? 'Afficher moins' : 'Afficher plus';
        });
    }
}());
</script>
@endsection
