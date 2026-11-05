@extends('admin.layout')

@section('title', 'Rapports PDF')
@section('page_title', 'Rapports d\'activités (PDF)')

@section('content')
@php
    $today = now()->format('Y-m-d');
    $presets = [
        'Ce mois'          => [now()->startOfMonth(), now()],
        'Mois précédent'   => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
        'Ce trimestre'     => [now()->firstOfQuarter(), now()],
        'Trimestre préc.'  => [now()->subQuarterNoOverflow()->firstOfQuarter(), now()->subQuarterNoOverflow()->lastOfQuarter()],
        'Cette année'      => [now()->startOfYear(), now()],
        'Année précédente' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
    ];
    $oldSections = old('sections', array_keys($sections));
@endphp
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6 md:p-8">
        <p class="text-slate-600 mb-6">
            Générez un rapport complet de l'activité de Hope Media Network RDC sur la période de votre choix.
            Le PDF comporte l'en-tête avec le logo, une synthèse chiffrée, le détail de chaque section, vos observations et le bloc de signature.
        </p>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-700 rounded-lg">
                <div class="flex items-center gap-2 font-semibold mb-2"><i class="fas fa-exclamation-circle"></i> Le rapport n'a pas pu être généré :</div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.reports.generate') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Période -->
            <fieldset class="space-y-3">
                <legend class="text-base font-bold text-slate-800 mb-2"><i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Période</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach($presets as $label => [$f, $t])
                        <button type="button" data-from="{{ $f->format('Y-m-d') }}" data-to="{{ $t->format('Y-m-d') }}"
                                class="report-preset text-xs px-3 py-1.5 rounded-full border border-slate-300 text-slate-700 hover:bg-blue-50 hover:border-blue-400 transition">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-semibold text-slate-700 mb-1">Du <span class="text-red-500">*</span></label>
                        <input type="date" name="date_from" id="date_from" required max="{{ $today }}"
                               value="{{ old('date_from', now()->startOfMonth()->format('Y-m-d')) }}"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_from') border-red-500 @enderror">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-semibold text-slate-700 mb-1">Au <span class="text-red-500">*</span></label>
                        <input type="date" name="date_to" id="date_to" required
                               value="{{ old('date_to', $today) }}"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_to') border-red-500 @enderror">
                    </div>
                </div>
            </fieldset>

            <!-- Contenu -->
            <fieldset class="space-y-3">
                <legend class="text-base font-bold text-slate-800 mb-2"><i class="fas fa-list-check text-blue-600 mr-2"></i>Contenu du rapport</legend>
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Titre du rapport</label>
                    <input type="text" name="title" id="title" maxlength="150" value="{{ old('title') }}"
                           placeholder="Rapport d'activités"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">Sections à inclure <span class="text-red-500">*</span></span>
                        <button type="button" id="toggleSections" class="text-xs text-blue-600 hover:underline">Tout cocher / décocher</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($sections as $key => $label)
                            <label class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="sections[]" value="{{ $key }}" class="report-section w-4 h-4 text-blue-600"
                                       {{ in_array($key, $oldSections) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mt-1">La synthèse, les observations et la signature sont toujours incluses.</p>
                </div>
                <div>
                    <label for="observations" class="block text-sm font-semibold text-slate-700 mb-1">Observations et recommandations</label>
                    <textarea name="observations" id="observations" rows="5" maxlength="5000"
                              placeholder="Faits marquants, difficultés rencontrées, perspectives pour la période suivante…"
                              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('observations') }}</textarea>
                </div>
            </fieldset>

            <!-- Signataire -->
            <fieldset class="space-y-3">
                <legend class="text-base font-bold text-slate-800 mb-2"><i class="fas fa-signature text-blue-600 mr-2"></i>Signataire</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="signatory_name" class="block text-sm font-semibold text-slate-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                        <input type="text" name="signatory_name" id="signatory_name" required maxlength="120"
                               value="{{ old('signatory_name', $signatory['name'] ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('signatory_name') border-red-500 @enderror">
                    </div>
                    <div>
                        <label for="signatory_role" class="block text-sm font-semibold text-slate-700 mb-1">Fonction <span class="text-red-500">*</span></label>
                        <input type="text" name="signatory_role" id="signatory_role" required maxlength="120"
                               value="{{ old('signatory_role', $signatory['role'] ?? '') }}" placeholder="ex : Directeur de Hope Media"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('signatory_role') border-red-500 @enderror">
                    </div>
                    <div>
                        <label for="signatory_place" class="block text-sm font-semibold text-slate-700 mb-1">Lieu</label>
                        <input type="text" name="signatory_place" id="signatory_place" maxlength="80"
                               value="{{ old('signatory_place', $signatory['place'] ?? 'Kinshasa') }}"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label for="signature" class="block text-sm font-semibold text-slate-700 mb-1">Signature scannée <span class="text-slate-400 font-normal">(facultatif — PNG ou JPG, 1 Mo max, non conservée)</span></label>
                    <input type="file" name="signature" id="signature" accept="image/png,image/jpeg"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('signature') border-red-500 @enderror">
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="submit" name="output" value="preview" formtarget="_blank"
                        class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition font-semibold">
                    <i class="fas fa-eye mr-2"></i>Aperçu
                </button>
                <button type="submit" name="output" value="download"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-file-pdf mr-2"></i>Télécharger le PDF
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const from = document.getElementById('date_from');
    const to = document.getElementById('date_to');
    document.querySelectorAll('.report-preset').forEach(btn => btn.addEventListener('click', () => {
        from.value = btn.dataset.from;
        to.value = btn.dataset.to;
    }));
    const sync = () => { to.min = from.value; };
    from.addEventListener('change', sync); sync();

    document.getElementById('toggleSections')?.addEventListener('click', () => {
        const boxes = [...document.querySelectorAll('.report-section')];
        const all = boxes.every(b => b.checked);
        boxes.forEach(b => { b.checked = !all; });
    });
})();
</script>
@endpush
