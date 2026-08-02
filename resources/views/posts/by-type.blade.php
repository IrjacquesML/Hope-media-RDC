@extends('layouts.app')

@section('title', $typeLabel ?? ucfirst($type))

@section('content')
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-{{ match($type) {
                    'video' => 'play-circle',
                    'audio' => 'music',
                    'text' => 'book',
                    'image' => 'image',
                    default => 'file'
                } }} mr-3"></i>
                {{ $typeLabel ?? ucfirst($type) }}
            </h1>
            <p class="text-lg text-blue-100">Explorez tous nos {{ strtolower($typeLabel ?? ucfirst($type)) }}</p>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Grille de posts -->
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition transform hover:scale-105">
                        <div class="relative h-56 bg-gradient-to-br from-slate-300 to-slate-400 overflow-hidden">
                            @if($post->featured_image)
                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                    <i class="fas fa-{{ $post->getContentIconAttribute() }} text-5xl text-blue-600 opacity-30"></i>
                                </div>
                            @endif
                            
                            <div class="absolute top-4 left-4">
                                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                    {{ $post->category->name }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2">
                                <a href="{{ $post->url }}" class="hover:text-blue-600">{{ $post->title }}</a>
                            </h3>
                            
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3">
                                {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <div class="flex gap-3">
                                    <span><i class="fas fa-user mr-1"></i> {{ $post->author ?? 'Anonyme' }}</span>
                                    <span><i class="fas fa-comment text-blue-400 mr-1"></i>{{ $post->comments_count }}</span>
                                </div>
                                <a href="{{ $post->url }}" class="text-blue-600 font-semibold hover:text-blue-700">
                                    Lire <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $posts->links() }}
        @else
            <div class="text-center py-16">
                <p class="text-2xl text-slate-600 mb-4">Aucun enseignement trouvé</p>
                <p class="text-slate-500">Il n'y a pas encore de {{ strtolower($typeLabel ?? ucfirst($type)) }} disponible.</p>
            </div>
        @endif
    </div>
@endsection
