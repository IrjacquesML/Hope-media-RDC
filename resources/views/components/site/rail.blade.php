{{-- Rangée horizontale à faire glisser (liste verticale sur téléphone). --}}
@props(['id', 'title', 'kicker' => null, 'href' => null, 'vertical' => false])
<section class="wrap row" aria-labelledby="row-{{ $id }}" style="margin-bottom:clamp(28px,4vw,44px)">
    <div class="row-head">
        <h2 id="row-{{ $id }}">@if ($kicker)<small>{{ $kicker }}</small>@endif{{ $title }}</h2>
        <div class="row-tools">
            @if ($href)<a class="more" href="{{ $href }}">Tout voir <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>@endif
            <button class="rail-btn" type="button" data-dir="-1" aria-label="Défiler vers la gauche : {{ $title }}"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <button class="rail-btn" type="button" data-dir="1" aria-label="Défiler vers la droite : {{ $title }}"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="rail{{ $vertical ? ' is-vertical' : '' }}">{{ $slot }}</div>
</section>
