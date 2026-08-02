@extends('admin.layout')

@section('title', 'Gérer les Médias')
@section('page_title', 'Tous les Médias')

@section('content')
    <p class="mb-6 text-slate-600">{{ $medias->total() }} médias au total</p>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-100 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Post Associé</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">URL</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Durée/Taille</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($medias as $media)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            @if($media->post)
                                <a href="{{ route('posts.show', $media->post->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold">
                                    {{ $media->post->title }}
                                </a>
                            @else
                                <span class="text-slate-400">Post supprimé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                {{ ucfirst($media->media_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 text-sm truncate max-w-xs">{{ $media->url }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            @if($media->duration)
                                {{ $media->formatted_duration }}
                            @elseif($media->file_size)
                                {{ $media->formatted_file_size }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.medias.delete', $media) }}" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Aucun média trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $medias->links() }}
    </div>
@endsection
