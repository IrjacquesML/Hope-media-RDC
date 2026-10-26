@extends('layouts.app')

@section('title', $typeLabel ?? ucfirst($type))

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">Enseignements</div>
        <h1>{{ $typeLabel ?? ucfirst($type) }}</h1>
        <p>Explorez tous nos {{ mb_strtolower($typeLabel ?? ucfirst($type)) }}.</p>
    </div>
</header>

<div class="wrap page-body">
    @include('posts.partials.type-filters', ['currentType' => $type])

    @if ($posts->count() > 0)
        <div class="grid-cards">
            @foreach ($posts as $post)
                <x-site.post-card :post="$post" :likes="false" />
            @endforeach
        </div>
        {{ $posts->links('partials.pagination') }}
    @else
        <div class="empty">
            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
            <strong>Aucun enseignement trouvé</strong>
            Il n'y a pas encore de {{ mb_strtolower($typeLabel ?? ucfirst($type)) }} disponible.
        </div>
    @endif
</div>
@endsection
