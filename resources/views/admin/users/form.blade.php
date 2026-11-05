@extends('admin.layout')

@php $editing = $user->exists; @endphp
@section('title', $editing ? 'Modifier un compte' : 'Nouveau compte')
@section('page_title', $editing ? 'Modifier le compte de ' . $user->name : 'Nouveau compte')

@section('content')
@php $input = 'w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500'; $self = $editing && $user->is(auth()->user()); @endphp
<div class="max-w-3xl">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 mb-6"><i class="fas fa-arrow-left"></i> Retour aux comptes</a>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8 space-y-6" autocomplete="off">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name', $user->name) }}" class="{{ $input }}">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">E-mail de connexion <span class="text-red-500">*</span></label>
                <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $user->email) }}" class="{{ $input }}" autocomplete="off">
            </div>
        </div>

        <fieldset>
            <legend class="block text-sm font-semibold text-slate-700 mb-2">Rôle <span class="text-red-500">*</span></legend>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach ($roles as $key => $r)
                    <label class="relative flex flex-col gap-1 p-4 rounded-xl border cursor-pointer transition has-[:checked]:border-blue-500 has-[:checked]:ring-2 has-[:checked]:ring-blue-200 border-slate-200 hover:bg-slate-50 {{ $self ? 'opacity-60 cursor-not-allowed' : '' }}">
                        <span class="flex items-center gap-2">
                            <input type="radio" name="role" value="{{ $key }}" @checked(old('role', $user->role) === $key) @disabled($self) class="w-4 h-4 text-blue-600">
                            <span class="font-semibold text-slate-900">{{ $r['label'] }}</span>
                        </span>
                        <span class="text-xs text-slate-500">{{ $r['description'] }}</span>
                    </label>
                @endforeach
            </div>
            @if ($self)
                <input type="hidden" name="role" value="{{ $user->role }}">
                <p class="text-xs text-slate-500 mt-2"><i class="fas fa-lock mr-1"></i>Vous ne pouvez pas changer votre propre rôle.</p>
            @endif
        </fieldset>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">
                    {{ $editing ? 'Nouveau mot de passe' : 'Mot de passe' }} @unless ($editing)<span class="text-red-500">*</span>@endunless
                </label>
                <input id="password" name="password" type="password" @unless ($editing) required @endunless minlength="8" class="{{ $input }}" autocomplete="new-password">
                <p class="text-xs text-slate-500 mt-1">8 caractères minimum, avec des lettres et des chiffres.{{ $editing ? ' Laissez vide pour ne pas le changer.' : '' }}</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" class="{{ $input }}" autocomplete="new-password">
            </div>
        </div>

        <label class="inline-flex items-center gap-3 {{ $self ? 'opacity-60' : 'cursor-pointer' }}">
            @if ($self)<input type="hidden" name="is_active" value="1">@endif
            <input type="checkbox" name="is_active" value="1" class="w-5 h-5 rounded text-blue-600" @checked(old('is_active', $user->is_active)) @disabled($self)>
            <span class="text-sm font-semibold text-slate-700">Compte actif <span class="font-normal text-slate-500">— un compte désactivé ne peut plus se connecter</span></span>
        </label>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Annuler</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                <i class="fas fa-floppy-disk mr-2"></i>{{ $editing ? 'Enregistrer' : 'Créer le compte' }}
            </button>
        </div>
    </form>
</div>
@endsection
