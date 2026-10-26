@extends('admin.layout')

@section('title', 'Vidéos & Sync')
@section('page_title', 'Contenus réseaux sociaux')

@section('content')
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        @foreach(['youtube' => 'YouTube', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'x' => 'X'] as $key => $label)
            <div class="bg-white rounded-lg shadow p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900">{{ $label }}</h3>
                    <span class="text-sm bg-slate-100 text-slate-700 px-2 py-1 rounded-full">{{ $counts[$key] ?? 0 }}</span>
                </div>
                <form action="{{ route('admin.videos.sync', $key) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                        <i class="fas fa-sync-alt mr-1"></i> Synchroniser
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.videos.index') }}"
           class="px-3 py-1.5 rounded-full text-sm font-semibold {{ !request('platform') ? 'bg-slate-800 text-white' : 'bg-white border text-slate-600' }}">
            Tout
        </a>
        @foreach(['youtube','facebook','instagram','tiktok','x'] as $p)
            <a href="{{ route('admin.videos.index', ['platform' => $p]) }}"
               class="px-3 py-1.5 rounded-full text-sm font-semibold {{ request('platform') === $p ? 'bg-blue-600 text-white' : 'bg-white border text-slate-600' }}">
                {{ ucfirst($p) }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Miniature</th>
                    <th class="px-4 py-3 text-left">Titre</th>
                    <th class="px-4 py-3 text-left">Plateforme</th>
                    <th class="px-4 py-3 text-left">Publié</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($videos as $video)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            @if($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}" alt="" class="w-20 h-12 object-cover rounded">
                            @else
                                <div class="w-20 h-12 bg-slate-200 rounded"></div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-900 max-w-xs truncate">{{ $video->title }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs bg-slate-100">{{ ucfirst($video->platform->value ?? $video->platform) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $video->published_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($video->post_url)
                                    <a href="{{ $video->post_url }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="Ouvrir">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                                <form action="{{ route('admin.videos.delete', $video) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce contenu ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                            Aucun contenu synchronisé. Utilisez les boutons ci-dessus.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $videos->links() }}</div>
@endsection
