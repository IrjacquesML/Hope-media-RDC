@extends('admin.layout')

@section('title', 'Paramètres du site')
@section('page_title', 'Paramètres du site')

@section('content')
@php
    $tabs = $groups + ['partners' => ['label' => 'Partenaires', 'icon' => 'fa-handshake', 'intro' => 'Partenaires affichés dans le pied de page de toutes les pages. Leur nom est cliquable et ouvre leur site.']];
    $current = $tabs[$tab];
    $input = 'w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500';
@endphp

<div class="flex flex-col lg:flex-row gap-6">
    {{-- Onglets --}}
    <nav class="lg:w-60 flex-shrink-0" aria-label="Sections des paramètres">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-2 flex lg:flex-col gap-1 overflow-x-auto">
            @foreach ($tabs as $key => $g)
                <a href="{{ route('admin.settings.index', ['onglet' => $key]) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap transition {{ $tab === $key ? 'bg-[#0B1530] text-white' : 'text-slate-700 hover:bg-slate-100' }}"
                   @if ($tab === $key) aria-current="page" @endif>
                    <i class="fas {{ $g['icon'] }} w-4 text-center {{ $tab === $key ? 'text-amber-400' : 'text-slate-400' }}"></i>{{ $g['label'] }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('admin.users.index') }}" class="mt-3 hidden lg:flex items-center gap-3 px-4 py-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
            <i class="fas fa-users-gear text-slate-400"></i> Gérer les comptes
        </a>
    </nav>

    <div class="flex-1 min-w-0 max-w-4xl">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2"><i class="fas {{ $current['icon'] }} text-blue-600"></i>{{ $current['label'] }}</h2>
            @isset($current['intro'])<p class="text-sm text-slate-600 mt-1">{{ $current['intro'] }}</p>@endisset
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-300 text-red-700 rounded-xl text-sm">
                <p class="font-semibold mb-1"><i class="fas fa-circle-exclamation mr-1"></i>Rien n’a été enregistré :</p>
                <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if ($tab !== 'partners')
            {{-- Formulaire des paramètres de l'onglet --}}
            <form method="POST" action="{{ route('admin.settings.update', $tab) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 divide-y divide-slate-100">
                @csrf
                @method('PUT')

                @foreach ($fields as $key => $f)
                    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-6">
                        <div>
                            <label for="f-{{ $key }}" class="block text-sm font-semibold text-slate-800">{{ $f['label'] }} @if (!empty($f['required']))<span class="text-red-500">*</span>@endif</label>
                            @if ($f['custom'] && $f['type'] !== 'boolean')
                                <span class="inline-block mt-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Personnalisé</span>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            @switch($f['type'])
                                @case('boolean')
                                    <label class="inline-flex items-center gap-3 cursor-pointer">
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input id="f-{{ $key }}" type="checkbox" name="{{ $key }}" value="1" class="w-5 h-5 rounded text-blue-600" @checked(old($key, $f['value']))>
                                        <span class="text-sm text-slate-700">Activé</span>
                                    </label>
                                    @break

                                @case('textarea')
                                @case('lines')
                                    <textarea id="f-{{ $key }}" name="{{ $key }}" rows="{{ $f['type'] === 'lines' ? 5 : (mb_strlen((string) $f['value']) > 300 ? 8 : 3) }}"
                                              class="{{ $input }} text-sm @error($key) border-red-500 @enderror">{{ old($key, $f['value']) }}</textarea>
                                    @break

                                @case('image')
                                    <div class="flex items-start gap-4">
                                        <div class="w-28 h-20 rounded-lg border border-slate-200 flex items-center justify-center overflow-hidden flex-shrink-0 {{ str_contains($key, 'dark') ? 'bg-[#0B1530]' : 'bg-slate-50' }}">
                                            @if ($f['value'])
                                                <img src="{{ asset($f['value']) }}" alt="" class="max-h-full max-w-full object-contain">
                                            @else
                                                <span class="text-xs text-slate-400">Aucune image</span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-2">
                                            <input id="f-{{ $key }}" type="file" name="{{ $key }}" accept="image/png,image/jpeg,image/webp"
                                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 @error($key) border-red-500 @enderror">
                                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                                @if ($f['value'])
                                                    <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" name="{{ $key }}_remove" value="1"> Retirer l’image</label>
                                                @endif
                                                @if ($f['custom'] && $f['default'])
                                                    <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" name="{{ $key }}_default" value="1"> Revenir à l’image d’origine</label>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @break

                                @default
                                    <input id="f-{{ $key }}" type="{{ in_array($f['type'], ['url', 'email', 'tel']) ? $f['type'] : 'text' }}" name="{{ $key }}"
                                           value="{{ old($key, $f['value']) }}" @if (!empty($f['required'])) required @endif placeholder="{{ $f['placeholder'] ?? ($f['type'] === 'url' ? 'https://' : '') }}"
                                           class="{{ $input }} text-sm @error($key) border-red-500 @enderror">
                            @endswitch

                            @isset($f['help'])<p class="text-xs text-slate-500 mt-1">{{ $f['help'] }}</p>@endisset
                            @if ($f['type'] !== 'boolean' && $f['type'] !== 'image' && empty($f['required']))
                                <p class="text-xs text-slate-400 mt-1">Laissez vide pour masquer cet élément sur le site.</p>
                            @endif
                            @if ($f['type'] !== 'boolean' && $f['type'] !== 'image' && $f['default'] && $f['custom'])
                                <label class="inline-flex items-start gap-1.5 text-xs text-slate-600 mt-1 cursor-pointer">
                                    <input type="checkbox" name="{{ $key }}_default" value="1" class="mt-0.5">
                                    <span>Rétablir la valeur d’origine : « {{ \Illuminate\Support\Str::limit(str_replace("\n", ' / ', $f['default']), 90) }} »</span>
                                </label>
                            @endif
                            @error($key)<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endforeach

                <div class="p-5 flex items-center justify-between gap-3 bg-slate-50 rounded-b-xl">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="text-sm text-slate-600 hover:text-blue-700"><i class="fas fa-eye mr-1"></i>Voir le résultat sur le site</a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition font-semibold">
                        <i class="fas fa-floppy-disk mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        @else
            {{-- Partenaires --}}
            <div class="space-y-4">
                <form method="POST" action="{{ route('admin.settings.partners.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    @csrf
                    <h3 class="font-bold text-slate-900 mb-3"><i class="fas fa-plus text-blue-600 mr-1"></i>Ajouter un partenaire</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1" for="p-name">Nom <span class="text-red-500">*</span></label>
                            <input id="p-name" type="text" name="name" required maxlength="150" value="{{ old('name') }}" class="{{ $input }} text-sm" placeholder="ex : Hope Channel International">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1" for="p-url">Site web</label>
                            <input id="p-url" type="url" name="url" maxlength="500" value="{{ old('url') }}" class="{{ $input }} text-sm" placeholder="https://">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1" for="p-logo">Logo <span class="text-slate-400 font-normal">(facultatif)</span></label>
                            <input id="p-logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2">
                        </div>
                        <div class="flex items-end gap-4">
                            <div class="w-28">
                                <label class="block text-sm font-semibold text-slate-700 mb-1" for="p-order">Ordre</label>
                                <input id="p-order" type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order') }}" class="{{ $input }} text-sm" placeholder="auto">
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 pb-2.5 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4"> Affiché
                            </label>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-plus mr-1"></i>Ajouter</button>
                    </div>
                </form>

                @forelse ($partners as $partner)
                    <details class="bg-white rounded-xl shadow-sm border border-slate-200 group">
                        <summary class="p-4 flex items-center gap-4 cursor-pointer list-none">
                            <span class="w-14 h-10 rounded border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                                @if ($partner->logo)<img src="{{ asset($partner->logo) }}" alt="" class="max-h-full max-w-full object-contain">@else<i class="fas fa-handshake text-slate-300"></i>@endif
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block font-semibold text-slate-900 truncate">{{ $partner->name }}</span>
                                <span class="block text-xs text-slate-500 truncate">{{ $partner->url ?: 'Pas de lien' }}</span>
                            </span>
                            <span class="text-xs text-slate-400 hidden sm:inline">Ordre {{ $partner->sort_order }}</span>
                            @if ($partner->is_active)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-800">Affiché</span>
                            @else
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Masqué</span>
                            @endif
                            <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition"></i>
                        </summary>
                        <div class="px-4 pb-4 border-t border-slate-100 pt-4">
                            <form method="POST" action="{{ route('admin.settings.partners.update', $partner) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nom</label>
                                    <input type="text" name="name" required maxlength="150" value="{{ $partner->name }}" class="{{ $input }} text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Site web</label>
                                    <input type="url" name="url" maxlength="500" value="{{ $partner->url }}" class="{{ $input }} text-sm" placeholder="https://">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nouveau logo</label>
                                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2">
                                    @if ($partner->logo)
                                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 mt-1 cursor-pointer"><input type="checkbox" name="logo_remove" value="1"> Retirer le logo</label>
                                    @endif
                                </div>
                                <div class="flex items-end gap-4">
                                    <div class="w-28">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ordre</label>
                                        <input type="number" name="sort_order" min="0" max="9999" value="{{ $partner->sort_order }}" class="{{ $input }} text-sm">
                                    </div>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 pb-2.5 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" @checked($partner->is_active) class="w-4 h-4"> Affiché
                                    </label>
                                </div>
                                <div class="md:col-span-2 flex justify-between items-center">
                                    <button type="submit" form="del-partner-{{ $partner->id }}" class="text-sm text-red-600 hover:text-red-800"><i class="fas fa-trash mr-1"></i>Supprimer</button>
                                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold text-sm"><i class="fas fa-floppy-disk mr-1"></i>Enregistrer</button>
                                </div>
                            </form>
                            <form id="del-partner-{{ $partner->id }}" method="POST" action="{{ route('admin.settings.partners.delete', $partner) }}" onsubmit="return confirm('Supprimer le partenaire « {{ addslashes($partner->name) }} » ?')">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </details>
                @empty
                    <div class="bg-white rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500">
                        <i class="fas fa-handshake text-3xl text-slate-300 mb-2"></i>
                        <p>Aucun partenaire pour l’instant. Ajoutez-en un ci-dessus : il apparaîtra dans le pied de page du site.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection
