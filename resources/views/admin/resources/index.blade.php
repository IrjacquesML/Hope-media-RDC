@extends('admin.layout')

@section('title', 'Ressources')
@section('page_title', 'Gestion des Ressources')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-slate-600">{{ $resources->total() }} ressource(s) au total</p>
    <a href="{{ route('admin.resources.create') }}"
       class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2">
        <i class="fas fa-plus"></i> Ajouter une ressource
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold">Titre</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold hidden md:table-cell">Catégorie</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold hidden md:table-cell">Fichier</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold">Statut</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold hidden lg:table-cell">Téléchgts</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($resources as $resource)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 max-w-xs truncate">{{ $resource->title }}</div>
                        @if($resource->author)
                            <div class="text-xs text-slate-400">{{ $resource->author }}</div>
                        @endif
                        @if($resource->is_featured)
                            <span class="text-xs text-yellow-600 font-medium">
                                <i class="fas fa-star mr-1"></i>À la une
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell text-slate-600">{{ $resource->category_label }}</td>
                    <td class="px-6 py-4 hidden md:table-cell text-slate-500 text-xs">
                        <span class="uppercase font-mono bg-slate-100 px-2 py-0.5 rounded">{{ $resource->file_type }}</span>
                        · {{ $resource->formatted_file_size }}
                    </td>
                    <td class="px-6 py-4">
                        @if($resource->status === 'published')
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-semibold">Publié</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-semibold">Brouillon</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell text-slate-600 font-semibold">
                        {{ number_format($resource->downloads) }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('ressources.show', $resource->slug) }}" target="_blank"
                               class="text-slate-500 hover:text-blue-600 transition" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.resources.edit', $resource) }}"
                               class="text-slate-500 hover:text-yellow-600 transition" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.resources.delete', $resource) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette ressource ?')">
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
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <i class="fas fa-book text-4xl opacity-30 mb-3 block"></i>
                        Aucune ressource. <a href="{{ route('admin.resources.create') }}" class="text-blue-600 hover:underline">Ajouter la première</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($resources->hasPages())
    <div class="mt-4">
        {{ $resources->links() }}
    </div>
@endif
@endsection
