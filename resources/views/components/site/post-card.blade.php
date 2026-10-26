{{-- Carte d'enseignement (Post) avec vues, « J'aime » et commentaires. --}}
@props(['post', 'likes' => true])
@php
    $typeIcons = ['video' => 'fa-circle-play', 'audio' => 'fa-music', 'image' => 'fa-image', 'text' => 'fa-file-lines'];
    $typeIcon = $typeIcons[$post->content_type] ?? 'fa-file-lines';
@endphp
<article class="card">
    <a class="thumb" href="{{ $post->url }}" tabindex="-1" aria-hidden="true">
        @if ($post->featured_image)
            <img src="{{ asset($post->featured_image) }}" alt="" loading="lazy" decoding="async">
        @else
            <x-site.art :seed="$post->id" :icon="$typeIcon" />
        @endif
        <span class="platform"><i class="fa-solid {{ $typeIcon }}" aria-hidden="true"></i></span>
        @if ($post->is_featured)
            <span class="tl"><span class="chip gold"><i class="fa-solid fa-star" aria-hidden="true"></i> En vedette</span></span>
        @endif
        @if ($post->content_type === 'video')<span class="play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>@endif
    </a>
    <div class="meta">
        @if ($post->category)
            <a class="cat-tag c-{{ $post->category->id % 4 }}" href="{{ route('posts.category', $post->category->slug) }}">{{ $post->category->name }}</a>
        @endif
        @if ($post->published_at)<span>{{ $post->published_at->translatedFormat('d M Y') }}</span>@endif
    </div>
    <h3><a href="{{ $post->url }}">{{ $post->title }}</a></h3>
    <p class="excerpt">{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}</p>
    <div class="meta">
        <span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $post->author ?? 'Anonyme' }}</span>
        @if ($post->teaching_date)<span><i class="fa-solid fa-cross" aria-hidden="true"></i>Enseignement du {{ $post->teaching_date->translatedFormat('d M Y') }}</span>@endif
    </div>
    <div class="card-foot">
        <div class="stats">
            <span title="Vues"><i class="fa-regular fa-eye" aria-hidden="true"></i> {{ $post->views }}<span class="sr-only"> vues</span></span>
            @if ($likes)
                <button class="like-btn" type="button" aria-pressed="false"
                        data-like-toggle="{{ route('likes.toggle', $post) }}" data-like-check="{{ route('likes.check', $post) }}"
                        aria-label="Aimer « {{ $post->title }} »">
                    <i class="fa-regular fa-heart" aria-hidden="true"></i><span class="like-count">{{ $post->likes }}</span>
                </button>
            @endif
            @isset($post->comments_count)
                <span title="Commentaires"><i class="fa-regular fa-comment" aria-hidden="true"></i> {{ $post->comments_count }}<span class="sr-only"> commentaires</span></span>
            @endisset
        </div>
        <a class="more" href="{{ $post->url }}">Lire <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</article>
