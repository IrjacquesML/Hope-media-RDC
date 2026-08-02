@extends('admin.layout')

@section('title', 'Voir le Message')
@section('page_title', 'Message de ' . $contact->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Message Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <div class="pb-6 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">{{ $contact->subject }}</h2>
                    <div class="flex flex-wrap gap-4 text-sm text-slate-600">
                        <div>
                            <span class="font-semibold">De:</span> {{ $contact->name }} ({{ $contact->email }})
                        </div>
                        <div>
                            <span class="font-semibold">Type:</span> {{ ucfirst(str_replace('_', ' ', $contact->request_type)) }}
                        </div>
                        <div>
                            <span class="font-semibold">Date:</span> {{ $contact->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <p class="text-slate-900 whitespace-pre-wrap">{{ $contact->message }}</p>
                </div>
            </div>

            <!-- Reply Form -->
            @if($contact->status !== 'replied')
                <div class="bg-white rounded-lg shadow p-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-4">Répondre</h3>
                    <form action="{{ route('admin.contacts.reply', $contact) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <textarea name="reply" rows="6" placeholder="Votre réponse..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer la réponse
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-4">Réponse Envoyée</h3>
                    <p class="text-slate-900 whitespace-pre-wrap">{{ $contact->admin_reply }}</p>
                    <p class="text-sm text-slate-600 mt-4">Envoyée le: {{ $contact->replied_at->format('d/m/Y H:i') }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div>
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Informations</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-600">Statut</p>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $contact->status === 'new' ? 'bg-blue-100 text-blue-800' : ($contact->status === 'replied' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800') }}">
                            {{ ucfirst($contact->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">Téléphone</p>
                        <p class="font-semibold text-slate-900">{{ $contact->phone ?? 'Non fourni' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">IP Address</p>
                        <p class="font-semibold text-slate-900">{{ $contact->ip_address }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">Abonnement Newsletter</p>
                        <p class="font-semibold text-slate-900">{{ $contact->subscribed_to_newsletter ? 'Oui' : 'Non' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.contacts.index') }}" class="block text-center bg-slate-300 text-slate-900 px-4 py-2 rounded-lg hover:bg-slate-400 transition font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour
                    </a>
                    <form action="{{ route('admin.contacts.archive', $contact) }}" method="POST" onsubmit="return confirm('Archiver ce message?')">
                        @csrf
                        <button type="submit" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition font-semibold">
                            <i class="fas fa-archive mr-2"></i>
                            Archiver
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
