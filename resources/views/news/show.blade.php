@extends('layouts.app')

@section('title', $article->meta_title ?? $article->title)
@section('meta_description', $article->excerpt)
@section('og_type', 'article')
@if ($article->featured_image)
    @section('og_image', asset($article->featured_image))
@endif

@php
    $shareUrl = request()->url();
    $date = $article->published_at ?? $article->created_at;
    $minutes = max(1, (int) ceil(str_word_count(strip_tags($article->content)) / 200));
@endphp

@section('content')
<div class="wrap">
    <header class="article-head">
        <nav class="crumbs" aria-label="Fil d'Ariane">
            <a href="{{ route('home') }}">Accueil</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('news.index') }}">Écho de la mission</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span>{{ Str::limit($article->title, 50) }}</span>
        </nav>
        <div class="meta">
            @if ($article->is_featured)<span class="chip gold"><i class="fa-solid fa-star" aria-hidden="true"></i> À la une</span>@endif
            @if ($article->category)
                <a class="cat-tag c-{{ abs(crc32($article->category)) % 4 }}" href="{{ route('news.index', ['categorie' => $article->category]) }}">{{ $article->category }}</a>
            @endif
        </div>
        <h1>{{ $article->title }}</h1>
        <div class="meta">
            <span><i class="fa-solid fa-calendar" aria-hidden="true"></i>{{ $date->translatedFormat('d F Y') }}</span>
            @if ($article->author)
                <span><i class="fa-solid fa-user" aria-hidden="true"></i><a href="{{ route('news.index', ['auteur' => $article->author]) }}">{{ $article->author }}</a></span>
            @endif
            <span><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ number_format($article->views, 0, ',', ' ') }} vue(s)</span>
            <button type="button" class="news-like-btn" aria-pressed="false"
                    data-like-toggle="{{ route('likes.news.toggle', $article) }}" data-like-check="{{ route('likes.news.check', $article) }}">
                <i class="fa-regular fa-heart" aria-hidden="true"></i><span class="like-count">{{ number_format($article->likes) }}</span> j'aime
            </button>
            <span><i class="fa-regular fa-comment" aria-hidden="true"></i>{{ $article->comments->count() }} commentaire(s)</span>
            <span><i class="fa-regular fa-clock" aria-hidden="true"></i>~{{ $minutes }} min de lecture</span>
        </div>
    </header>

    @if ($article->featured_image)
        <figure class="article-cover" style="margin:24px 0 0">
            <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" decoding="async">
        </figure>
    @endif

    <div class="article-layout" style="margin-top:28px">
        <div style="display:grid;gap:28px;min-width:0">
            @if ($article->tags)
                <div class="tags">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('news.index', ['q' => $tag]) }}">#{{ $tag }}</a>
                    @endforeach
                </div>
            @endif

            <article class="prose">{!! nl2br(e($article->content)) !!}</article>

            <section aria-labelledby="shareTitle" style="display:grid;gap:10px">
                <h2 id="shareTitle" class="sec-title" style="font-size:1.5rem">Partager cet article</h2>
                <div class="share">
                    <a class="btn btn-gold" href="https://wa.me/?text={{ urlencode($article->title . ' — ' . $shareUrl) }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                    <a class="btn btn-line" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i>Facebook</a>
                    <a class="btn btn-line" href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"><i class="fa-brands fa-twitter" aria-hidden="true"></i>Twitter</a>
                    <button class="btn btn-line" type="button" data-copy="{{ $shareUrl }}"><i class="fa-solid fa-link" aria-hidden="true"></i>Copier le lien</button>
                </div>
            </section>

            @include('partials.comments', [
                'comments' => $article->comments,
                'action' => route('comments.news.store', $article),
                'placeholder' => 'Partagez votre réaction à cet article...',
            ])
        </div>

        <aside style="display:grid;gap:18px;align-content:start;min-width:0">
            @if ($recent->isNotEmpty())
                <div class="side-list">
                    <h2>Autres articles</h2>
                    @foreach ($recent as $item)
                        <x-site.news-card :article="$item" :compact="true" class="side-item" />
                    @endforeach
                    <a class="btn btn-line" href="{{ route('news.index') }}">Voir tous les articles <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            @endif

            <div class="info-card">
                <h2>Catégories</h2>
                <div class="filters">
                    @foreach (\App\Models\News::categories() as $cat)
                        <a href="{{ route('news.index', ['categorie' => $cat]) }}" @if ($article->category === $cat) aria-current="true" @endif>{{ $cat }}</a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
