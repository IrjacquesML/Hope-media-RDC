@extends('layouts.app')

@section('title', "Résultats de recherche: {$query}")

@section('content')
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-search mr-3"></i>
                Résultats de recherche
            </h1>
            <p class="text-lg text-blue-100">Pour: <span class="font-semibold">"{{ $query }}"</span></p>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($posts->count() > 0)
            <div class="mb-6">
                <p class="text-slate-600 text-lg">
                    <i class="fas fa-info-circle mr-2"></i>
                    {{ $posts->total() }} résultat(s) trouvé(s)
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition transform hover:scale-105">
                        <div class="relative h-56 bg-gradient-to-br from-slate-300 to-slate-400">
                            @if($post->featured_image)
                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-blue-100">
                                    <i class="fas fa-{{ $post->getContentIconAttribute() }} text-5xl text-blue-600 opacity-30"></i>
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2">
                                <a href="{{ $post->url }}" class="hover:text-blue-600">{{ $post->title }}</a>
                            </h3>
                            
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3">
                                {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">{{ $post->formatted_date }}</span>
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
                <div class="text-6xl text-slate-300 mb-4">
                    <i class="fas fa-search"></i>
                </div>
                <p class="text-2xl text-slate-600 mb-4">Aucun résultat</p>
                <p class="text-slate-500 mb-8">Nous n'avons trouvé aucun enseignement correspondant à votre recherche.</p>
                <a href="{{ route('posts.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Voir tous les enseignements
                </a>
            </div>
        @endif
    </div>
@endsection
