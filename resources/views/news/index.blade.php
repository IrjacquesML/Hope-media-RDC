@extends('layouts.app')

@section('title', 'Écho de la mission')
@section('meta_description', 'Écho de la mission — les dernières nouvelles de l\'Église Adventiste du 7ème Jour - Union du Congo Ouest')

@php
    $filtered = request()->hasAny(['q', 'categorie', 'auteur']);
    $featured = $news->firstWhere('is_featured', true) ?? null;
    $others = $news->filter(fn ($a) => $filtered || ! $featured || $a->id !== $featured->id);
@endphp

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">Actualités</div>
        <h1>Écho de la mission</h1>
        <p>Dernières nouvelles de l'Église Adventiste du 7ème Jour.</p>
    </div>
</header>

<div class="wrap page-body">
    {{-- Recherche : un seul champ --}}
    <form method="GET" action="{{ route('news.index') }}" class="search-form" role="search" style="max-width:640px">
        <label class="sr-only" for="newsQ">Rechercher une actualité</label>
        <input class="input" id="newsQ" type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher une actualité…">
        <button class="btn btn-gold" type="submit" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
    </form>

    {{-- Filtre actif (recherche, ou catégorie / auteur depuis les liens d'un article) --}}
    @if ($filtered)
        <div class="active-filters">
            @if (request('q'))<span class="pill"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>« {{ request('q') }} »</span>@endif
            @if (request('categorie'))<span class="pill"><i class="fa-solid fa-tag" aria-hidden="true"></i>{{ request('categorie') }}</span>@endif
            @if (request('auteur'))<span class="pill"><i class="fa-solid fa-user" aria-hidden="true"></i>{{ request('auteur') }}</span>@endif
            <span>{{ $news->total() }} résultat(s)</span>
            <a class="more" href="{{ route('news.index') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i>Effacer</a>
        </div>
    @endif

    @if ($news->isEmpty())
        <div class="empty">
            <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
            <strong>Aucun article trouvé</strong>
            Essayez de modifier vos filtres ou revenez plus tard.
        </div>
    @else
        {{-- Article à la une --}}
        @if ($featured && ! $filtered)
            <div class="mag" style="grid-template-columns:minmax(0,1fr)">
                <a class="story lead" href="{{ route('news.show', $featured->slug) }}">
                    <div class="thumb">
                        @if ($featured->featured_image)
                            <img src="{{ asset($featured->featured_image) }}" alt="" decoding="async">
                        @else
                            <x-site.art :seed="$featured->id" icon="fa-newspaper" />
                        @endif
                        <span class="tl"><span class="chip gold"><i class="fa-solid fa-star" aria-hidden="true"></i> À la une</span></span>
                    </div>
                    <div class="meta">
                        @if ($featured->category)<span class="cat-tag c-{{ abs(crc32($featured->category)) % 4 }}">{{ $featured->category }}</span>@endif
                        <span>{{ ($featured->published_at ?? $featured->created_at)->translatedFormat('d F Y') }}</span>
                        @if ($featured->author)<span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $featured->author }}</span>@endif
                        <span><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ $featured->views }} vue(s)</span>
                    </div>
                    <h3>{{ $featured->title }}</h3>
                    <p>{{ $featured->excerpt }}</p>
                    @if ($featured->tags)
                        <div class="tags">@foreach ($featured->tags as $tag)<span>#{{ $tag }}</span>@endforeach</div>
                    @endif
                </a>
            </div>
        @endif

        @if ($others->isNotEmpty())
            <div class="grid-cards">
                @foreach ($others as $article)
                    <a class="card story" href="{{ route('news.show', $article->slug) }}">
                        <div class="thumb">
                            @if ($article->featured_image)
                                <img src="{{ asset($article->featured_image) }}" alt="" loading="lazy" decoding="async">
                            @else
                                <x-site.art :seed="$article->id" icon="fa-newspaper" />
                            @endif
                            @if ($article->is_featured)
                                <span class="tl"><span class="chip gold"><i class="fa-solid fa-star" aria-hidden="true"></i> À la une</span></span>
                            @endif
                        </div>
                        <div class="meta">
                            @if ($article->category)<span class="cat-tag c-{{ abs(crc32($article->category)) % 4 }}">{{ $article->category }}</span>@endif
                            <span>{{ ($article->published_at ?? $article->created_at)->translatedFormat('d M Y') }}</span>
                        </div>
                        <h3>{{ $article->title }}</h3>
                        <p class="excerpt">{{ $article->excerpt }}</p>
                        @if ($article->tags)
                            <div class="tags">@foreach (array_slice($article->tags, 0, 3) as $tag)<span>#{{ $tag }}</span>@endforeach</div>
                        @endif
                        <div class="meta">
                            @if ($article->author)<span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $article->author }}</span>@endif
                            <span><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ $article->views }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{ $news->links('partials.pagination') }}
    @endif
</div>
@endsection
