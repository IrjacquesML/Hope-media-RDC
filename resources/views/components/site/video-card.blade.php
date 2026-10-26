{{-- Carte vidéo synchronisée (YouTube, Facebook, Instagram, TikTok). 16:9, ou 9:16 pour les formats verticaux. --}}
@props(['video', 'vertical' => null, 'stats' => false])
@php
    $pf = is_object($video->platform) ? $video->platform->value : $video->platform;
    $vertical ??= in_array($pf, ['tiktok', 'instagram'], true);
    $icons = ['youtube' => 'fa-youtube', 'facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'tiktok' => 'fa-tiktok', 'x' => 'fa-x-twitter'];
    $labels = ['youtube' => 'YouTube', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'x' => 'X'];
    $title = html_entity_decode((string) $video->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $duration = ($video->duration ? (is_numeric($video->duration) ? sprintf('%d:%02d', intdiv((int) $video->duration, 60), (int) $video->duration % 60) : \App\Helpers\MediaHelper::formatDuration($video->duration)) : null); // ISO (PT17M18S) ou secondes (TikTok)
@endphp
<a class="card{{ $vertical ? ' is-vertical' : '' }}" href="{{ route('videos.show', $video) }}" data-pf="{{ $pf }}"
   aria-label="{{ $title }} — {{ $labels[$pf] ?? $pf }}{{ $duration ? ', ' . $duration : '' }}">
    <div class="thumb">
        @if ($video->thumbnail_url)
            <img src="{{ $video->thumbnail_url }}" alt="" loading="lazy" decoding="async">
        @else
            <x-site.art :seed="$video->id" icon="fa-circle-play" />
        @endif
        <span class="platform p-{{ $pf }}"><i class="fa-brands {{ $icons[$pf] ?? 'fa-youtube' }}" aria-hidden="true"></i></span>
        <span class="play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
        @php $isNew = $video->published_at && $video->published_at->gt(now()->subDays(7)); @endphp
        @if ($duration || $isNew)
            <span class="badges">
                @if ($isNew)<span class="chip gold">Nouveau</span>@endif
                @if ($duration)<span class="chip dur">{{ $duration }}</span>@endif
            </span>
        @endif
    </div>
    <h3 class="{{ trim(($__t = html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === mb_strtoupper($__t) && preg_match('/\p{Lu}{4}/u', $__t) ? ' is-caps' : '') }}">{{ $title }}</h3>
    <div class="meta">
        <span>{{ $labels[$pf] ?? ucfirst($pf) }}</span>
        @if ($video->published_at)<span>{{ $video->published_at->diffForHumans() }}</span>@endif
        @if ($stats && $video->view_count)<span><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ number_format($video->view_count, 0, ',', ' ') }}</span>@endif
        @if ($stats && $video->likes_count)<span><i class="fa-regular fa-heart" aria-hidden="true"></i>{{ number_format($video->likes_count, 0, ',', ' ') }}</span>@endif
    </div>
</a>
