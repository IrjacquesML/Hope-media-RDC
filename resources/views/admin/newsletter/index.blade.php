@extends('admin.layout')

@section('title', 'Newsletter - Abonnés')
@section('page_title', 'Newsletter — Liste des abonnés')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-slate-600">
        <span class="font-bold text-blue-600">{{ $subscribers->total() }}</span> abonné(s) au total
    </p>
    <span class="text-sm text-slate-500">
        <i class="fas fa-check-circle text-green-500 mr-1"></i>
        {{ $subscribers->where('is_active', true)->count() }} actif(s) sur cette page
    </span>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold">E-mail</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold hidden md:table-cell">Nom</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold">Statut</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold hidden md:table-cell">Inscrit le</th>
                <th class="text-left px-6 py-3 text-slate-600 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($subscribers as $subscriber)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $subscriber->email }}</td>
                    <td class="px-6 py-4 hidden md:table-cell text-slate-600">{{ $subscriber->name ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($subscriber->is_active)
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-semibold">Actif</span>
                        @else
                            <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded-full font-semibold">Inactif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell text-slate-500 text-xs">
                        {{ $subscriber->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.newsletter.delete', $subscriber) }}" method="POST"
                              onsubmit="return confirm('Supprimer cet abonné ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600 transition" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <i class="fas fa-users text-4xl opacity-30 mb-3 block"></i>
                        Aucun abonné pour le moment.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $subscribers->links() }}
</div>
@endsection
