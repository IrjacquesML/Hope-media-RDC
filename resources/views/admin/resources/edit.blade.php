@extends('admin.layout')

@section('title', 'Modifier la ressource')
@section('page_title', 'Modifier la ressource')

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

    <form action="{{ route('admin.resources.update', $resource) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Titre -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Titre <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $resource->title) }}" required
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Auteur + Éditeur -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Auteur</label>
                <input type="text" name="author" value="{{ old('author', $resource->author) }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Éditeur / Maison d'édition</label>
                <input type="text" name="publisher" value="{{ old('publisher', $resource->publisher) }}"
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
                        <option value="{{ $key }}" {{ old('category', $resource->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Année de publication</label>
                <input type="number" name="published_year" value="{{ old('published_year', $resource->published_year) }}"
                       min="1800" max="{{ date('Y') }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Description / Résumé</label>
            <textarea name="description" rows="4"
                      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $resource->description) }}</textarea>
        </div>

        <!-- Fichier actuel -->
        <div class="bg-slate-50 rounded-lg p-4">
            <p class="text-sm font-semibold text-slate-700 mb-1">Fichier actuel</p>
            <p class="text-sm text-slate-600">
                <i class="fas fa-file-pdf text-red-400 mr-1"></i>
                {{ $resource->file_name }}
                <span class="text-slate-400">({{ $resource->formatted_file_size }})</span>
            </p>
        </div>

        <!-- Nouveau fichier (optionnel) -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Remplacer le fichier <span class="text-slate-400 font-normal">(optionnel)</span>
            </label>
            <input type="file" name="file" accept=".pdf,.epub,.docx"
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-slate-400 mt-1">Taille maximale : 50 Mo — laissez vide pour conserver le fichier actuel</p>
        </div>

        <!-- Couverture actuelle -->
        @if($resource->cover_image)
            <div>
                <p class="text-sm font-semibold text-slate-700 mb-2">Couverture actuelle</p>
                <img src="{{ asset($resource->cover_image) }}" alt="Couverture"
                     class="h-32 w-auto rounded-lg border border-slate-200">
            </div>
        @endif

        <!-- Nouvelle couverture -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                {{ $resource->cover_image ? 'Remplacer la couverture' : 'Image de couverture' }}
                <span class="text-slate-400 font-normal">(optionnel)</span>
            </label>
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Statut + Date publication -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Statut</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="draft" {{ old('status', $resource->status) === 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="published" {{ old('status', $resource->status) === 'published' ? 'selected' : '' }}>Publié</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Date de publication</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', $resource->published_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- À la une -->
        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                   {{ old('is_featured', $resource->is_featured) ? 'checked' : '' }}
                   class="w-4 h-4 text-blue-600 border-slate-300 rounded">
            <label for="is_featured" class="text-sm font-semibold text-slate-700">Mettre à la une</label>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('admin.resources.index') }}"
               class="border border-slate-300 text-slate-700 px-6 py-2 rounded-lg hover:bg-slate-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
