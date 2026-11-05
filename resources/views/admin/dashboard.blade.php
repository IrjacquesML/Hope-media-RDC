@extends('admin.layout')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')

@section('content')
@php
    $me = auth()->user();
    $hour = (int) now()->format('G');
    $hello = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $statusLabels = ['new' => 'Nouveau', 'read' => 'Lu', 'replied' => 'Répondu', 'archived' => 'Archivé'];

    $cards = [
        ['Enseignements', $stats['published_posts'], $stats['draft_posts'] . ' brouillon(s)', 'fa-book-open', 'bg-blue-50 text-blue-700', route('admin.posts.index'), 'editor'],
        ['Écho de la mission', $stats['published_news'], ($stats['total_news'] - $stats['published_news']) . ' non publié(s)', 'fa-newspaper', 'bg-indigo-50 text-indigo-700', route('admin.news.index'), 'editor'],
        ['Vidéos', $stats['total_videos'], 'tous réseaux confondus', 'fa-circle-play', 'bg-red-50 text-red-600', route('admin.videos.index'), 'editor'],
        ['Vues (30 jours)', number_format($stats['views_30d'], 0, ',', ' '), 'articles et enseignements récents', 'fa-eye', 'bg-emerald-50 text-emerald-700', null, 'editor'],
        ['Messages à traiter', $stats['new_contacts'], 'messages et prières non lus', 'fa-envelope', 'bg-amber-50 text-amber-700', $me->hasRole('admin') ? route('admin.contacts.index') : null, 'admin'],
        ['Commentaires en attente', $stats['pending_comments'], 'à modérer', 'fa-comments', 'bg-purple-50 text-purple-700', $me->hasRole('admin') ? route('admin.comments.index') : null, 'admin'],
        ['Abonnés newsletter', $stats['newsletter_subscribers'], 'abonnés actifs', 'fa-envelope-open-text', 'bg-sky-50 text-sky-700', $me->hasRole('admin') ? route('admin.newsletter.index') : null, 'admin'],
        ['Ressources', $stats['total_resources'], 'livres et ouvrages', 'fa-book', 'bg-slate-100 text-slate-700', route('admin.resources.index'), 'editor'],
    ];
    $cards = array_values(array_filter($cards, fn ($c) => $me->hasRole($c[6])));

    $actions = array_filter([
        ['Nouvel enseignement', 'fa-plus', route('admin.posts.create'), 'editor'],
        ['Nouvel article', 'fa-pen-to-square', route('admin.news.create'), 'editor'],
        ['Ajouter une ressource', 'fa-book-medical', route('admin.resources.create'), 'editor'],
        ['Image du carrousel', 'fa-images', route('admin.hero-slides.create'), 'editor'],
        ['Rapport PDF', 'fa-file-pdf', route('admin.reports.index'), 'admin'],
        ['Paramètres du site', 'fa-sliders', route('admin.settings.index'), 'super_admin'],
    ], fn ($a) => $me->hasRole($a[3]));
@endphp

