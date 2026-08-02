@extends('layouts.app')

@section('title', $category->name)
@section('meta_description', $category->description ?? 'Découvrez tous les enseignements de la catégorie ' . $category->name)

@section('content')
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-folder mr-3"></i>
                {{ $category->name }}
            </h1>
            <p class="text-lg text-blue-100">{{ $category->description }}</p>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Grille de posts -->
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition transform hover:scale-105">
                        <!-- Image -->
                        <div class="relative h-56 bg-gradient-to-br from-slate-300 to-slate-400 overflow-hidden">
                            @if($post->featured_image)
                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                    <i class="fas fa-{{ $post->getContentIconAttribute() }} text-5xl text-blue-600 opacity-30"></i>
                                </div>
                            @endif
                            
                            <!-- Badge type -->
                            <div class="absolute top-4 right-4 {{ match($post->content_type) {
                                'video' => 'bg-red-500',
                                'audio' => 'bg-green-500',
                                'text' => 'bg-blue-500',
                                'image' => 'bg-purple-500',
                                default => 'bg-slate-500'
                            } }} text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                                <i class="fas fa-{{ $post->getContentIconAttribute() }} text-lg"></i>
                            </div>
                        </div>

                        <!-- Contenu -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2 hover:text-blue-600">
                                <a href="{{ $post->url }}">{{ $post->title }}</a>
                            </h3>
                            
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3">
                                {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <div class="flex items-center justify-between text-xs text-slate-500 mb-4">
                                <span><i class="fas fa-user mr-1"></i> {{ $post->author ?? 'Anonyme' }}</span>
                                <span><i class="fas fa-calendar mr-1"></i> {{ $post->formatted_date }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex gap-2 text-slate-500">
                                    <span title="Vues"><i class="fas fa-eye"></i> {{ $post->views }}</span>
                                    <button class="like-btn-category text-red-500 hover:text-red-600 transition" data-post-id="{{ $post->id }}" title="Aimer cette publication" type="button">
                                        <i class="far fa-heart"></i> <span class="like-count">{{ $post->likes }}</span>
                                    </button>
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
                <p class="text-2xl text-slate-600 mb-4">Aucun enseignement</p>
                <p class="text-slate-500 mb-8">Il n'y a pas encore d'enseignement dans cette catégorie.</p>
                <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Retourner à l'accueil
                </a>
            </div>
        @endif
    </div>

    <!-- JavaScript pour les likes -->
    <script>
        document.querySelectorAll('.like-btn-category').forEach(button => {
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
