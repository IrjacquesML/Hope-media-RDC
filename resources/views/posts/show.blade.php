@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? Str::limit(strip_tags($post->content), 160))
@section('meta_keywords', $post->meta_keywords ?? '')
@section('og_type', 'article')
@if ($post->featured_image)
    @section('og_image', asset($post->featured_image))
@endif

@php
    $shareUrl = request()->url();
    $typeIcons = ['video' => 'fa-circle-play', 'audio' => 'fa-music', 'image' => 'fa-image', 'text' => 'fa-file-lines'];
    $typeLabels = ['video' => 'Vidéo', 'audio' => 'Audio', 'image' => 'Image', 'text' => 'Lecture'];
@endphp

@section('content')
<div class="wrap">
    <header class="article-head">
        <nav class="crumbs" aria-label="Fil d'Ariane">
            <a href="{{ route('home') }}">Accueil</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('posts.index') }}">Enseignements</a>
            @if ($post->category)
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i><a href="{{ route('posts.category', $post->category->slug) }}">{{ $post->category->name }}</a>
            @endif
        </nav>
        <div class="meta">
            @if ($post->category)
                <a class="cat-tag c-{{ $post->category->id % 4 }}" href="{{ route('posts.category', $post->category->slug) }}">{{ $post->category->name }}</a>
            @endif
            <span><i class="fa-solid {{ $typeIcons[$post->content_type] ?? 'fa-file-lines' }}" aria-hidden="true"></i>{{ $typeLabels[$post->content_type] ?? ucfirst($post->content_type) }}</span>
        </div>
        <h1>{{ $post->title }}</h1>
        <div class="meta">
            <span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $post->author ?? 'Anonyme' }}</span>
            <span><i class="fa-solid fa-calendar" aria-hidden="true"></i>{{ $post->formatted_date }}</span>
            @if ($post->teaching_date)
                <span><i class="fa-solid fa-cross" aria-hidden="true"></i>Enseignement du {{ $post->teaching_date->translatedFormat('d M Y') }}</span>
            @endif
        </div>
    </header>

    @if ($post->featured_image)
        <figure class="article-cover" style="margin:24px 0 0">
            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" decoding="async">
        </figure>
    @endif

    <div class="article-layout" style="margin-top:28px">
        <div style="display:grid;gap:28px;min-width:0">

            {{-- Médias associés --}}
            @if ($post->medias->count() > 0)
                <section class="form-card" aria-labelledby="mediaTitle">
                    <h2 id="mediaTitle">Contenu multimédia</h2>
                    @foreach ($post->medias as $media)
                        <div class="media-block">
                            <h3>
                                @if ($media->isYouTube())<i class="fa-brands fa-youtube" style="color:#E62117" aria-hidden="true"></i>
                                @elseif ($media->isVimeo())<i class="fa-brands fa-vimeo" style="color:#1AB7EA" aria-hidden="true"></i>
                                @elseif ($media->media_type === 'dailymotion')<i class="fa-solid fa-circle-play" aria-hidden="true"></i>
                                @elseif ($media->isLocal())<i class="fa-solid fa-download" aria-hidden="true"></i>
                                @endif
                                {{ $media->title }}
                            </h3>
                            @if ($media->description)<p class="muted" style="margin:0">{{ $media->description }}</p>@endif

                            @php $isAudio = $media->isLocal() && in_array($media->mime_type, ['audio/mpeg', 'audio/ogg', 'audio/wav']); @endphp
                            <div class="media-frame{{ $isAudio ? ' audio' : '' }}">
                                @if ($media->isYouTube())
                                    <iframe src="{{ $media->getYouTubeEmbedUrl() }}" title="{{ $media->title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                @elseif ($media->isVimeo())
                                    <iframe src="{{ $media->getVimeoEmbedUrl() }}" title="{{ $media->title }}" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                                @elseif ($media->media_type === 'dailymotion')
                                    <iframe src="{{ $media->getDailyMotionEmbedUrl() }}" title="{{ $media->title }}" loading="lazy" allow="autoplay; fullscreen" allowfullscreen></iframe>
                                @elseif ($media->isLocal())
                                    @if (in_array($media->mime_type, ['video/mp4', 'video/webm', 'video/ogg']))
                                        <video controls preload="none" poster="{{ $media->getThumbnailUrl() }}">
                                            <source src="{{ $media->getLocalUrl() }}" type="{{ $media->mime_type }}">
                                            Votre navigateur ne supporte pas le lecteur vidéo HTML5.
                                        </video>
                                    @elseif ($isAudio)
                                        <audio controls preload="none">
                                            <source src="{{ $media->getLocalUrl() }}" type="{{ $media->mime_type }}">
                                            Votre navigateur ne supporte pas le lecteur audio HTML5.
                                        </audio>
                                    @elseif (in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/webp']))
                                        <img src="{{ $media->getLocalUrl() }}" alt="{{ $media->title }}" loading="lazy">
                                    @endif
                                @endif
                            </div>

                            <div class="meta">
                                <span class="tags"><span>
                                    @if ($media->isYouTube()) YouTube
                                    @elseif ($media->isVimeo()) Vimeo
                                    @elseif ($media->media_type === 'dailymotion') Dailymotion
                                    @elseif ($media->isLocal()) Fichier local
                                    @endif
                                </span></span>
                                @if ($media->duration)<span><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $media->formatted_duration }}</span>@endif
                                @if ($media->file_size)<span><i class="fa-solid fa-hard-drive" aria-hidden="true"></i>{{ $media->formatted_file_size }}</span>@endif
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif

            {{-- Texte --}}
            <article class="prose">{!! $post->content !!}</article>

            {{-- Partage --}}
            <section aria-labelledby="shareTitle" style="display:grid;gap:10px">
                <h2 id="shareTitle" class="sec-title" style="font-size:1.5rem">Partager cet enseignement</h2>
                <div class="share">
                    <a class="btn btn-gold" href="https://wa.me/?text={{ urlencode($post->title . ' ' . $shareUrl) }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                    <a class="btn btn-line" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook" aria-hidden="true"></i>Facebook</a>
                    <a class="btn btn-line" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener"><i class="fa-brands fa-twitter" aria-hidden="true"></i>Twitter</a>
                    <a class="btn btn-line" href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode($shareUrl) }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>Email</a>
                    <button class="btn btn-line" type="button" data-copy="{{ $shareUrl }}"><i class="fa-solid fa-link" aria-hidden="true"></i>Copier le lien</button>
                </div>
            </section>

            {{-- Approfondir --}}
            <section class="info-card" aria-labelledby="deeperTitle">
                <h2 id="deeperTitle"><i class="fa-solid fa-lightbulb" style="color:var(--accent-text)" aria-hidden="true"></i> Envie d'approfondir cet enseignement ?</h2>
                <p class="muted" style="margin:0">Contactez-nous pour des études bibliques plus détaillées ou posez vos questions.</p>
                <div><a class="btn btn-line" href="{{ route('contact.create') }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>Nous contacter</a></div>
            </section>

            @include('partials.comments', [
                'comments' => $post->comments,
                'action' => route('comments.store', $post),
                'placeholder' => 'Partagez votre réflexion sur cet enseignement...',
            ])
        </div>

        {{-- Barre latérale --}}
        <aside style="display:grid;gap:18px;align-content:start;min-width:0">
            <div class="info-card">
                <h2>Statistiques</h2>
                <div class="stat-list">
                    <div><span><i class="fa-regular fa-eye" aria-hidden="true"></i> Vues</span><b>{{ $post->views }}</b></div>
                    <div>
                        <button class="like-btn" type="button" aria-pressed="false" style="width:100%;justify-content:space-between;padding:0 4px"
                                data-like-toggle="{{ route('likes.toggle', $post) }}" data-like-check="{{ route('likes.check', $post) }}">
                            <span><i class="fa-regular fa-heart" aria-hidden="true"></i> Aimer cette publication</span>
                            <b class="like-count">{{ $post->likes }}</b>
                        </button>
                    </div>
                    <div><span><i class="fa-regular fa-comment" aria-hidden="true"></i> Commentaires</span><b>{{ $post->comments->count() }}</b></div>
                    <div class="muted"><span>Publié le</span><span>{{ $post->published_at ? $post->published_at->translatedFormat('d M Y') : 'Non publié' }}</span></div>
                </div>
            </div>

            @if ($post->category)
                <div class="info-card">
                    <h2>Catégorie</h2>
                    <a class="btn btn-line" href="{{ route('posts.category', $post->category->slug) }}"><i class="fa-solid fa-folder" aria-hidden="true"></i>{{ $post->category->name }}</a>
                </div>
            @endif

            @if ($post->author)
                <div class="info-card" style="background:linear-gradient(135deg,var(--hope),var(--night));color:#fff;border:0">
                    <h2><i class="fa-solid fa-circle-user" aria-hidden="true"></i> Auteur</h2>
                    <p style="margin:0;font-weight:700;font-size:1.1rem">{{ $post->author }}</p>
                    @if ($post->author_email)<p style="margin:0;opacity:.8;overflow-wrap:anywhere">{{ $post->author_email }}</p>@endif
                </div>
            @endif

            @if ($relatedPosts->count() > 0)
                <div class="side-list">
                    <h2>Posts connexes</h2>
                    @foreach ($relatedPosts as $relatedPost)
                        <a class="card side-item" href="{{ $relatedPost->url }}">
                            <div class="thumb">
                                @if ($relatedPost->featured_image)
                                    <img src="{{ asset($relatedPost->featured_image) }}" alt="" loading="lazy">
                                @else
                                    <x-site.art :seed="$relatedPost->id" :icon="$typeIcons[$relatedPost->content_type] ?? 'fa-file-lines'" />
                                @endif
                            </div>
                            <div>
                                <h3>{{ $relatedPost->title }}</h3>
                                <div class="meta" style="margin-top:4px">{{ $relatedPost->formatted_date }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
</div>

{{-- Prière --}}
<section class="wrap" aria-labelledby="prayTitle">
    <div class="prayer">
        <x-site.art theme="t-dawn" icon="fa-hands-praying" style="--gx:80%;--gy:10%" />
        <div class="prayer-in">
            <h2 id="prayTitle">En besoin de prière&nbsp;?</h2>
            <p>Partagez vos demandes de prière avec notre communauté. Nous prions ensemble pour le soutien spirituel.</p>
            <div class="prayer-cta"><a class="btn btn-gold" href="{{ route('contact.prayer') }}"><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>Soumettre une demande</a></div>
        </div>
    </div>
</section>
@endsection
