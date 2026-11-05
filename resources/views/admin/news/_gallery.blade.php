{{-- Carrousel d'images de l'article (Écho de la mission). $news est optionnel (création). --}}
@php $galleryImages = isset($news) ? $news->images : collect(); @endphp
<div class="border border-slate-200 rounded-lg p-4 space-y-4">
    <div>
        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <i class="fas fa-images text-blue-600"></i> Carrousel d'images
        </h3>
        <p class="text-xs text-slate-500 mt-1">
            Ces images défilent en haut de l'article, après l'image à la une. JPG, PNG ou WebP, 5 Mo max par image, 20 images max par envoi.
        </p>
    </div>

    @if($galleryImages->isNotEmpty())
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($galleryImages as $image)
                <li class="flex gap-3 items-start border border-slate-200 rounded-lg p-2 bg-slate-50">
                    <img src="{{ asset($image->image) }}" alt="" class="w-24 h-16 object-cover rounded">
                    <div class="flex-1 min-w-0 space-y-2">
                        <input type="text" name="gallery_captions[{{ $image->id }}]" maxlength="255"
                               value="{{ old('gallery_captions.' . $image->id, $image->caption) }}"
                               placeholder="Légende (facultatif)"
                               class="w-full border border-slate-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-xs text-slate-500 flex items-center gap-1">
                                Ordre
                                <input type="number" name="gallery_order[{{ $image->id }}]" min="0" max="9999"
                                       value="{{ old('gallery_order.' . $image->id, $image->sort_order) }}"
                                       class="w-16 border border-slate-300 rounded px-1 py-0.5 text-sm">
                            </label>
                            <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                                <input type="checkbox" name="gallery_remove[]" value="{{ $image->id }}" class="w-3.5 h-3.5">
                                Supprimer
                            </label>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <div>
        <label for="gallery_images" class="block text-sm font-semibold text-slate-700 mb-1">
            {{ $galleryImages->isNotEmpty() ? 'Ajouter des images' : 'Images du carrousel' }}
        </label>
        <input type="file" name="gallery_images[]" id="gallery_images" multiple accept="image/jpeg,image/png,image/webp"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gallery_images') border-red-500 @enderror @error('gallery_images.*') border-red-500 @enderror">
        @error('gallery_images')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        @error('gallery_images.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        <div id="gallery-preview" class="flex flex-wrap gap-2 mt-2"></div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('gallery_images');
    const preview = document.getElementById('gallery-preview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        preview.querySelectorAll('img').forEach(img => URL.revokeObjectURL(img.src));
        preview.innerHTML = '';
        Array.from(input.files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = '';
            img.className = 'w-20 h-14 object-cover rounded border border-slate-200';
            preview.appendChild(img);
        });
    });
})();
</script>
@endpush
