@extends('layouts.app')

@section('title', "Résultats de recherche: {$query}")

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">Recherche</div>
        <h1>« {{ $query }} »</h1>
        <p>{{ $posts->total() }} résultat(s) trouvé(s) dans les enseignements.</p>
    </div>
</header>

<div class="wrap page-body">
    @include('posts.partials.type-filters')

    @if ($posts->count() > 0)
        <div class="grid-cards">
            @foreach ($posts as $post)
                <x-site.post-card :post="$post" :likes="false" />
            @endforeach
        </div>
        {{ $posts->links('partials.pagination') }}
    @else
        <div class="empty">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <strong>Aucun résultat</strong>
            Nous n'avons trouvé aucun enseignement correspondant à votre recherche.
            <a class="btn btn-line" href="{{ route('posts.index') }}">Voir tous les enseignements</a>
        </div>
    @endif
</div>
@endsection
