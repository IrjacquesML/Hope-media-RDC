@extends('admin.layout')

@section('title', 'Modifier l\'image')
@section('page_title', 'Modifier l\'image du carrousel')

@section('content')
<div class="max-w-xl">
    <a href="{{ route('admin.hero-slides.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 mb-6 transition">
        <i class="fas fa-arrow-left"></i> Retour au carrousel
    </a>

    <div class="bg-white rounded-lg shadow p-6 md:p-8">
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-700 rounded-lg">
                <div class="flex items-center gap-2 font-semibold mb-2">
                    <i class="fas fa-exclamation-circle"></i>
                    Veuillez corriger les erreurs suivantes :
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.hero-slides.update', $heroSlide) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Image actuelle</label>
                <img src="{{ asset($heroSlide->image) }}"
                     alt="{{ $heroSlide->title ?? 'Slide' }}"
                     class="w-full max-h-48 object-cover rounded-lg border border-slate-200 mb-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Remplacer l'image <span class="text-slate-400 font-normal">(optionnel)</span></label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('image') border-red-500 @enderror">
                <p class="text-xs text-slate-400 mt-1">JPEG, PNG ou WebP — max 5 Mo. Laissez vide pour conserver l'image actuelle.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Titre <span class="text-slate-400 font-normal">(optionnel)</span></label>
                <input type="text" name="title" value="{{ old('title', $heroSlide->title) }}"
                       placeholder="Ex: Lever de soleil"
                       class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $heroSlide->sort_order) }}" min="0" max="999"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $heroSlide->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-slate-700">Active sur l'accueil</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-1"></i> Mettre à jour
                </button>
                <a href="{{ route('admin.hero-slides.index') }}"
                   class="border border-slate-300 text-slate-600 px-6 py-2.5 rounded-lg hover:bg-slate-50 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
