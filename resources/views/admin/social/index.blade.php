@extends('admin.layout')

@section('title', 'Réseaux sociaux')
@section('page_title', 'Réseaux sociaux')

@section('content')
@php
    $intervals = [1 => 'Toutes les heures', 2 => 'Toutes les 2 heures', 3 => 'Toutes les 3 heures', 6 => 'Toutes les 6 heures', 12 => 'Toutes les 12 heures', 24 => 'Une fois par jour'];
    $schedulerOk = $schedulerRunAt && $schedulerRunAt->gt(now()->subMinutes(30));
    $cronLine = '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1';
@endphp

{{-- État de la synchronisation automatique --}}
@if ($schedulerOk)
    <div class="mb-6 p-4 rounded-lg border border-green-300 bg-green-50 text-green-800 flex items-start gap-3">
        <i class="fas fa-circle-check mt-1"></i>
        <div>
            <p class="font-semibold">La synchronisation automatique fonctionne.</p>
            <p class="text-sm">Dernier passage du planificateur {{ $schedulerRunAt->diffForHumans() }}. Chaque réseau activé est synchronisé à la fréquence choisie ci-dessous.</p>
        </div>
    </div>
@else
    <div class="mb-6 p-4 rounded-lg border border-amber-300 bg-amber-50 text-amber-900 flex items-start gap-3">
        <i class="fas fa-triangle-exclamation mt-1"></i>
        <div class="min-w-0">
            <p class="font-semibold">La synchronisation automatique n'est pas encore active sur ce serveur.</p>
            <p class="text-sm">Vous pouvez synchroniser à la main avec les boutons « Synchroniser maintenant ». Pour l'automatique, la personne qui gère le serveur doit ajouter une fois cette ligne au planificateur (<code>crontab -e</code>) :</p>
            <code class="block mt-2 p-2 rounded bg-white border border-amber-200 text-xs break-all">{{ $cronLine }}</code>
            @if ($schedulerRunAt)<p class="text-xs mt-2">Dernier passage connu : {{ $schedulerRunAt->diffForHumans() }}.</p>@endif
        </div>
    </div>
@endif

<p class="mb-6 text-slate-600 text-sm max-w-3xl">
    Saisissez ici les accès de chaque réseau. Ils sont <strong>chiffrés</strong> dans la base de données et ne sont jamais réaffichés.
    Utilisez « Tester la connexion » pour vérifier vos accès sans rien importer, puis « Synchroniser maintenant ».
