{{-- Visuel de secours (dégradé + halo + pictogramme) quand un contenu n'a pas d'image. --}}
@props(['seed' => 0, 'icon' => 'fa-book-open', 'title' => null, 'theme' => null])
@php
    $themes = ['t-sky', 't-river', 't-violet', 't-ember', 't-forest', 't-sand', 't-steel', 't-dawn'];
    $theme ??= $themes[abs(crc32((string) $seed)) % count($themes)];
@endphp
<div {{ $attributes->merge(['class' => 'art ' . $theme]) }} aria-hidden="true">
    <i class="fa-solid {{ $icon }}"></i>
    @if ($title)<span class="art-title">{{ $title }}</span>@endif
</div>
