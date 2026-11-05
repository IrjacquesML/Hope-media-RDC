@extends('admin.layout')

@section('title', 'Newsletter')
@section('page_title', 'Newsletter')

@section('content')
@php $isSuper = auth()->user()->isSuperAdmin(); @endphp

{{-- État de l'envoi automatique --}}
@if (! $mailConfigured)
    <div class="mb-6 p-5 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 flex items-start gap-3">
        <i class="fas fa-triangle-exclamation mt-1"></i>
        <div>
            <p class="font-semibold">Les abonnés ne reçoivent encore aucun e-mail : l’envoi n’est pas configuré.</p>
            <p class="text-sm mt-1">Créez un compte gratuit sur Brevo, puis renseignez l’identifiant et la clé SMTP dans
                @if ($isSuper)<a href="{{ route('admin.settings.index', ['onglet' => 'mail']) }}" class="underline font-semibold">Paramètres → E-mails</a>@else Paramètres → E-mails (super administrateur) @endif.
                La newsletter partira ensuite toute seule chaque semaine.</p>
        </div>
    </div>
@elseif (! $autoEnabled)
    <div class="mb-6 p-5 rounded-xl border border-slate-300 bg-white text-slate-700 flex items-start gap-3">
        <i class="fas fa-pause-circle mt-1"></i>
        <p><span class="font-semibold">L’envoi automatique est en pause.</span> Réactivez-le dans
            @if ($isSuper)<a href="{{ route('admin.settings.index', ['onglet' => 'newsletter']) }}" class="underline font-semibold">Paramètres → Newsletter</a>@else Paramètres → Newsletter @endif.</p>
    </div>
@else
    <div class="mb-6 p-5 rounded-xl border border-green-300 bg-green-50 text-green-900 flex items-start gap-3">
        <i class="fas fa-circle-check mt-1"></i>
        <div>
            <p class="font-semibold">Envoi automatique actif. Prochaine newsletter : {{ $nextSlot->translatedFormat('l d F Y à H\hi') }}.</p>
            <p class="text-sm mt-1">
                {{ $pendingItems }} contenu(x) à inclure pour l’instant, envoyé(s) à {{ $activeCount }} abonné(s) actif(s).
                S’il n’y a aucune nouveauté d’ici là, rien ne sera envoyé.
                @if ($isSuper)<a href="{{ route('admin.settings.index', ['onglet' => 'newsletter']) }}" class="underline">Changer le jour, l’heure ou les textes</a>@endif
            </p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Abonnés actifs</p><p class="text-2xl font-bold text-green-700">{{ $activeCount }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Désabonnés</p><p class="text-2xl font-bold text-slate-500">{{ $inactiveCount }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-2 justify-center">
        <a href="{{ route('admin.newsletter.preview') }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm font-semibold">
            <i class="fas fa-eye"></i> Aperçu de la prochaine newsletter
        </a>
        <form method="POST" action="{{ route('admin.newsletter.test') }}">
            @csrf
            <button class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#0B1530] text-white hover:bg-black text-sm font-semibold disabled:opacity-40" @disabled(! $mailConfigured)>
                <i class="fas fa-paper-plane"></i> M’envoyer un test ({{ auth()->user()->email }})
            </button>
        </form>
    </div>
</div>

{{-- Historique des envois --}}
<div class="bg-white rounded-xl border border-slate-200 mb-8">
    <div class="px-5 py-4 border-b border-slate-100"><h2 class="font-bold text-slate-900"><i class="fas fa-clock-rotate-left text-blue-600 mr-2"></i>Derniers envois</h2></div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-5 py-3 font-semibold">Semaine du</th>
                <th class="px-5 py-3 font-semibold">Objet</th>
                <th class="px-5 py-3 font-semibold">Contenus</th>
                <th class="px-5 py-3 font-semibold">E-mails envoyés</th>
                <th class="px-5 py-3 font-semibold">État</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($campaigns as $c)
                <tr>
                    <td class="px-5 py-3 whitespace-nowrap">{{ $c->scheduled_for->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-3">{{ $c->subject }}</td>
                    <td class="px-5 py-3">{{ count($c->items ?? []) }}</td>
                    <td class="px-5 py-3">{{ $c->sent_count }} / {{ $c->recipients_total }}@if ($c->failed_count) <span class="text-red-600">({{ $c->failed_count }} échec(s))</span>@endif</td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $c->status_color }}">{{ $c->status_label }}</span>
                        @if ($c->status === 'failed')
                            <p class="text-xs text-red-700 mt-1">{{ $c->error }}</p>
                            <form method="POST" action="{{ route('admin.newsletter.retry', $c) }}" class="mt-1">
                                @csrf
                                <button class="text-xs font-semibold text-blue-700 hover:underline"><i class="fas fa-rotate-right mr-1"></i>Relancer l’envoi</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucune newsletter envoyée pour l’instant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Abonnés --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
    <h2 class="font-bold text-slate-900 text-lg"><i class="fas fa-users text-blue-600 mr-2"></i>Abonnés</h2>
    <form method="GET" class="flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="E-mail ou nom…" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <button class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm font-semibold"><i class="fas fa-magnifying-glass"></i></button>
    </form>
</div>
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-5 py-3 font-semibold">E-mail</th>
                <th class="px-5 py-3 font-semibold hidden md:table-cell">Nom</th>
                <th class="px-5 py-3 font-semibold">Statut</th>
                <th class="px-5 py-3 font-semibold hidden md:table-cell">Inscrit le</th>
                <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($subscribers as $subscriber)
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">{{ $subscriber->email }}</td>
                    <td class="px-5 py-3 hidden md:table-cell text-slate-600">{{ $subscriber->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if ($subscriber->is_active)
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-semibold">Actif</span>
                        @else
                            <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded-full font-semibold">Désabonné</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell text-slate-500 text-xs">{{ $subscriber->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 text-right">
                        <form action="{{ route('admin.newsletter.delete', $subscriber) }}" method="POST" onsubmit="return confirm('Supprimer cet abonné ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600 transition" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Aucun abonné pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $subscribers->links() }}</div>
@endsection
