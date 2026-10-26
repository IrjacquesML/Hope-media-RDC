@extends('admin.layout')

@section('title', 'Écho de la mission')
@section('page_title', 'Gestion — Écho de la mission')

@section('content')

{{-- Stats cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-newspaper text-blue-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500">Total articles</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check-circle text-green-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['published'] }}</p>
            <p class="text-xs text-slate-500">Publiés</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['draft'] }}</p>
            <p class="text-xs text-slate-500">Brouillons</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-eye text-purple-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['views']) }}</p>
            <p class="text-xs text-slate-500">Vues totales</p>
        </div>
    </div>
</div>

{{-- Barre d'actions --}}
<div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
    {{-- Recherche + filtre statut --}}
    <form method="GET" action="{{ route('admin.news.index') }}" class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full md:w-auto">
        <div class="relative">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Rechercher un article..."
                   class="border border-slate-300 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        </div>
        <select name="statut"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Tous les statuts</option>
            <option value="published" {{ request('statut') === 'published' ? 'selected' : '' }}>Publiés</option>
            <option value="draft"     {{ request('statut') === 'draft'     ? 'selected' : '' }}>Brouillons</option>
            <option value="archived"  {{ request('statut') === 'archived'  ? 'selected' : '' }}>Archivés</option>
        </select>
        <button type="submit"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition text-sm">
            <i class="fas fa-filter mr-1"></i>Filtrer
        </button>
        @if(request()->hasAny(['q', 'statut']))
            <a href="{{ route('admin.news.index') }}"
               class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg hover:bg-slate-50 transition text-sm">
                <i class="fas fa-times mr-1"></i>Réinitialiser
            </a>
        @endif
    </form>

    <a href="{{ route('admin.news.create') }}"
       class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold flex items-center justify-center gap-2 text-sm whitespace-nowrap">
        <i class="fas fa-plus"></i> Nouvel article
    </a>
</div>

{{-- Résultats filtrés --}}
@if(request()->hasAny(['q', 'statut']))
    <p class="text-sm text-slate-500 mb-4">
        <i class="fas fa-info-circle mr-1 text-blue-500"></i>
        {{ $news->total() }} résultat(s) trouvé(s)
    </p>
@endif

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold w-10 hidden sm:table-cell"></th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold">Titre</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold hidden lg:table-cell">Catégorie</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold hidden md:table-cell">Auteur</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold">Statut</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold hidden md:table-cell">Date</th>
                <th class="text-right px-4 py-3 text-slate-600 font-semibold hidden lg:table-cell">Vues</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($news as $article)
                <tr class="hover:bg-slate-50 transition">
                    {{-- Miniature --}}
                    <td class="px-4 py-3 hidden sm:table-cell">
                        @if($article->featured_image)
                            <img src="{{ asset($article->featured_image) }}"
                                 alt=""
                                 class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-newspaper text-blue-300 text-sm"></i>
                            </div>
                        @endif
                    </td>
                    {{-- Titre --}}
                    <td class="px-4 py-3 max-w-xs">
                        <div class="font-semibold text-slate-900 truncate">{{ $article->title }}</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            @if($article->is_featured)
                                <span class="text-xs text-red-600 font-medium">
                                    <i class="fas fa-star mr-0.5"></i>À la une
                                </span>
                            @endif
                            @if($article->tags)
                                <span class="text-xs text-slate-400">{{ count($article->tags) }} tag(s)</span>
                            @endif
                        </div>
                    </td>
                    {{-- Catégorie --}}
                    <td class="px-4 py-3 hidden lg:table-cell">
                        @if($article->category)
                            <span class="bg-purple-50 text-purple-700 text-xs px-2 py-1 rounded-full font-semibold">{{ $article->category }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    {{-- Auteur --}}
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600 text-xs">{{ $article->author ?? '—' }}</td>
                    {{-- Statut --}}
                    <td class="px-4 py-3">
                        @if($article->status === 'published')
                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Publié
                            </span>
                        @elseif($article->status === 'draft')
                            <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>Brouillon
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded-full font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Archivé
                            </span>
                        @endif
                    </td>
                    {{-- Date --}}
                    <td class="px-4 py-3 hidden md:table-cell text-slate-400 text-xs">
                        {{ $article->published_at?->format('d/m/Y') ?? $article->created_at->format('d/m/Y') }}
                    </td>
                    {{-- Vues --}}
                    <td class="px-4 py-3 hidden lg:table-cell text-right text-slate-500 text-xs font-medium">
                        <i class="fas fa-eye mr-1 text-slate-300"></i>{{ number_format($article->views) }}
                    </td>
                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('news.show', $article->slug) }}" target="_blank"
                               class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-blue-100 hover:text-blue-600 flex items-center justify-center transition"
                               title="Voir sur le site">
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                            <a href="{{ route('admin.news.edit', $article) }}"
                               class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-yellow-100 hover:text-yellow-700 flex items-center justify-center transition"
                               title="Modifier">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('admin.news.delete', $article) }}" method="POST"
                                  onsubmit="return confirm('Supprimer « {{ addslashes($article->title) }} » ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition"
                                        title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center text-slate-400">
                        <i class="fas fa-newspaper text-5xl opacity-20 mb-4 block"></i>
                        <p class="text-base font-semibold mb-1">Aucun article trouvé</p>
                        <p class="text-sm mb-4">Commencez par créer votre premier article.</p>
                        <a href="{{ route('admin.news.create') }}"
                           class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                            <i class="fas fa-plus"></i> Créer un article
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5 flex items-center justify-between">
    <p class="text-sm text-slate-500">
        {{ $news->firstItem() ?? 0 }}–{{ $news->lastItem() ?? 0 }} sur {{ $news->total() }} articles
    </p>
    {{ $news->links() }}
</div>

@endsection
