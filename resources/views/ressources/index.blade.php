@extends('layouts.app')

@section('title', 'Ressources - Livres & Ouvrages')
@section('meta_description', 'Téléchargez gratuitement des livres, ouvrages et manuels chrétiens de Hope Media RDC.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- En-tête -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-slate-900 mb-3">
            <i class="fas fa-book-open text-blue-600 mr-2"></i>Ressources
        </h1>
        <p class="text-slate-500 text-lg">Livres, ouvrages et manuels à télécharger gratuitement</p>
    </div>

    <!-- Filtres + Recherche -->
    <form method="GET" action="{{ route('ressources.index') }}" class="flex flex-wrap gap-3 mb-8 items-center">
        <div class="flex-1 min-w-[220px]">
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}"
                       placeholder="Rechercher un livre, un auteur…"
                       class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
            </div>
        </div>
        <select name="categorie" class="border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Toutes les catégories</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ ($category ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
            Filtrer
        </button>
        @if($search || $category)
            <a href="{{ route('ressources.index') }}" class="text-slate-500 hover:text-red-600 px-3 py-2 rounded-lg border border-slate-300 transition">
                <i class="fas fa-times mr-1"></i>Réinitialiser
            </a>
        @endif
    </form>

    <!-- Résultats -->
    <p class="text-sm text-slate-500 mb-6">{{ $resources->total() }} ressource(s) disponible(s)</p>

    @if($resources->isEmpty())
        <div class="text-center py-20">
            <i class="fas fa-book text-6xl text-slate-200 mb-4 block"></i>
            <p class="text-slate-400 text-lg">Aucune ressource disponible pour le moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($resources as $resource)
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">
                    <!-- Couverture -->
                    <a href="{{ route('ressources.show', $resource->slug) }}" class="block">
                        @if($resource->cover_image)
                            <img src="{{ asset($resource->cover_image) }}" alt="{{ $resource->title }}"
                                 class="w-full h-52 object-cover">
                        @else
                            <div class="w-full h-52 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                                <i class="fas fa-book text-5xl text-blue-400"></i>
                            </div>
                        @endif
                    </a>

                    <div class="p-4 flex flex-col flex-1">
                        <!-- Catégorie -->
                        <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-semibold mb-2">
                            {{ $resource->category_label }}
                        </span>

                        <!-- Titre -->
                        <a href="{{ route('ressources.show', $resource->slug) }}"
                           class="font-bold text-slate-900 hover:text-blue-600 transition line-clamp-2 mb-1">
                            {{ $resource->title }}
                        </a>

                        <!-- Auteur -->
                        @if($resource->author)
                            <p class="text-sm text-slate-500 mb-3">
                                <i class="fas fa-user mr-1"></i>{{ $resource->author }}
                            </p>
                        @endif

                        <!-- Infos fichier -->
                        <div class="mt-auto flex items-center justify-between text-xs text-slate-400">
                            <span>
                                <i class="fas fa-file-{{ $resource->file_type === 'pdf' ? 'pdf text-red-400' : 'alt text-blue-400' }} mr-1"></i>
                                {{ strtoupper($resource->file_type) }} · {{ $resource->formatted_file_size }}
                            </span>
                            <span><i class="fas fa-download mr-1"></i>{{ number_format($resource->downloads) }}</span>
                        </div>

                        <!-- Bouton télécharger -->
                        <a href="{{ route('ressources.download', $resource) }}"
                           class="mt-3 block w-full text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded-lg transition">
                            <i class="fas fa-download mr-1"></i>Télécharger
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $resources->links() }}
        </div>
    @endif
</div>
@endsection
