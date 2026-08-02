@extends('admin.layout')

@section('title', 'Éditer Catégorie')
@section('page_title', 'Éditer: ' . $category->name)

@section('content')
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nom -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Nom de la catégorie</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Type</label>
                <select name="type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="enseignement" {{ old('type', $category->type) == 'enseignement' ? 'selected' : '' }}>Enseignement</option>
                    <option value="service" {{ old('type', $category->type) == 'service' ? 'selected' : '' }}>Service</option>
                </select>
                @error('type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Icône -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Icône (Font Awesome)</label>
                <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="Ex: fas fa-video" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('icon')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Couleur -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Couleur</label>
                <input type="color" name="color" value="{{ old('color', $category->color) }}" class="h-10 w-20 border border-slate-300 rounded-lg cursor-pointer">
                @error('color')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actif -->
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="w-4 h-4">
                    <span class="text-sm font-semibold text-slate-900">Catégorie active</span>
                </label>
            </div>

            <!-- Boutons -->
            <div class="flex gap-4 pt-6 border-t border-slate-200">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>
                    Mettre à jour
                </button>
                <a href="{{ route('admin.categories.index') }}" class="bg-slate-300 text-slate-900 px-6 py-2 rounded-lg hover:bg-slate-400 transition font-semibold">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
