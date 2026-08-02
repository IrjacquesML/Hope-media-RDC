@extends('admin.layout')

@section('title', 'Nouvel Écho de la mission')
@section('page_title', 'Nouvel Écho de la mission')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('admin.news.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 mb-6 transition">
        <i class="fas fa-arrow-left"></i> Retour aux articles
    </a>

    <div class="bg-white rounded-lg shadow p-6 md:p-8">
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-700 rounded-lg">
                <div class="flex items-center gap-2 font-semibold mb-2">
                    <i class="fas fa-exclamation-circle"></i>
                    L'article n'a pas pu être créé. Veuillez corriger les erreurs suivantes :
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-700 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Titre -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Titre <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Contenu -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contenu <span class="text-red-500">*</span></label>
                <textarea name="content" rows="10" required
                          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror">{{ old('content') }}</textarea>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Résumé -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-semibold text-slate-700">Résumé (extrait)</label>
                    <span id="excerpt-counter" class="text-xs text-slate-400">0 / 500</span>
                </div>
                <textarea name="excerpt" id="excerpt" rows="3" maxlength="500"
                          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Laissez vide pour générer automatiquement...">{{ old('excerpt') }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Laissez vide pour générer automatiquement depuis le contenu.</p>
                @error('excerpt')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Catégorie & Tags -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Catégorie</label>
                    <select name="category"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Aucune catégorie --</option>
                        @foreach(\App\Models\News::categories() as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tags <span class="text-slate-400 font-normal">(séparés par des virgules)</span></label>
                    <input type="text" name="tags" value="{{ old('tags') }}"
                           placeholder="ex: Congo, Évangile, Mission"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Auteur -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Auteur</label>
                    <input type="text" name="author" value="{{ old('author') }}"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Statut -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Statut <span class="text-red-500">*</span></label>
                    <select name="status" required
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publié</option>
                        <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archivé</option>
                    </select>
                </div>

                <!-- Date de publication -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Date de publication</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Image à la une -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Image à la une</label>
                    <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Article vedette -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                       class="w-4 h-4 text-blue-600" {{ old('is_featured') ? 'checked' : '' }}>
                <label for="is_featured" class="text-sm font-semibold text-slate-700">
                    Mettre à la une (article vedette)
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.news.index') }}"
                   class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>Publier l'article
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const textarea = document.getElementById('excerpt');
    const counter  = document.getElementById('excerpt-counter');
    if (!textarea || !counter) return;

    function update() {
        const len = textarea.value.length;
        counter.textContent = len + ' / 500';
        counter.classList.toggle('text-red-500', len >= 480);
        counter.classList.toggle('text-slate-400', len < 480);
    }

    textarea.addEventListener('input', update);
    update();
})();
</script>
@endpush
