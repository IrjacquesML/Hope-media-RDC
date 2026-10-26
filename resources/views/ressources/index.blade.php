@extends('layouts.app')

@section('title', 'Ressources - Livres & Ouvrages')
@section('meta_description', 'Téléchargez gratuitement des livres, ouvrages et manuels chrétiens de Hope Media RDC.')

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">Bibliothèque</div>
        <h1>Ressources</h1>
        <p>Livres, ouvrages et manuels à télécharger gratuitement.</p>
    </div>
</header>

<div class="wrap page-body">
    <form method="GET" action="{{ route('ressources.index') }}" class="search-form" role="search" aria-label="Filtrer les ressources">
        <label class="sr-only" for="resQ">Rechercher un livre, un auteur</label>
        <input class="input" id="resQ" type="search" name="q" value="{{ $search ?? '' }}" placeholder="Rechercher un livre, un auteur…">
        <label class="sr-only" for="resCat">Catégorie</label>
        <select class="select" id="resCat" name="categorie" style="flex:0 1 220px;width:auto;height:48px">
            <option value="">Toutes les catégories</option>
            @foreach ($categories as $key => $label)
                <option value="{{ $key }}" @selected(($category ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-gold"><i class="fa-solid fa-filter" aria-hidden="true"></i>Filtrer</button>
        @if ($search || $category)
            <a href="{{ route('ressources.index') }}" class="btn btn-line"><i class="fa-solid fa-xmark" aria-hidden="true"></i>Réinitialiser</a>
        @endif
    </form>

    <p class="result-count">{{ $resources->total() }} ressource(s) disponible(s)</p>

    @if ($resources->isEmpty())
        <div class="empty">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <strong>Aucune ressource disponible pour le moment.</strong>
        </div>
    @else
        <div class="books">
            @foreach ($resources as $resource)
                <article class="book">
                    <a class="cover" href="{{ route('ressources.show', $resource->slug) }}" tabindex="-1" aria-hidden="true">
                        @if ($resource->cover_image)
                            <img src="{{ asset($resource->cover_image) }}" alt="" loading="lazy">
                        @else
                            <x-site.art :seed="$resource->id" icon="fa-book" :title="$resource->title" />
                        @endif
                    </a>
                    <span class="cat-tag c-{{ abs(crc32((string) $resource->category)) % 4 }}">{{ $resource->category_label }}</span>
                    <h3><a href="{{ route('ressources.show', $resource->slug) }}">{{ $resource->title }}</a></h3>
                    @if ($resource->author)<div class="meta"><span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $resource->author }}</span></div>@endif
                    <div class="meta">
                        <span><i class="fa-regular fa-file" aria-hidden="true"></i>{{ strtoupper($resource->file_type) }} · {{ $resource->formatted_file_size }}</span>
                        <span><i class="fa-solid fa-download" aria-hidden="true"></i>{{ number_format($resource->downloads, 0, ',', ' ') }}</span>
                    </div>
                    <a class="btn btn-line" href="{{ route('ressources.download', $resource) }}"><i class="fa-solid fa-download" aria-hidden="true"></i>Télécharger</a>
                </article>
            @endforeach
        </div>
        {{ $resources->links('partials.pagination') }}
    @endif
</div>
@endsection
