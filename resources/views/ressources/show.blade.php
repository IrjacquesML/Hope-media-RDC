@extends('layouts.app')

@section('title', $resource->title)
@section('meta_description', Str::limit(strip_tags($resource->description), 160))
@if ($resource->cover_image)
    @section('og_image', asset($resource->cover_image))
@endif

@section('content')
<div class="wrap">
    <div class="book-detail">
        <div style="display:grid;gap:14px;align-content:start">
            <div class="book">
                <div class="cover">
                    @if ($resource->cover_image)
                        <img src="{{ asset($resource->cover_image) }}" alt="Couverture : {{ $resource->title }}">
                    @else
                        <x-site.art :seed="$resource->id" icon="fa-book" :title="$resource->title" />
                    @endif
                </div>
            </div>
            <a class="btn btn-gold btn-block" href="{{ route('ressources.download', $resource) }}"><i class="fa-solid fa-download" aria-hidden="true"></i>Télécharger gratuitement</a>
            <dl class="facts">
                <div><dt>Format</dt><dd>{{ strtoupper($resource->file_type) }}</dd></div>
                <div><dt>Taille</dt><dd>{{ $resource->formatted_file_size }}</dd></div>
                <div><dt>Téléchargements</dt><dd>{{ number_format($resource->downloads, 0, ',', ' ') }}</dd></div>
            </dl>
        </div>

        <div style="display:grid;gap:16px;min-width:0;align-content:start">
            <nav class="crumbs" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}">Accueil</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                <a href="{{ route('ressources.index') }}">Ressources</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                <span>{{ Str::limit($resource->title, 50) }}</span>
            </nav>
            <span class="cat-tag c-{{ abs(crc32((string) $resource->category)) % 4 }}">{{ $resource->category_label }}</span>
            <h1 class="content-title">{{ $resource->title }}</h1>
            <div class="meta">
                @if ($resource->author)<span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $resource->author }}</span>@endif
                @if ($resource->publisher)<span><i class="fa-solid fa-building" aria-hidden="true"></i>{{ $resource->publisher }}</span>@endif
                @if ($resource->published_year)<span><i class="fa-solid fa-calendar" aria-hidden="true"></i>{{ $resource->published_year }}</span>@endif
            </div>
            @if ($resource->description)
                <div class="prose">{!! nl2br(e($resource->description)) !!}</div>
            @endif
            <div style="display:grid;gap:10px">
                <h2 class="sec-title" style="font-size:1.4rem">Partager cette ressource</h2>
                <div class="share">
                    <a class="btn btn-gold" href="https://wa.me/?text={{ urlencode($resource->title . ' - ' . url()->current()) }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                    <a class="btn btn-line" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i>Facebook</a>
                </div>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section style="margin-top:48px" aria-labelledby="relTitle">
            <div class="row-head"><h2 id="relTitle"><small>Bibliothèque</small>Ressources similaires</h2></div>
            <div class="books">
                @foreach ($related as $item)
                    <article class="book">
                        <a class="cover" href="{{ route('ressources.show', $item->slug) }}" tabindex="-1" aria-hidden="true">
                            @if ($item->cover_image)
                                <img src="{{ asset($item->cover_image) }}" alt="" loading="lazy">
                            @else
                                <x-site.art :seed="$item->id" icon="fa-book" :title="$item->title" />
                            @endif
                        </a>
                        <h3><a href="{{ route('ressources.show', $item->slug) }}">{{ $item->title }}</a></h3>
                        <a class="btn btn-line" href="{{ route('ressources.download', $item) }}"><i class="fa-solid fa-download" aria-hidden="true"></i>Télécharger</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