</p>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    @foreach ($platforms as $key => $p)
        @php
            $m = $p['model'];
            $scoped = old('_platform') === $key;
        @endphp
        <section id="reseau-{{ $key }}" class="bg-white rounded-xl shadow border border-slate-100 overflow-hidden flex flex-col">
            {{-- En-tête --}}
            <div class="p-5 border-b border-slate-100 flex items-start gap-4">
                <span class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-2xl flex-shrink-0" style="background: {{ $p['color'] }}">
                    <i class="fa-brands {{ $p['icon'] }}"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">{{ $p['label'] }}</h2>
                        @if ($m->enabled)
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-800">Automatique activée</span>
                        @else
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Automatique désactivée</span>
                        @endif
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">{{ $p['videoCount'] }} vidéo(s) sur le site</span>
                    </div>
                    <p class="text-sm mt-1">
                        @if (! $m->last_synced_at)
                            <span class="text-slate-500">Jamais synchronisé.</span>
                        @elseif ($m->last_status === 'ok')
                            <span class="text-green-700"><i class="fas fa-check mr-1"></i>Dernière synchronisation {{ $m->last_synced_at->diffForHumans() }} : {{ $m->last_count }} vidéo(s).</span>
                        @else
                            <span class="text-red-700"><i class="fas fa-xmark mr-1"></i>Échec {{ $m->last_synced_at->diffForHumans() }} :</span>
                            <span class="block text-red-700 text-xs mt-1 break-words">{{ $m->last_message }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="p-5 flex flex-col gap-4 flex-1">
                @if ($p['missing'])
                    <p class="text-sm p-3 rounded-lg bg-slate-50 border border-slate-200 text-slate-700">
                        <i class="fas fa-circle-info mr-1 text-blue-600"></i>À compléter : {{ implode(', ', $p['missing']) }}.
                    </p>
                @endif
                @isset($p['note'])
                    <p class="text-sm p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900"><i class="fas fa-lightbulb mr-1"></i>{{ $p['note'] }}</p>
                @endisset

                {{-- Réglages --}}
                <form method="POST" action="{{ route('admin.social.update', $key) }}" class="flex flex-col gap-4" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_platform" value="{{ $key }}">

                    @foreach ($p['fields'] as $field => $f)
                        @php $error = $scoped ? $errors->first("credentials.{$field}") : null; @endphp
                        <div>
                            <label for="{{ $key }}-{{ $field }}" class="block text-sm font-semibold text-slate-700 mb-1">
                                {{ $f['label'] }} @if ($f['required'])<span class="text-red-500">*</span>@endif
                            </label>
                            <input id="{{ $key }}-{{ $field }}" name="credentials[{{ $field }}]"
                                   type="{{ $f['secret'] ? 'password' : 'text' }}"
                                   value="{{ $f['secret'] ? '' : ($scoped ? old("credentials.{$field}", $f['value']) : $f['value']) }}"
                                   placeholder="{{ $f['secret'] && $f['source'] === 'admin' ? '•••••••• enregistré — laisser vide pour conserver' : ($f['placeholder'] ?? '') }}"
                                   autocomplete="off" spellcheck="false"
                                   class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 @if ($error) border-red-500 @endif">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-500">
                                @if ($f['source'] === 'admin')
                                    <span class="text-green-700"><i class="fas fa-lock mr-1"></i>Enregistré</span>
                                    <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" name="forget[{{ $field }}]" value="1"> Effacer</label>
                                @elseif ($f['source'] === 'env')
                                    <span><i class="fas fa-file-code mr-1"></i>Valeur actuelle lue dans le fichier .env du serveur</span>
                                @endif
                                @isset($f['help'])<span>{{ $f['help'] }}</span>@endisset
                            </div>
                            @if ($error)<p class="text-red-500 text-xs mt-1">{{ $error }}</p>@endif
                        </div>
                    @endforeach

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="{{ $key }}-max" class="block text-sm font-semibold text-slate-700 mb-1">Vidéos récupérées à chaque fois</label>
                            <input id="{{ $key }}-max" name="max_results" type="number" min="1" max="{{ $p['max_limit'] }}"
                                   value="{{ $scoped ? old('max_results', $m->max_results) : $m->max_results }}"
                                   class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Maximum {{ $p['max_limit'] }}.</p>
                            @if ($scoped && $errors->has('max_results'))<p class="text-red-500 text-xs mt-1">{{ $errors->first('max_results') }}</p>@endif
                        </div>
                        <div>
                            <label for="{{ $key }}-interval" class="block text-sm font-semibold text-slate-700 mb-1">Fréquence automatique</label>
                            <select id="{{ $key }}-interval" name="interval_hours" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($intervals as $h => $label)
                                    <option value="{{ $h }}" @selected((int) ($scoped ? old('interval_hours', $m->interval_hours) : $m->interval_hours) === $h)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="enabled" value="1" @checked($scoped ? old('enabled') : $m->enabled)>
                        Synchroniser automatiquement {{ $p['label'] }}
                    </label>

                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-semibold self-start">
                        <i class="fas fa-floppy-disk mr-1"></i> Enregistrer
                    </button>
                </form>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-100">
                    <form method="POST" action="{{ route('admin.social.test', $key) }}">
                        @csrf
                        <button type="submit" class="bg-slate-100 text-slate-800 px-4 py-2 rounded-lg hover:bg-slate-200 transition font-semibold text-sm disabled:opacity-40 disabled:cursor-not-allowed" @disabled($p['missing'])>
                            <i class="fas fa-plug mr-1"></i> Tester la connexion
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.social.sync', $key) }}">
                        @csrf
                        <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-900 transition font-semibold text-sm disabled:opacity-40 disabled:cursor-not-allowed" @disabled($p['missing'])>
                            <i class="fas fa-rotate mr-1"></i> Synchroniser maintenant
                        </button>
                    </form>
                </div>

                {{-- Guide --}}
                <details class="text-sm text-slate-700 rounded-lg bg-slate-50 border border-slate-200 p-3">
                    <summary class="cursor-pointer font-semibold"><i class="fas fa-book-open mr-1 text-blue-600"></i>Comment obtenir les accès {{ $p['label'] }} ?</summary>
                    <ol class="list-decimal pl-5 mt-3 space-y-1">
                        @foreach ($p['guide'] as $step)<li>{{ $step }}</li>@endforeach
                    </ol>
                    <a href="{{ $p['docs'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 mt-3 text-blue-700 font-semibold hover:underline">
                        Ouvrir la console {{ $p['label'] }} <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </details>
            </div>
        </section>
    @endforeach
</div>
@endsection
