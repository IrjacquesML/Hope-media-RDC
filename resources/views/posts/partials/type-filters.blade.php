{{-- Recherche + filtres par type de contenu (partagés par les listes d'enseignements). --}}
@php
    $types = [
        ''      => ['Tous', 'fa-layer-group'],
        'video' => ['Vidéos', 'fa-circle-play'],
        'audio' => ['Audios', 'fa-music'],
        'text'  => ['Lectures', 'fa-file-lines'],
        'image' => ['Images', 'fa-image'],
    ];
    $currentType = $currentType ?? null;
@endphp
<div class="toolbar">
    <nav class="filters" aria-label="Type de contenu">
        @foreach ($types as $key => [$label, $icon])
            <a href="{{ $key !== '' ? route('posts.by-type', $key) : route('posts.index') }}"
               @if (($key === '' && request()->routeIs('posts.index')) || ($key !== '' && $currentType === $key)) aria-current="true" @endif>
                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}
            </a>
        @endforeach
    </nav>
    <form class="search-form" action="{{ route('posts.search') }}" method="GET" role="search" style="flex:1 1 280px;max-width:440px">
        <label class="sr-only" for="postSearch">Rechercher un enseignement</label>
        <input class="input" id="postSearch" type="search" name="q" value="{{ $query ?? '' }}" placeholder="Rechercher un enseignement…" required minlength="3">
        <button class="btn btn-line" type="submit" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
    </form>
</div>
