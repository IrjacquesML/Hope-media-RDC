@extends('layouts.app')

@section('title', $title ?? 'Enseignements')
@section('meta_description', 'Découvrez tous nos enseignements bibliques')

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">Enseignements</div>
        <h1>{{ $title ?? 'Tous les enseignements' }}</h1>
        <p>Explorez notre collection d'enseignements spirituels : prédications, études bibliques, audios et lectures.</p>
    </div>
</header>

<div class="wrap page-body">
    @include('posts.partials.type-filters')

    @if ($posts->count() > 0)
        <div class="grid-cards">
            @foreach ($posts as $post)
                <x-site.post-card :post="$post" />
            @endforeach
        </div>
        {{ $posts->links('partials.pagination') }}
    @else
        <div class="empty">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <strong>Aucun enseignement trouvé</strong>
            Il n'y a pas encore d'enseignement disponible.
            <a class="btn btn-line" href="{{ route('home') }}">Retourner à l'accueil</a>
        </div>
    @endif
</div>
@endsection
