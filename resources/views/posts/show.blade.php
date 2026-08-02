@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? Str::limit(strip_tags($post->content), 160))
@section('meta_keywords', $post->meta_keywords ?? '')

@section('content')
    <!-- En-tête du post -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Catégorie -->
            <div class="mb-4">
                <a href="{{ route('posts.category', $post->category->slug) }}" class="inline-block bg-blue-400 hover:bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-semibold transition">
                    {{ $post->category->name }}
                </a>
            </div>

            <!-- Titre -->
            <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ $post->title }}</h1>

            <!-- Métadonnées -->
            <div class="flex flex-wrap gap-6 text-blue-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user"></i>
                    <span>{{ $post->author ?? 'Anonyme' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $post->formatted_date }}</span>
                </div>
                @if($post->teaching_date)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-cross"></i>
                        <span>Enseignement du {{ $post->teaching_date->format('d M Y') }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <i class="fas fa-{{ $post->getContentIconAttribute() }}"></i>
                    <span>{{ ucfirst($post->content_type) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenu principal (2 colonnes) -->
            <div class="lg:col-span-2">
                <!-- Image en vedette -->
                @if($post->featured_image)
                    <div class="mb-8 rounded-lg overflow-hidden shadow-lg">
                        <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto">
                    </div>
                @endif

                <!-- Médias associés (Vidéos, Audios, Images) -->
                @if($post->medias->count() > 0)
                    <div class="mb-12 bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                            <i class="fas fa-play-circle text-blue-600"></i>
                            Contenu multimédia
                        </h2>

                        <div class="space-y-8">
                            @foreach($post->medias as $media)
                                <div class="border-b pb-8 last:border-b-0">
                                    <!-- Titre du média avec icône de source -->
                                    <div class="flex items-center gap-3 mb-4">
                                        @if($media->isYouTube())
                                            <i class="fab fa-youtube text-red-600 text-xl"></i>
                                        @elseif($media->isVimeo())
                                            <i class="fab fa-vimeo text-blue-600 text-xl"></i>
                                        @elseif($media->media_type === 'dailymotion')
                                            <i class="fas fa-play-circle text-blue-500 text-xl"></i>
                                        @elseif($media->isLocal())
                                            <i class="fas fa-download text-green-600 text-xl"></i>
                                        @endif
                                        <h3 class="text-xl font-bold text-slate-900">{{ $media->title }}</h3>
                                    </div>

                                    <!-- Description -->
                                    @if($media->description)
                                        <p class="text-slate-600 mb-4">{{ $media->description }}</p>
                                    @endif

                                    <!-- Lecteur média -->
                                    <div class="mb-4 rounded-lg overflow-hidden bg-slate-900 aspect-video">
                                        @if($media->isYouTube())
                                            <iframe 
                                                width="100%" 
                                                height="100%" 
                                                src="{{ $media->getYouTubeEmbedUrl() }}" 
                                                frameborder="0" 
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                allowfullscreen>
                                            </iframe>
                                        @elseif($media->isVimeo())
                                            <iframe 
                                                src="{{ $media->getVimeoEmbedUrl() }}" 
                                                width="100%" 
                                                height="100%" 
                                                frameborder="0" 
                                                allow="autoplay; fullscreen; picture-in-picture" 
                                                allowfullscreen>
                                            </iframe>
                                        @elseif($media->media_type === 'dailymotion')
                                            <iframe 
                                                src="{{ $media->getDailyMotionEmbedUrl() }}" 
                                                width="100%" 
                                                height="100%" 
                                                frameborder="0" 
                                                allow="autoplay; fullscreen" 
                                                allowfullscreen>
                                            </iframe>
                                        @elseif($media->isLocal())
                                            @if(in_array($media->mime_type, ['video/mp4', 'video/webm', 'video/ogg']))
                                                <video controls class="w-full h-full" poster="{{ $media->getThumbnailUrl() }}">
                                                    <source src="{{ $media->getLocalUrl() }}" type="{{ $media->mime_type }}">
                                                    Votre navigateur ne supporte pas le lecteur vidéo HTML5.
                                                </video>
                                            @elseif(in_array($media->mime_type, ['audio/mpeg', 'audio/ogg', 'audio/wav']))
                                                <audio controls class="w-full h-full">
                                                    <source src="{{ $media->getLocalUrl() }}" type="{{ $media->mime_type }}">
                                                    Votre navigateur ne supporte pas le lecteur audio HTML5.
                                                </audio>
                                            @elseif(in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/webp']))
                                                <img src="{{ $media->getLocalUrl() }}" alt="{{ $media->title }}" class="w-full h-full object-cover">
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Métadonnées du média -->
                                    <div class="flex flex-wrap gap-4 text-sm text-slate-600">
                                        <span class="px-3 py-1 bg-slate-100 rounded-full">
                                            @if($media->isYouTube())
                                                <i class="fab fa-youtube text-red-600 mr-1"></i> YouTube
                                            @elseif($media->isVimeo())
                                                <i class="fab fa-vimeo text-blue-600 mr-1"></i> Vimeo
                                            @elseif($media->media_type === 'dailymotion')
                                                <i class="fas fa-play-circle text-blue-500 mr-1"></i> Dailymotion
                                            @elseif($media->isLocal())
                                                <i class="fas fa-download text-green-600 mr-1"></i> Local
                                            @endif
                                        </span>
                                        @if($media->duration)
                                            <span><i class="fas fa-clock mr-1"></i> {{ $media->formatted_duration }}</span>
                                        @endif
                                        @if($media->file_size)
                                            <span><i class="fas fa-hdd mr-1"></i> {{ $media->formatted_file_size }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Contenu textuel -->
                <div class="bg-white rounded-lg shadow-md p-8 prose prose-lg max-w-none mb-8">
                    {!! $post->content !!}
                </div>

                <!-- Partage social -->
                <div class="bg-gradient-to-r from-slate-100 to-slate-50 rounded-lg p-6 mb-8">
                    <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-share-alt"></i>
                        Partager cet enseignement
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-2 bg-sky-500 text-white px-4 py-2 rounded hover:bg-sky-600 transition">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . request()->url()) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode(request()->url()) }}" 
                           class="inline-flex items-center gap-2 bg-slate-600 text-white px-4 py-2 rounded hover:bg-slate-700 transition">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>

                <!-- Appel à l'action -->
                <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded mb-8">
                    <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-blue-600"></i>
                        Envie d'approfondir cet enseignement?
                    </h3>
                    <p class="text-slate-700 mb-4">Contactez-nous pour des études bibliques plus détaillées ou posez vos questions.</p>
                    <a href="{{ route('contact.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Nous contacter
                    </a>
                </div>

                <!-- Section Commentaires -->
                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                        <i class="fas fa-comments text-blue-600"></i>
                        Commentaires
                        <span class="text-sm font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ $post->comments->count() }}</span>
                    </h2>

                    {{-- Message de succès --}}
                    @if(session('comment_success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 text-green-800">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            {{ session('comment_success') }}
                        </div>
                    @endif

                    {{-- Formulaire d'ajout de commentaire --}}
                    <div class="mb-8 bg-slate-50 rounded-lg p-6 border border-slate-200">
                        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-pen text-blue-600"></i>
                            Laisser un commentaire
                        </h3>
                        <form action="{{ route('comments.store', $post) }}" method="POST" novalidate>
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="author_name" class="block text-sm font-medium text-slate-700 mb-1">
                                        Nom <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="author_name" name="author_name"
                                        value="{{ old('author_name') }}"
                                        required maxlength="100"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('author_name') border-red-400 @enderror"
                                        placeholder="Votre nom">
                                    @error('author_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="author_email" class="block text-sm font-medium text-slate-700 mb-1">
                                        Email <span class="text-slate-400 text-xs">(optionnel, non affiché)</span>
                                    </label>
                                    <input type="email" id="author_email" name="author_email"
                                        value="{{ old('author_email') }}"
                                        maxlength="255"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('author_email') border-red-400 @enderror"
                                        placeholder="votre@email.com">
                                    @error('author_email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="content" class="block text-sm font-medium text-slate-700 mb-1">
                                    Commentaire <span class="text-red-500">*</span>
                                </label>
                                <textarea id="content" name="content" rows="4" required maxlength="2000"
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none @error('content') border-red-400 @enderror"
                                    placeholder="Partagez votre réflexion sur cet enseignement...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-slate-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Votre commentaire sera visible immédiatement.
                                </p>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-medium text-sm">
                                    <i class="fas fa-paper-plane"></i>
                                    Envoyer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Liste des commentaires approuvés --}}
                    @if($post->comments->count() > 0)
                        <div class="space-y-6">
                            @foreach($post->comments as $comment)
                                <div class="flex gap-4 border-b border-slate-100 pb-6 last:border-b-0 last:pb-0">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-700 font-bold text-sm uppercase">
                                            {{ mb_substr($comment->author_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="font-semibold text-slate-900 text-sm">{{ $comment->author_name }}</span>
                                            <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-slate-700 text-sm leading-relaxed">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-500">
                            <i class="fas fa-comment-slash text-3xl mb-3 text-slate-300"></i>
                            <p class="text-sm">Aucun commentaire pour l'instant. Soyez le premier à réagir !</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Barre latérale -->
            <aside class="lg:col-span-1">
                <!-- Widget Statistiques -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="font-bold text-slate-900 mb-4">Statistiques</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">
                                <i class="fas fa-eye mr-2"></i>
                                Vues
                            </span>
                            <span class="font-bold text-blue-600">{{ $post->views }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <button id="like-btn-{{ $post->id }}" type="button" class="flex-1 flex justify-between items-center like-btn text-left text-slate-600 hover:text-red-600 transition py-2 px-3 rounded-lg hover:bg-red-50" data-post-id="{{ $post->id }}">
                                <span>
                                    <i class="fas fa-heart mr-2"></i>
                                    Aimer cette publication
                                </span>
                                <span class="font-bold text-red-600 like-count">{{ $post->likes }}</span>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">
                                <i class="fas fa-comment mr-2 text-blue-400"></i>
                                Commentaires
                            </span>
                            <span class="font-bold text-blue-600">{{ $post->comments->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-500 text-sm">
                            <span>Publié le</span>
                            <span>{{ $post->published_at ? $post->published_at->format('d M Y') : 'Non publié' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Widget Catégorie -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="font-bold text-slate-900 mb-4">Catégorie</h3>
                    <a href="{{ route('posts.category', $post->category->slug) }}" 
                       class="block bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700 transition">
                        <i class="fas fa-folder mr-2"></i>
                        {{ $post->category->name }}
                    </a>
                </div>

                <!-- Widget Auteur -->
                @if($post->author)
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-6 mb-6 text-white">
                        <h3 class="font-bold mb-2 flex items-center gap-2">
                            <i class="fas fa-user-circle"></i>
                            Auteur
                        </h3>
                        <p class="font-semibold text-lg">{{ $post->author }}</p>
                        @if($post->author_email)
                            <p class="text-blue-100 text-sm">{{ $post->author_email }}</p>
                        @endif
                    </div>
                @endif

                <!-- Posts connexes -->
                @if($relatedPosts->count() > 0)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-link text-blue-600"></i>
                            Posts connexes
                        </h3>
                        <div class="space-y-4">
                            @foreach($relatedPosts as $relatedPost)
                                <a href="{{ $relatedPost->url }}" class="group block">
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0">
                                            @if($relatedPost->featured_image)
                                                <img src="{{ asset($relatedPost->featured_image) }}" 
                                                     alt="{{ $relatedPost->title }}" 
                                                     class="w-12 h-12 rounded object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-{{ $relatedPost->getContentIconAttribute() }} text-blue-600 text-sm"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="font-semibold text-slate-900 text-sm group-hover:text-blue-600 transition line-clamp-2">
                                                {{ $relatedPost->title }}
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-1">{{ $relatedPost->formatted_date }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

    <!-- Section prière -->
    <section class="bg-gradient-to-r from-red-600 to-red-700 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4 flex items-center justify-center gap-3">
                <i class="fas fa-hands-praying"></i>
                En besoin de prière?
            </h2>
            <p class="text-lg text-red-100 mb-8 max-w-2xl mx-auto">
                Partagez vos demandes de prière avec notre communauté. Nous prions ensemble pour le soutien spirituel.
            </p>
            <a href="{{ route('contact.prayer') }}" class="inline-block bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition">
                <i class="fas fa-pray mr-2"></i>
                Soumettre une demande
            </a>
        </div>
    </section>

    <!-- JavaScript pour les likes -->
    <script>
        document.querySelectorAll('.like-btn').forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                const likeCount = this.querySelector('.like-count');
                const icon = this.querySelector('i');

                // Envoyer une requête AJAX pour liker/disliker
                fetch(`/likes/toggle/${postId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour le nombre de likes
                        likeCount.textContent = data.likes;
                        
                        // Changer l'icône en fonction du statut
                        if (data.isLiked) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }

                        // Afficher un toast de notification
                        showNotification(data.message, 'success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Une erreur s\'est produite', 'error');
                });
            });

            // Charger l'état initial des likes
            const postId = button.getAttribute('data-post-id');
            fetch(`/likes/check/${postId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const icon = button.querySelector('i');
                if (data.isLiked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Fonction pour afficher une notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white text-sm z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
@endsection
