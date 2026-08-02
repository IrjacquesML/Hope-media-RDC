@extends('layouts.app')

@section('title', 'Écho de la mission')
@section('meta_description', 'Écho de la mission — les dernières nouvelles de l\'Église Adventiste du 7ème Jour - Union du Congo Ouest')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- En-tête -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-slate-900 mb-3">
            <i class="fas fa-newspaper mr-3 text-blue-600"></i>Écho de la mission
        </h1>
        <p class="text-slate-600 text-lg">Dernières nouvelles de l'Église Adventiste du 7ème Jour</p>
    </div>

    <!-- Filtres & Recherche -->
    <form method="GET" action="{{ route('news.index') }}" class="mb-10 bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Recherche --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Recherche</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Titre, contenu..."
                           class="w-full border border-slate-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                </div>
            </div>

            {{-- Catégorie --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Catégorie</label>
                <select name="categorie"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('categorie') === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Auteur --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Auteur</label>
                <select name="auteur"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="">Tous les auteurs</option>
                    @foreach($authors as $aut)
                        <option value="{{ $aut }}" {{ request('auteur') === $aut ? 'selected' : '' }}>
                            {{ $aut }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-3 mt-4">
            <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                <i class="fas fa-filter mr-2"></i>Filtrer
            </button>
            @if(request()->hasAny(['q', 'categorie', 'auteur']))
                <a href="{{ route('news.index') }}"
                   class="bg-slate-100 text-slate-700 px-5 py-2 rounded-lg hover:bg-slate-200 transition text-sm font-semibold">
                    <i class="fas fa-times mr-2"></i>Réinitialiser
                </a>
            @endif
        </div>
    </form>

    {{-- Filtre actif --}}
    @if(request()->hasAny(['q', 'categorie', 'auteur']))
        <div class="mb-6 flex flex-wrap gap-2 items-center text-sm">
            <span class="text-slate-500 font-medium">Filtres actifs :</span>
            @if(request('q'))
                <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                    <i class="fas fa-search text-xs"></i> "{{ request('q') }}"
                </span>
            @endif
            @if(request('categorie'))
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 px-3 py-1 rounded-full">
                    <i class="fas fa-tag text-xs"></i> {{ request('categorie') }}
                </span>
            @endif
            @if(request('auteur'))
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full">
                    <i class="fas fa-user text-xs"></i> {{ request('auteur') }}
                </span>
            @endif
            <span class="text-slate-400">— {{ $news->total() }} résultat(s)</span>
        </div>
    @endif

    @if($news->isEmpty())
        <div class="text-center py-20 text-slate-500">
            <i class="fas fa-newspaper text-6xl mb-4 opacity-30"></i>
            <p class="text-xl font-semibold mb-2">Aucun article trouvé</p>
            <p class="text-sm">Essayez de modifier vos filtres ou revenez plus tard.</p>
        </div>
    @else
        {{-- Article à la une (le premier si is_featured) --}}
        @php $featured = $news->firstWhere('is_featured', true) ?? null; @endphp
        @if($featured && !request()->hasAny(['q', 'categorie', 'auteur']))
            <div class="mb-10 bg-white rounded-2xl shadow-md overflow-hidden flex flex-col md:flex-row">
                @if($featured->featured_image)
                    <a href="{{ route('news.show', $featured->slug) }}" class="md:w-1/2 flex-shrink-0">
                        <img src="{{ asset($featured->featured_image) }}"
                             alt="{{ $featured->title }}"
                             class="w-full h-64 md:h-full object-cover">
                    </a>
                @else
                    <div class="md:w-1/2 flex-shrink-0 h-64 md:h-auto bg-gradient-to-br from-blue-100 to-blue-300 flex items-center justify-center">
                        <i class="fas fa-newspaper text-7xl text-blue-400"></i>
                    </div>
                @endif
                <div class="p-6 md:p-10 flex flex-col justify-center">
                    <span class="inline-flex items-center gap-1 mb-3 text-xs font-bold text-white bg-red-500 px-3 py-1 rounded-full w-fit">
                        <i class="fas fa-star"></i> À la une
                    </span>
                    @if($featured->category)
                        <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider mb-2">{{ $featured->category }}</span>
                    @endif
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-3 leading-snug">
                        <a href="{{ route('news.show', $featured->slug) }}" class="hover:text-blue-600 transition">
                            {{ $featured->title }}
                        </a>
                    </h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">{{ $featured->excerpt }}</p>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-400 mb-5">
                        <span><i class="fas fa-calendar mr-1"></i>{{ $featured->published_at?->format('d F Y') ?? $featured->created_at->format('d F Y') }}</span>
                        @if($featured->author)
                            <span><i class="fas fa-user mr-1"></i>{{ $featured->author }}</span>
                        @endif
                        <span><i class="fas fa-eye mr-1"></i>{{ $featured->views }} vue(s)</span>
                    </div>
                    @if($featured->tags)
                        <div class="flex flex-wrap gap-2 mb-5">
                            @foreach($featured->tags as $tag)
                                <span class="bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded-full">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <a href="{{ route('news.show', $featured->slug) }}"
                       class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition w-fit font-semibold text-sm">
                        Lire l'article <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif

        <!-- Grille d'articles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
            @foreach($news as $article)
                @if(!$featured || $article->id !== $featured->id || request()->hasAny(['q', 'categorie', 'auteur']))
                <article class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col group">
                    @if($article->featured_image)
                        <a href="{{ route('news.show', $article->slug) }}" class="overflow-hidden">
                            <img src="{{ asset($article->featured_image) }}"
                                 alt="{{ $article->title }}"
                                 class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                        </a>
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-blue-50 to-blue-200 flex items-center justify-center">
                            <i class="fas fa-newspaper text-5xl text-blue-300"></i>
                        </div>
                    @endif

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            @if($article->is_featured)
                                <span class="text-xs font-semibold text-white bg-red-500 px-2 py-0.5 rounded-full">
                                    <i class="fas fa-star mr-1"></i>À la une
                                </span>
                            @endif
                            @if($article->category)
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">
                                    {{ $article->category }}
                                </span>
                            @endif
                        </div>

                        <h2 class="text-base font-bold text-slate-900 mb-2 leading-snug line-clamp-2">
                            <a href="{{ route('news.show', $article->slug) }}" class="hover:text-blue-600 transition">
                                {{ $article->title }}
                            </a>
                        </h2>

                        <p class="text-slate-500 text-sm flex-1 mb-3 line-clamp-3">
                            {{ $article->excerpt }}
                        </p>

                        @if($article->tags)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach(array_slice($article->tags, 0, 3) as $tag)
                                    <span class="bg-slate-100 text-slate-500 text-xs px-2 py-0.5 rounded-full">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-xs text-slate-400 mt-auto pt-3 border-t border-slate-100">
                            <span>
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $article->published_at?->format('d/m/Y') ?? $article->created_at->format('d/m/Y') }}
                            </span>
                            @if($article->author)
                                <span><i class="fas fa-user mr-1"></i>{{ $article->author }}</span>
                            @endif
                            <span><i class="fas fa-eye mr-1"></i>{{ $article->views }}</span>
                        </div>

                        <a href="{{ route('news.show', $article->slug) }}"
                           class="mt-4 inline-block text-center bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                            Lire la suite <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </article>
                @endif
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $news->links() }}
        </div>
    @endif

    <!-- Section abonnement Newsletter -->
    <div class="mt-16 bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-8 md:p-12 text-white text-center shadow-xl">
        <h2 class="text-2xl md:text-3xl font-bold mb-3">
            <i class="fas fa-envelope-open-text mr-3"></i>Restez informé(e)
        </h2>
        <p class="mb-6 text-blue-100 max-w-lg mx-auto">
            Abonnez-vous à notre newsletter pour recevoir directement les derniers Échos de la mission.
        </p>

        @if(session('newsletter_success'))
            <div class="mb-4 bg-green-500 text-white px-4 py-3 rounded-lg inline-block">
                <i class="fas fa-check-circle mr-2"></i>{{ session('newsletter_success') }}
            </div>
        @elseif(session('newsletter_info'))
            <div class="mb-4 bg-yellow-500 text-white px-4 py-3 rounded-lg inline-block">
                <i class="fas fa-info-circle mr-2"></i>{{ session('newsletter_info') }}
            </div>
        @endif

        <form action="{{ route('newsletter.subscribe') }}" method="POST"
              class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            @csrf
            <input type="text" name="name" placeholder="Votre prénom (optionnel)"
                   class="flex-1 px-4 py-3 rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-white">
            <input type="email" name="email" required placeholder="Votre adresse e-mail"
                   class="flex-1 px-4 py-3 rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-white">
            <button type="submit"
                    class="bg-white text-blue-700 font-bold px-6 py-3 rounded-lg hover:bg-blue-50 transition whitespace-nowrap">
                <i class="fas fa-paper-plane mr-2"></i>S'abonner
            </button>
        </form>
        @error('email')
            <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

</div>
@endsection