{{-- Accueil --}}
<div class="mb-6 rounded-2xl bg-[#0B1530] text-white p-6 md:p-8 relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-56 h-56 rounded-full bg-amber-400/10"></div>
    <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-amber-400 text-sm font-semibold">{{ now()->translatedFormat('l d F Y') }}</p>
            <h2 class="text-2xl md:text-3xl font-bold mt-1">{{ $hello }}, {{ \Illuminate\Support\Str::before($me->name, ' ') ?: $me->name }}</h2>
            <p class="text-slate-300 mt-1">Voici l’activité de {{ $site->site_name }}.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($actions as [$label, $icon, $url])
                <a href="{{ $url }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-sm font-semibold transition">
                    <i class="fas {{ $icon }} text-amber-400"></i>{{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Chiffres clés --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach ($cards as [$label, $value, $hint, $icon, $color, $url])
        @php $tag = $url ? 'a' : 'div'; @endphp
        <{{ $tag }} @if ($url) href="{{ $url }}" @endif class="bg-white rounded-xl border border-slate-200 p-5 flex items-start gap-4 {{ $url ? 'hover:shadow-md hover:border-slate-300 transition' : '' }}">
            <span class="w-12 h-12 rounded-xl flex items-center justify-center text-xl flex-shrink-0 {{ $color }}"><i class="fas {{ $icon }}"></i></span>
            <span class="min-w-0">
                <span class="block text-sm text-slate-500">{{ $label }}</span>
                <span class="block text-2xl font-bold text-slate-900">{{ $value }}</span>
                <span class="block text-xs text-slate-400 truncate">{{ $hint }}</span>
            </span>
        </{{ $tag }}>
    @endforeach
</div>

{{-- Synchronisation des réseaux --}}
@if ($me->hasRole('admin'))
    <div class="bg-white rounded-xl border border-slate-200 mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <h2 class="font-bold text-slate-900"><i class="fas fa-share-nodes text-blue-600 mr-2"></i>Synchronisation des réseaux</h2>
            <a href="{{ route('admin.social.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">Gérer →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
            @foreach ($sync as $key => $s)
                <a href="{{ route('admin.social.index') }}#reseau-{{ $key }}" class="p-4 flex items-center gap-3 hover:bg-slate-50 transition">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white flex-shrink-0" style="background: {{ $s['color'] }}"><i class="fa-brands {{ $s['icon'] }}"></i></span>
                    <span class="min-w-0">
                        <span class="block font-semibold text-slate-900">{{ $s['label'] }} <span class="text-xs font-normal text-slate-400">· {{ $s['videos'] }}</span></span>
                        @if (! $s['configured'])
                            <span class="block text-xs text-slate-400">Non configuré</span>
                        @elseif ($s['status'] === 'error')
                            <span class="block text-xs text-red-600"><i class="fas fa-circle-xmark mr-0.5"></i>Erreur {{ $s['last']?->diffForHumans() }}</span>
                        @elseif ($s['last'])
                            <span class="block text-xs {{ $s['enabled'] ? 'text-green-700' : 'text-slate-500' }}"><i class="fas fa-circle-check mr-0.5"></i>{{ $s['last']->diffForHumans() }}{{ $s['enabled'] ? ' · auto' : ' · manuel' }}</span>
                        @else
                            <span class="block text-xs text-amber-700">Prêt, jamais synchronisé</span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endif

<div class="grid grid-cols-1 {{ $me->hasRole('admin') ? 'xl:grid-cols-3' : 'lg:grid-cols-2' }} gap-6">
    {{-- Derniers enseignements --}}
    <div class="bg-white rounded-xl border border-slate-200 flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900"><i class="fas fa-book-open text-blue-600 mr-2"></i>Derniers enseignements</h2>
            <a href="{{ route('admin.posts.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">Tout voir →</a>
        </div>
        <ul class="divide-y divide-slate-100 flex-1">
            @forelse ($recent_posts as $post)
                <li class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="block font-semibold text-slate-900 hover:text-blue-600 truncate">{{ $post->title }}</a>
                        <p class="text-xs text-slate-500">{{ $post->category?->name ?? 'Sans catégorie' }} · {{ $post->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0 {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $post->status === 'published' ? 'Publié' : 'Brouillon' }}</span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-slate-500 text-sm">Aucun enseignement.</li>
            @endforelse
        </ul>
    </div>

    {{-- Derniers articles --}}
    <div class="bg-white rounded-xl border border-slate-200 flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900"><i class="fas fa-newspaper text-indigo-600 mr-2"></i>Écho de la mission</h2>
            <a href="{{ route('admin.news.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">Tout voir →</a>
        </div>
        <ul class="divide-y divide-slate-100 flex-1">
            @forelse ($recent_news as $article)
                <li class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('admin.news.edit', $article) }}" class="block font-semibold text-slate-900 hover:text-blue-600 truncate">{{ $article->title }}</a>
                        <p class="text-xs text-slate-500">{{ number_format($article->views, 0, ',', ' ') }} vue(s) · {{ $article->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0 {{ $article->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $article->status === 'published' ? 'Publié' : ($article->status === 'archived' ? 'Archivé' : 'Brouillon') }}</span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-slate-500 text-sm">Aucun article.</li>
            @endforelse
        </ul>
    </div>

    {{-- Derniers messages --}}
    @if ($me->hasRole('admin'))
        <div class="bg-white rounded-xl border border-slate-200 flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-900"><i class="fas fa-envelope text-amber-600 mr-2"></i>Derniers messages</h2>
                <a href="{{ route('admin.contacts.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">Tout voir →</a>
            </div>
            <ul class="divide-y divide-slate-100 flex-1">
                @forelse ($recent_contacts as $contact)
                    <li class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="block font-semibold text-slate-900 hover:text-blue-600 truncate">{{ $contact->name }}</a>
                            <p class="text-xs text-slate-500 truncate">{{ $contact->formatted_request_type }} · {{ $contact->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0 {{ $contact->status === 'new' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$contact->status] ?? $contact->status }}</span>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-slate-500 text-sm">Aucun message.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>
@endsection
