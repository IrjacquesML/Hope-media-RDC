@extends('layouts.app')

@php
    $pfKey = $video->platform->value;
    $isVertical = in_array($pfKey, ['tiktok', 'instagram']);
    $platformConfig = [
        'youtube'   => ['icon' => 'fa-youtube',   'label' => 'YouTube'],
        'facebook'  => ['icon' => 'fa-facebook',  'label' => 'Facebook'],
        'instagram' => ['icon' => 'fa-instagram', 'label' => 'Instagram'],
        'tiktok'    => ['icon' => 'fa-tiktok',    'label' => 'TikTok'],
        'x'         => ['icon' => 'fa-x-twitter', 'label' => 'X'],
    ];
    $pf = $platformConfig[$pfKey] ?? $platformConfig['youtube'];
    $title = html_entity_decode((string) $video->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $description = html_entity_decode((string) $video->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $duration = ($video->duration ? (is_numeric($video->duration) ? sprintf('%d:%02d', intdiv((int) $video->duration, 60), (int) $video->duration % 60) : \App\Helpers\MediaHelper::formatDuration($video->duration)) : null);
    $shareUrl = request()->url();
@endphp

@section('title', $title)
@section('meta_description', Str::limit(strip_tags($description ?: $title), 160))
@section('og_type', 'video.other')
@if ($video->thumbnail_url)
    @section('og_image', $video->thumbnail_url)
@endif

@section('content')
<div class="wrap watch">
    <article style="min-width:0">
        <nav class="crumbs" aria-label="Fil d'Ariane">
            <a href="{{ route('home') }}">Accueil</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('videos.index') }}">Contenus</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('videos.index', ['platform' => $pfKey]) }}">{{ $pf['label'] }}</a>
        </nav>

        {{-- Lecteur : vignette jusqu'au clic --}}
        <div class="player{{ $isVertical ? ' is-vertical' : '' }}" id="video-player"
             data-src="{{ $video->embed_url_autoplay }}" data-platform="{{ $pfKey }}"
             role="button" tabindex="0" aria-label="Lire : {{ $title }}">
            @if ($video->thumbnail_url)
                <img src="{{ $video->thumbnail_url }}" alt="" id="thumb-img">
            @else
                <x-site.art :seed="$video->id" icon="fa-circle-play" />
            @endif
            <div class="player-facade">
                <span class="player-top"><span class="pf-pill p-{{ $pfKey }}"><i class="fa-brands {{ $pf['icon'] }}" aria-hidden="true"></i>{{ $pf['label'] }}</span></span>
                <span class="big-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
            </div>
        </div>

        <h1 class="{{ trim(($__t = html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === mb_strtoupper($__t) && preg_match('/\p{Lu}{4}/u', $__t) ? ' is-caps' : '') }}">{{ $title }}</h1>
        <div class="meta">
            @if ($video->published_at)<span><i class="fa-solid fa-calendar" aria-hidden="true"></i>{{ $video->published_at->isoFormat('D MMMM YYYY') }}</span>@endif
            @if ($video->channel_title)<span><i class="fa-solid fa-circle-user" aria-hidden="true"></i>{{ $video->channel_title }}</span>@endif
            @if ($video->view_count)<span><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ number_format($video->view_count, 0, ',', ' ') }} vues</span>@endif
            @if ($video->likes_count)<span><i class="fa-regular fa-heart" aria-hidden="true"></i>{{ number_format($video->likes_count, 0, ',', ' ') }}</span>@endif
            @if ($video->comments_count)<span><i class="fa-regular fa-comment" aria-hidden="true"></i>{{ number_format($video->comments_count, 0, ',', ' ') }}</span>@endif
            @if ($duration)<span><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $duration }}</span>@endif
        </div>

        <div class="watch-actions">
            <a class="btn btn-gold" href="https://wa.me/?text={{ urlencode($title) }}%20{{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
            <a class="btn btn-line" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook" aria-hidden="true"></i>Facebook</a>
            <a class="btn btn-line" href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($title) }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-telegram" aria-hidden="true"></i>Telegram</a>
            <button class="btn btn-line" type="button" data-copy="{{ $shareUrl }}"><i class="fa-solid fa-link" aria-hidden="true"></i>Copier le lien</button>
            @if ($video->post_url)
                <a class="btn btn-line" href="{{ $video->post_url }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Voir sur {{ $pf['label'] }}</a>
            @endif
        </div>

        @if ($description)
            <div class="desc" id="description-container">
                <p class="text{{ strlen($description) > 300 ? ' clamped' : '' }}" id="description-text">{{ $description }}</p>
                @if (strlen($description) > 300)
                    <button id="desc-toggle" type="button" aria-expanded="false" aria-controls="description-text">Afficher plus</button>
                @endif
            </div>
        @endif
    </article>

    <aside class="side-list" aria-labelledby="relatedTitle">
        <h2 id="relatedTitle">Autres contenus {{ $pf['label'] }}</h2>
        @forelse ($related as $rel)
            @php
                $relVertical = in_array($rel->platform->value, ['tiktok', 'instagram']);
                $relDuration = $rel->platform->value === 'youtube' && $rel->duration ? \App\Helpers\MediaHelper::formatDuration($rel->duration) : null;
            @endphp
            <a class="card side-item{{ $relVertical ? ' is-vertical' : '' }}" href="{{ route('videos.show', $rel) }}">
                <div class="thumb">
                    @if ($rel->thumbnail_url)
                        <img src="{{ $rel->thumbnail_url }}" alt="" loading="lazy">
                    @else
                        <x-site.art :seed="$rel->id" icon="fa-circle-play" />
                    @endif
                    @if ($relDuration)<span class="badges"><span class="chip dur">{{ $relDuration }}</span></span>@endif
                </div>
                <div>
                    <h3 class="{{ trim(($__t = html_entity_decode((string) $rel->title, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === mb_strtoupper($__t) && preg_match('/\p{Lu}{4}/u', $__t) ? ' is-caps' : '') }}">{{ html_entity_decode((string) $rel->title, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h3>
                    <div class="meta" style="margin-top:4px">
                        @if ($rel->channel_title)<span>{{ $rel->channel_title }}</span>@endif
                        @if ($rel->published_at)<span>{{ $rel->published_at->diffForHumans() }}</span>@endif
                    </div>
                </div>
            </a>
        @empty
            <p class="muted" style="margin:0">Aucune autre suggestion disponible.</p>
        @endforelse
        <a class="btn btn-line" href="{{ route('videos.index', ['platform' => $pfKey]) }}">Voir tous les contenus {{ $pf['label'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </aside>
</div>
@endsection
