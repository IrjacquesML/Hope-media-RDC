{{-- Carte d'article de l'Écho de la mission : « une » (lead) ou compacte. --}}
@props(['article', 'lead' => false, 'compact' => false])
@php
    $date = $article->published_at ?? $article->created_at;
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $article->content)) / 200));
    $catIndex = $article->category ? abs(crc32($article->category)) % 4 : 0;
@endphp
<a {{ $attributes->merge(['class' => 'story' . ($lead ? ' lead' : '')]) }} href="{{ route('news.show', $article->slug) }}">
    <div class="thumb">
        @if ($article->featured_image)
            <img src="{{ asset($article->featured_image) }}" alt="" loading="lazy" decoding="async">
        @else
            <x-site.art :seed="$article->id" icon="fa-newspaper" />
        @endif
        @if ($article->is_featured && ! $compact)
            <span class="tl"><span class="chip gold"><i class="fa-solid fa-star" aria-hidden="true"></i> À la une</span></span>
        @endif
    </div>
    @unless ($lead)<div>@endunless
    <div class="meta">
        @if ($article->category)<span class="cat-tag c-{{ $catIndex }}">{{ $article->category }}</span>@endif
        @if ($date)<time datetime="{{ $date->toDateString() }}">{{ $date->translatedFormat('d M Y') }}</time>@endif
        @unless ($compact)<span>{{ $minutes }} min de lecture</span>@endunless
    </div>
    <h3>{{ $article->title }}</h3>
    @if ($lead)<p>{{ $article->excerpt }}</p>@endif
    @unless ($lead)</div>@endunless
</a>
