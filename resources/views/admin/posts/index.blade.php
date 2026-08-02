@extends('admin.layout')

@section('title', 'Gérer les Posts')
@section('page_title', 'Tous les Posts')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <p class="text-slate-600">{{ $posts->total() }} posts au total</p>
        <a href="{{ route('admin.posts.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
            <i class="fas fa-plus mr-2"></i>
            Nouveau Post
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-100 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Catégorie</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($posts as $post)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                {{ $post->title }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $post->category->name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                @switch($post->content_type)
                                    @case('text')
                                        <i class="fas fa-file-alt text-slate-600"></i>
                                        @break
                                    @case('video')
                                        <i class="fas fa-video text-red-600"></i>
                                        @break
                                    @case('audio')
                                        <i class="fas fa-music text-purple-600"></i>
                                        @break
                                    @case('image')
                                        <i class="fas fa-image text-blue-600"></i>
                                        @break
                                @endswitch
                                {{ ucfirst($post->content_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : ($post->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 text-sm">{{ $post->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.posts.delete', $post) }}" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Aucun post trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $posts->links() }}
    </div>
    <div class="mt-6">
        <a href="{{ route('admin.posts.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">← Retour</a>
    </div>
@endsection
