@extends('admin.layout')

@section('title', 'Comptes')
@section('page_title', 'Comptes d’administration')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Comptes</p><p class="text-2xl font-bold">{{ $stats['total'] }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Actifs</p><p class="text-2xl font-bold text-green-700">{{ $stats['active'] }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Désactivés</p><p class="text-2xl font-bold text-slate-500">{{ $stats['inactive'] }}</p></div>
</div>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom ou e-mail…" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <select name="role" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Tous les rôles</option>
            @foreach ($roles as $key => $r)<option value="{{ $key }}" @selected(request('role') === $key)>{{ $r['label'] }}</option>@endforeach
        </select>
        <button class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm font-semibold"><i class="fas fa-filter mr-1"></i>Filtrer</button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 transition font-semibold">
        <i class="fas fa-user-plus"></i> Nouveau compte
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-4 py-3 font-semibold">Compte</th>
                <th class="px-4 py-3 font-semibold">Rôle</th>
                <th class="px-4 py-3 font-semibold">État</th>
                <th class="px-4 py-3 font-semibold hidden md:table-cell">Dernière connexion</th>
                <th class="px-4 py-3 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
                <tr class="{{ $user->is_active ? '' : 'opacity-60' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-[#1F3A93] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $user->initials }}</span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs font-normal text-slate-400">(vous)</span>@endif</p>
                                <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ $user->role_color }}">{{ $user->role_label }}</span></td>
                    <td class="px-4 py-3">
                        @if ($user->is_active)
                            <span class="text-xs font-semibold text-green-700"><i class="fas fa-circle text-[8px] mr-1"></i>Actif</span>
                        @else
                            <span class="text-xs font-semibold text-slate-500"><i class="fas fa-ban mr-1"></i>Désactivé</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-500">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-blue-700 hover:bg-blue-50 font-semibold"><i class="fas fa-pen"></i><span class="hidden sm:inline">Modifier</span></a>
                        @unless ($user->is(auth()->user()))
                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="inline" onsubmit="return confirm('Supprimer définitivement le compte de {{ addslashes($user->name) }} ?')">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50 font-semibold"><i class="fas fa-trash"></i><span class="hidden sm:inline">Supprimer</span></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Aucun compte trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach ($roles as $r)
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $r['color'] }}">{{ $r['label'] }}</span>
            <p class="text-sm text-slate-600 mt-2">{{ $r['description'] }}</p>
        </div>
    @endforeach
</div>
@endsection
