@extends('admin.layout')

@section('title', 'Carrousel Accueil')
@section('page_title', 'Carrousel — Page d\'accueil')

@section('content')

<div class="grid grid-cols-2 gap-4 mb-8 max-w-md">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-images text-blue-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500">Total images</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-eye text-green-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
            <p class="text-xs text-slate-500">Actives</p>
        </div>
    </div>
</div>

<div class="flex justify-between items-center mb-6">
    <p class="text-slate-600 text-sm">Ces images s'affichent en fond du bandeau d'accueil.</p>
    <a href="{{ route('admin.hero-slides.create') }}"
       class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 text-sm whitespace-nowrap">
        <i class="fas fa-plus"></i> Ajouter une image
    </a>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold w-24">Aperçu</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold">Titre</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold hidden md:table-cell">Ordre</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold">Statut</th>
                <th class="text-left px-4 py-3 text-slate-600 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($slides as $slide)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <img src="{{ asset($slide->image) }}"
                             alt="{{ $slide->title ?? 'Slide' }}"
                             class="w-16 h-12 rounded-lg object-cover border border-slate-200">
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">
                            {{ $slide->title ?: 'Sans titre' }}
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">{{ $slide->image }}</div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600 font-mono">
                        {{ $slide->sort_order }}
                    </td>
                    <td class="px-4 py-3">
                        @if($slide->is_active)
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-semibold">Active</span>
                        @else
                            <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded-full font-semibold">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.hero-slides.edit', $slide) }}"
                               class="text-slate-500 hover:text-yellow-600 transition" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.hero-slides.delete', $slide) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette image du carrousel ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-500 hover:text-red-600 transition" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <i class="fas fa-images text-4xl opacity-30 mb-3 block"></i>
                        Aucune image dans le carrousel.
                        <a href="{{ route('admin.hero-slides.create') }}" class="text-blue-600 hover:underline ml-1">Ajouter la première</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($slides->hasPages())
    <div class="mt-6">{{ $slides->links() }}</div>
@endif

@endsection
