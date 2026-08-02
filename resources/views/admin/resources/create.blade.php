@extends('admin.layout')

@section('title', 'Ajouter une ressource')
@section('page_title', 'Ajouter une ressource')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('admin.resources.index') }}" class="text-blue-600 hover:underline text-sm mb-6 inline-block">
        <i class="fas fa-arrow-left mr-1"></i>Retour aux ressources
    </a>

    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.resources.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow p-8 space-y-6">
        @csrf

        <!-- Titre -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Titre <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Auteur + Éditeur -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Auteur</label>
                <input type="text" name="author" value="{{ old('author') }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Éditeur / Maison d'édition</label>
                <input type="text" name="publisher" value="{{ old('publisher') }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Catégorie + Année -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Catégorie <span class="text-red-500">*</span></label>
                <select name="category" required
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Année de publication</label>
                <input type="number" name="published_year" value="{{ old('published_year') }}"
                       min="1800" max="{{ date('Y') }}" placeholder="{{ date('Y') }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Description / Résumé</label>
            <textarea name="description" rows="4"
                      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Présentation du livre ou de l'ouvrage…">{{ old('description') }}</textarea>
        </div>

        <!-- Fichier à uploader -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Fichier (PDF, EPUB, DOCX) <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file" accept=".pdf,.epub,.docx" required
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-slate-400 mt-1">Taille maximale : 50 Mo</p>
        </div>

        <!-- Image de couverture -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Image de couverture</label>
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP — max 5 Mo</p>
        </div>

        <!-- Statut + Date publication -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Statut</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publié</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Date de publication</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- À la une -->
        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                   {{ old('is_featured') ? 'checked' : '' }}
                   class="w-4 h-4 text-blue-600 border-slate-300 rounded">
            <label for="is_featured" class="text-sm font-semibold text-slate-700">Mettre à la une</label>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Enregistrer
            </button>
            <a href="{{ route('admin.resources.index') }}"
               class="border border-slate-300 text-slate-700 px-6 py-2 rounded-lg hover:bg-slate-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
