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
<div class="flex flex-col lg:flex-row lg:items-start gap-4 mb-6">
    <div class="flex-1 min-w-0">
        @if ($schedulerOk && ! $autoWithoutCron)
            <div class="p-4 rounded-xl border border-green-300 bg-green-50 text-green-800 flex items-start gap-3">
                <i class="fas fa-circle-check mt-1"></i>
                <div>
                    <p class="font-semibold">La synchronisation automatique fonctionne (tâche planifiée du serveur).</p>
                    <p class="text-sm">Dernier passage {{ $schedulerRunAt->diffForHumans() }}. Chaque réseau activé est synchronisé à la fréquence choisie ci-dessous.</p>
                </div>
            </div>
        @elseif ($autoWithoutCron)
            <div class="p-4 rounded-xl border border-blue-200 bg-blue-50 text-blue-900 flex items-start gap-3">
                <i class="fas fa-bolt mt-1"></i>
                <div class="min-w-0">
                    <p class="font-semibold">Synchronisation automatique active{{ $schedulerOk ? '' : ' (mode sans cron)' }}.</p>
                    <p class="text-sm">Les réseaux activés sont synchronisés à la fréquence choisie, déclenchés par les visites du site@if ($schedulerOk) ou par la tâche planifiée du serveur@endif.
                        @if ($schedulerRunAt) Dernier passage {{ $schedulerRunAt->diffForHumans() }}.@else Premier passage à la prochaine visite.@endif
                    </p>
                    <details class="text-xs mt-2">
                        <summary class="cursor-pointer font-semibold">Plus fiable : ajouter la tâche planifiée sur le serveur</summary>
                        <p class="mt-1">Si un site reçoit peu de visites, demandez à la personne qui gère le serveur d’ajouter cette ligne (<code>crontab -e</code>) :</p>
                        <code class="block mt-1 p-2 rounded bg-white border border-blue-200 break-all">{{ $cronLine }}</code>
                    </details>
                </div>
            </div>
        @else
            <div class="p-4 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 flex items-start gap-3">
                <i class="fas fa-triangle-exclamation mt-1"></i>
                <div class="min-w-0">
                    <p class="font-semibold">La synchronisation automatique n'est pas active sur ce serveur.</p>
                    <p class="text-sm">Activez « Synchroniser les réseaux sans tâche planifiée » dans
                        @if (auth()->user()->isSuperAdmin())<a class="underline font-semibold" href="{{ route('admin.settings.index', ['onglet' => 'system']) }}">Paramètres → Système</a>@else Paramètres → Système (super administrateur)@endif,
                        ou ajoutez cette ligne au planificateur du serveur (<code>crontab -e</code>) :</p>
                    <code class="block mt-2 p-2 rounded bg-white border border-amber-200 text-xs break-all">{{ $cronLine }}</code>
                    @if ($schedulerRunAt)<p class="text-xs mt-2">Dernier passage connu : {{ $schedulerRunAt->diffForHumans() }}.</p>@endif
                </div>
            </div>
        @endif
    </div>
    <form method="POST" action="{{ route('admin.social.sync-all') }}" class="flex-shrink-0">
        @csrf
        <button type="submit" class="w-full lg:w-auto bg-slate-900 text-white px-5 py-3 rounded-xl hover:bg-black transition font-semibold disabled:opacity-40 disabled:cursor-not-allowed" @disabled($configuredCount === 0)
                onclick="this.disabled=true;this.textContent='Synchronisation en cours…';this.form.submit();">
            <i class="fas fa-rotate mr-2"></i>Tout synchroniser maintenant
        </button>
        <p class="text-xs text-slate-500 mt-1 text-center">{{ $configuredCount }} réseau(x) configuré(s)</p>
    </form>
</div>

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

                @if ($key === 'tiktok')
                    <div class="text-sm p-3 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                        <p class="font-semibold text-slate-800"><i class="fa-brands fa-tiktok mr-1"></i>Connexion du compte en un clic</p>
                        <p class="text-slate-600">1. Enregistrez le <strong>Client Key</strong> et le <strong>Client Secret</strong>. 2. Dans votre app TikTok (Login Kit), ajoutez cette adresse de redirection :</p>
                        <code class="block p-2 rounded bg-white border border-slate-200 text-xs break-all select-all">{{ $tiktokRedirect }}</code>
                        <p class="text-slate-600">3. Cliquez ci-dessous et connectez-vous avec le compte TikTok de Hope Media : les jetons sont enregistrés tout seuls.</p>
                        <a href="{{ route('admin.social.tiktok.connect') }}" class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition font-semibold">
                            <i class="fa-brands fa-tiktok"></i> {{ $p['model']->storedCredential('access_token') ? 'Reconnecter le compte TikTok' : 'Connecter le compte TikTok' }}
                        </a>
                        @if (! str_starts_with($tiktokRedirect, 'https://'))
                            <p class="text-xs text-amber-700"><i class="fas fa-triangle-exclamation mr-1"></i>TikTok exige une adresse en https : cette étape fonctionne sur le site en ligne, pas en local.</p>
                        @endif
                    </div>
                @endif
                @if ($key === 'instagram')
                    <p class="text-xs text-slate-500"><i class="fas fa-rotate mr-1"></i>Les jetons Instagram (IGAA…) sont prolongés automatiquement chaque semaine : plus besoin de les renouveler tous les 60 jours, tant que la synchronisation tourne.</p>
                @endif

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
