@extends('admin.layout')

@section('title', 'Mon profil')
@section('page_title', 'Mon profil')

@section('content')
@php $input = 'w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500'; @endphp
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
        <span class="w-14 h-14 rounded-full bg-[#1F3A93] text-white flex items-center justify-center text-lg font-bold">{{ $user->initials }}</span>
        <div>
            <p class="text-lg font-bold text-slate-900">{{ $user->name }}</p>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <span class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $user->role_color }}">{{ $user->role_label }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-300 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.profile.update') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
        @csrf
        @method('PUT')
        <h2 class="font-bold text-slate-900"><i class="fas fa-user-pen text-blue-600 mr-1"></i>Informations</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nom complet</label>
                <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name', $user->name) }}" class="{{ $input }}">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">E-mail de connexion</label>
                <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $user->email) }}" class="{{ $input }}">
            </div>
        </div>
        <div class="text-right">
            <button class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-floppy-disk mr-1"></i>Enregistrer</button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.profile.password') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
        @csrf
        @method('PUT')
        <h2 class="font-bold text-slate-900"><i class="fas fa-key text-blue-600 mr-1"></i>Changer de mot de passe</h2>
        <div>
            <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe actuel</label>
            <input id="current_password" name="current_password" type="password" required class="{{ $input }} md:max-w-sm" autocomplete="current-password">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" required minlength="8" class="{{ $input }}" autocomplete="new-password">
                <p class="text-xs text-slate-500 mt-1">8 caractères minimum, avec des lettres et des chiffres.</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Confirmer</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" class="{{ $input }}" autocomplete="new-password">
            </div>
        </div>
        <div class="text-right">
            <button class="bg-slate-900 text-white px-5 py-2 rounded-lg hover:bg-black transition font-semibold"><i class="fas fa-key mr-1"></i>Changer le mot de passe</button>
        </div>
    </form>
</div>
@endsection
