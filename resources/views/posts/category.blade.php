@extends('layouts.app')

@section('title', $category->name)
@section('meta_description', $category->description ?? 'Découvrez tous les enseignements de la catégorie ' . $category->name)

@section('content')
<header class="page-head">
    <div class="wrap">
        <nav class="crumbs" aria-label="Fil d'Ariane">
            <a href="{{ route('home') }}">Accueil</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('posts.index') }}">Enseignements</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span>{{ $category->name }}</span>
        </nav>
        <div class="eyebrow">Catégorie</div>
        <h1>{{ $category->name }}</h1>
        @if ($category->description)<p>{{ $category->description }}</p>@endif
    </div>
</header>

<div class="wrap page-body">
    @if ($posts->count() > 0)
        <div class="grid-cards">
            @foreach ($posts as $post)
                <x-site.post-card :post="$post" />
            @endforeach
        </div>
        {{ $posts->links('partials.pagination') }}
    @else
        <div class="empty">
            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
            <strong>Aucun enseignement</strong>
            Il n'y a pas encore d'enseignement dans cette catégorie.
            <a class="btn btn-line" href="{{ route('home') }}">Retourner à l'accueil</a>
        </div>
    @endif
</div>
@endsection
