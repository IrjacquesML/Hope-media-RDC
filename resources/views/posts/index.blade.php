@extends('layouts.app')

@section('title', $title ?? 'Enseignements')
@section('meta_description', 'Découvrez tous nos enseignements bibliques')

@section('content')
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-list mr-3"></i>
                {{ $title ?? 'Tous les enseignements' }}
            </h1>
            <p class="text-lg text-blue-100">Explorez notre vaste collection d'enseignements spirituels</p>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Barre de filtrage et recherche -->
        <div class="mb-12">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Recherche -->
                    <div class="md:col-span-2">
                        <form action="{{ route('posts.search') }}" method="GET" class="flex">
                            <div class="flex-grow relative">
                                <input 
                                    type="text" 
                                    name="q" 
                                    placeholder="Rechercher un enseignement..."
                                    class="w-full px-4 py-2 border border-slate-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                    required
                                >
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-6 rounded-r-lg hover:bg-blue-700 transition">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Tri -->
                    <div>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option>Plus récents</option>
                            <option>Les plus vus</option>
                            <option>Les plus aimés</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres rapides (Types de contenu) -->
        <div class="mb-8 flex flex-wrap gap-3">
            <a href="{{ route('posts.index') }}" class="px-4 py-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition font-semibold">
                <i class="fas fa-list mr-2"></i>
                Tous
            </a>
            <a href="{{ route('posts.by-type', 'video') }}" class="px-4 py-2 rounded-full bg-white text-slate-700 border-2 border-red-500 hover:bg-red-50 transition font-semibold">
                <i class="fas fa-play-circle text-red-500 mr-2"></i>
                Vidéos
            </a>
            <a href="{{ route('posts.by-type', 'audio') }}" class="px-4 py-2 rounded-full bg-white text-slate-700 border-2 border-green-500 hover:bg-green-50 transition font-semibold">
                <i class="fas fa-music text-green-500 mr-2"></i>
                Audios
            </a>
            <a href="{{ route('posts.by-type', 'text') }}" class="px-4 py-2 rounded-full bg-white text-slate-700 border-2 border-blue-500 hover:bg-blue-50 transition font-semibold">
                <i class="fas fa-book text-blue-500 mr-2"></i>
                Lectures
            </a>
            <a href="{{ route('posts.by-type', 'image') }}" class="px-4 py-2 rounded-full bg-white text-slate-700 border-2 border-purple-500 hover:bg-purple-50 transition font-semibold">
                <i class="fas fa-image text-purple-500 mr-2"></i>
                Images
            </a>
        </div>

        <!-- Grille de posts -->
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition transform hover:scale-105">
                        <!-- Image d'en-tête -->
                        <div class="relative h-56 bg-gradient-to-br from-slate-300 to-slate-400 overflow-hidden">
                            @if($post->featured_image)
                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                    <i class="fas fa-{{ $post->getContentIconAttribute() }} text-5xl text-blue-600 opacity-30"></i>
                                </div>
                            @endif
                            
                            <!-- Badge type + Catégorie -->
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                    {{ $post->category->name }}
                                </span>
                            </div>

                            <!-- Badge type de contenu -->
                            <div class="absolute top-4 right-4 {{ match($post->content_type) {
                                'video' => 'bg-red-500',
                                'audio' => 'bg-green-500',
                                'text' => 'bg-blue-500',
                                'image' => 'bg-purple-500',
                                default => 'bg-slate-500'
                            } }} text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                                <i class="fas fa-{{ $post->getContentIconAttribute() }} text-lg"></i>
                            </div>

                            <!-- Statut en vedette -->
                            @if($post->is_featured)
                                <div class="absolute bottom-4 left-4">
                                    <span class="bg-yellow-400 text-slate-900 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                        <i class="fas fa-star"></i>
                                        En vedette
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Contenu de la carte -->
                        <div class="p-6">
                            <!-- Titre -->
                            <h3 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2 hover:text-blue-600">
                                <a href="{{ $post->url }}">{{ $post->title }}</a>
                            </h3>

                            <!-- Description -->
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3">
                                {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <!-- Métadonnées -->
                            <div class="space-y-2 mb-4 text-xs text-slate-500">
                                <div><i class="fas fa-user mr-2"></i> {{ $post->author ?? 'Anonyme' }}</div>
                                <div><i class="fas fa-calendar mr-2"></i> {{ $post->formatted_date }}</div>
                                @if($post->teaching_date)
                                    <div><i class="fas fa-cross mr-2"></i> Enseignement du {{ $post->teaching_date->format('d M Y') }}</div>
                                @endif
                            </div>

                            <!-- Stats d'engagement -->
                            <div class="flex items-center justify-between py-3 border-t border-slate-200">
                                <div class="flex gap-4 text-slate-500 text-sm">
                                    <span title="Vues">
                                        <i class="fas fa-eye mr-1"></i>{{ $post->views }}
                                    </span>
                                    <button class="like-btn-index text-red-500 hover:text-red-600 transition" data-post-id="{{ $post->id }}" title="Aimer cette publication" type="button">
                                        <i class="far fa-heart mr-1"></i><span class="like-count">{{ $post->likes }}</span>
                                    </button>
                                    <span title="Commentaires">
                                        <i class="fas fa-comment mr-1 text-blue-400"></i>{{ $post->comments_count }}
                                    </span>
                                </div>
                                <a href="{{ $post->url }}" class="text-blue-600 font-semibold hover:text-blue-700 transition">
                                    Lire <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="text-6xl text-slate-300 mb-4">
                    <i class="fas fa-inbox"></i>
                </div>
                <p class="text-2xl text-slate-600 mb-4">Aucun enseignement trouvé</p>
                <p class="text-slate-500 mb-8">Il n'y a pas encore d'enseignement disponible dans cette catégorie.</p>
                <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Retourner à l'accueil
                </a>
            </div>
        @endif
    </div>

    <!-- JavaScript pour les likes -->
    <script>
        document.querySelectorAll('.like-btn-index').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
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
                    }
                })
                .catch(error => console.error('Error:', error));
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
    </script>
@endsection
