@extends('layouts.app')

@section('title', 'Vidéos')
@section('meta_description', 'Retrouvez tous nos contenus publiés sur YouTube, Facebook, Instagram et TikTok.')

@php
    $platforms = [
        'youtube'   => ['YouTube', 'fa-youtube'],
        'facebook'  => ['Facebook', 'fa-facebook'],
        'instagram' => ['Instagram', 'fa-instagram'],
        'tiktok'    => ['TikTok', 'fa-tiktok'],
        'x'         => ['X', 'fa-x-twitter'],
    ];
    $verticalOnly = in_array($currentPlatform, ['tiktok', 'instagram'], true);
@endphp

@section('content')
{{-- Hero compact --}}
<section class="hero compact" id="hero" aria-roledescription="carrousel" aria-label="Contenus sociaux" data-slide-ms="6500">
    @foreach (['images/hero-sunrise.jpg', 'images/hero-bible.jpg', 'images/hero-hope.jpg'] as $i => $img)
        <div class="hero-slide{{ $i === 0 ? ' is-active' : '' }}">
            <img src="{{ asset($img) }}" alt="" @if ($i > 0) loading="lazy" @endif decoding="async">
        </div>
    @endforeach
    <div class="wrap hero-inner">
        <div class="hero-copy">
            <span class="hero-slogan">Hope Media RDC · En continu</span>
            <h1>Vidéos</h1>
            <p class="lead">Enseignements, témoignages et contenus spirituels sur YouTube, Facebook, Instagram et TikTok, réunis au même endroit.</p>
        </div>
        <div class="hero-dots" role="group" aria-label="Diapositives du carrousel">
            @foreach ([0, 1, 2] as $i)
                <button type="button" data-index="{{ $i }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Image {{ $i + 1 }}"></button>
            @endforeach
        </div>
    </div>
</section>

<div class="wrap page-body">
    <div class="sync">
        <nav class="filters" aria-label="Filtrer par plateforme">
            <a href="{{ route('videos.index') }}" @if (! $currentPlatform) aria-current="true" @endif><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Tout <span class="count">{{ $total }}</span></a>
            @foreach ($platforms as $key => [$label, $icon])
                <a href="{{ route('videos.index', ['platform' => $key]) }}" @if ($currentPlatform === $key) aria-current="true" @endif>
                    <i class="fa-brands {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                    @if ($counts[$key] ?? 0)<span class="count">{{ $counts[$key] }}</span>@endif
                </a>
            @endforeach
        </nav>
        <p class="sync-status"><span class="ok" aria-hidden="true"></span>Synchronisé automatiquement depuis nos réseaux</p>
    </div>

    @if ($videos->isEmpty())
        <div class="empty">
            <i class="fa-solid fa-satellite-dish" aria-hidden="true"></i>
            <strong>Aucun contenu synchronisé pour le moment.</strong>
            Les contenus seront automatiquement importés depuis les réseaux sociaux.
        </div>
    @else
        <div class="grid-cards{{ $verticalOnly ? ' is-vertical' : '' }}">
            @foreach ($videos as $video)
                <x-site.video-card :video="$video" :vertical="$verticalOnly ? true : false" :stats="true" />
            @endforeach
        </div>
        {{ $videos->links('partials.pagination') }}
    @endif
</div>
@endsection
