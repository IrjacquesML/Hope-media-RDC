@extends('admin.layout')

@section('title', 'Gérer les Contacts')
@section('page_title', 'Tous les Messages')

@section('content')
    <p class="mb-6 text-slate-600">{{ $contacts->total() }} messages au total</p>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-100 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Nom</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($contacts as $contact)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $contact->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $contact->email }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                {{ ucfirst(str_replace('_', ' ', $contact->request_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $contact->status === 'new' ? 'bg-blue-100 text-blue-800' : ($contact->status === 'replied' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800') }}">
                                {{ ucfirst($contact->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 text-sm">{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Aucun message trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $contacts->links() }}
    </div>
@endsection
