@extends('admin.layout')

@section('title', 'Éditer Post')
@section('page_title', 'Éditer: ' . $post->title)

@section('content')
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Titre -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Titre</label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Auteur -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Auteur</label>
                <input type="text" name="author" value="{{ old('author', $post->author) }}" placeholder="ex: Pasteur Jean Mulolo" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('author')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catégorie -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Catégorie</label>
                <select name="category_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date et heure de publication -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Date et heure de publication</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('published_at')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type de contenu -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Type de contenu</label>
                <select name="content_type" id="content_type" onchange="updateContentFields()" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="text" {{ old('content_type', $post->content_type) == 'text' ? 'selected' : '' }}>📝 Texte</option>
                    <option value="video" {{ old('content_type', $post->content_type) == 'video' ? 'selected' : '' }}>🎥 Vidéo</option>
                    <option value="audio" {{ old('content_type', $post->content_type) == 'audio' ? 'selected' : '' }}>🎵 Audio</option>
                    <option value="image" {{ old('content_type', $post->content_type) == 'image' ? 'selected' : '' }}>🖼️ Image</option>
                </select>
                @error('content_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Extrait -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Extrait</label>
                <textarea name="excerpt" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contenu (dynamique selon le type) -->
            <div id="text-content" class="content-field">
                <label class="block text-sm font-semibold text-slate-900 mb-2">Contenu</label>
                <textarea id="content-textarea" name="content" rows="8" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('content', $post->content) }}</textarea>
                @error('content')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- URL Vidéo -->
            <div id="video-content" class="content-field hidden space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex flex-wrap gap-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="video_source" value="url" checked onchange="toggleVideoSource('url')" class="w-4 h-4">
                            <span class="font-medium">URL de la vidéo</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="video_source" value="file" onchange="toggleVideoSource('file')" class="w-4 h-4">
                            <span class="font-medium">Télécharger un fichier</span>
                        </label>
                    </div>

                    <!-- URL Input -->
                    <div id="video-url-section">
                        <label class="block text-sm font-semibold text-slate-900 mb-2">URL de la Vidéo</label>
                        <p class="text-sm text-slate-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Exemples acceptés:
                        </p>
                        <ul class="text-xs text-slate-600 mb-3 ml-6 space-y-1">
                            <li>🔴 YouTube: <code class="bg-slate-100 px-2 py-1 rounded">https://www.youtube.com/watch?v=dQw4w9WgXcQ</code></li>
                            <li>🔴 YouTube court: <code class="bg-slate-100 px-2 py-1 rounded">https://youtu.be/dQw4w9WgXcQ</code></li>
                            <li>🟦 Vimeo: <code class="bg-slate-100 px-2 py-1 rounded">https://vimeo.com/123456789</code></li>
                            <li>▶️ Dailymotion: <code class="bg-slate-100 px-2 py-1 rounded">https://www.dailymotion.com/video/x123456</code></li>
                        </ul>
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." 
                            value="{{ old('video_url', $post->medias->first()?->url ?? '') }}" 
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>

                    <!-- File Upload -->
                    <div id="video-file-section" class="hidden">
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Fichier Vidéo</label>
                        <p class="text-sm text-slate-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            MP4, WebM, Ogg (max 500MB)
                        </p>
                        <input type="file" name="video_file" accept="video/*" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>
                </div>

                <label class="block text-sm font-semibold text-slate-900 mb-2">Description</label>
                <textarea name="video_description" rows="4" placeholder="Description de la vidéo" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('video_description', $post->medias->first()?->description ?? '') }}</textarea>
            </div>

            <!-- URL Image -->
            <div id="image-content" class="content-field hidden space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex flex-wrap gap-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="image_source" value="url" checked onchange="toggleImageSource('url')" class="w-4 h-4">
                            <span class="font-medium">URL de l'image</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="image_source" value="file" onchange="toggleImageSource('file')" class="w-4 h-4">
                            <span class="font-medium">Télécharger un fichier</span>
                        </label>
                    </div>

                    <!-- URL Input -->
                    <div id="image-url-section">
                        <label class="block text-sm font-semibold text-slate-900 mb-2">URL de l'Image</label>
                        <p class="text-sm text-slate-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Lien complet vers l'image (JPEG, PNG, WebP)
                        </p>
                        <input type="url" name="image_url" placeholder="https://exemple.com/image.jpg" 
                            value="{{ old('image_url', $post->medias->first()?->url ?? '') }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>

                    <!-- File Upload -->
                    <div id="image-file-section" class="hidden">
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Fichier Image</label>
                        <p class="text-sm text-slate-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            JPEG, PNG, WebP (max 50MB)
                        </p>
                        <input type="file" name="image_file" accept="image/*" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>
                </div>

                <label class="block text-sm font-semibold text-slate-900 mb-2">Description</label>
                <textarea name="image_description" rows="4" placeholder="Description de l'image" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('image_description', $post->medias->first()?->description ?? '') }}</textarea>
            </div>

            <!-- URL Audio -->
            <div id="audio-content" class="content-field hidden space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex flex-wrap gap-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audio_source" value="url" checked onchange="toggleAudioSource('url')" class="w-4 h-4">
                            <span class="font-medium">URL de l'audio</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audio_source" value="file" onchange="toggleAudioSource('file')" class="w-4 h-4">
                            <span class="font-medium">Télécharger un fichier</span>
                        </label>
                    </div>

                    <!-- URL Input -->
                    <div id="audio-url-section">
                        <label class="block text-sm font-semibold text-slate-900 mb-2">URL de l'Audio</label>
                        <p class="text-sm text-slate-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            SoundCloud, Spotify, ou lien direct vers audio (MP3, WAV)
                        </p>
                        <input type="url" name="audio_url" placeholder="https://example.com/audio.mp3" 
                            value="{{ old('audio_url', $post->medias->first()?->url ?? '') }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>

                    <!-- File Upload -->
                    <div id="audio-file-section" class="hidden">
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Fichier Audio</label>
                        <p class="text-sm text-slate-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            MP3, WAV, OGG, M4A (max 100MB)
                        </p>
                        <input type="file" name="audio_file" accept="audio/*" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>
                </div>

                <label class="block text-sm font-semibold text-slate-900 mb-2">Description</label>
                <textarea name="audio_description" rows="4" placeholder="Description de l'audio" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('audio_description', $post->medias->first()?->description ?? '') }}</textarea>
            </div>

            <!-- Statut -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Statut</label>
                <select name="status" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Publié</option>
                    <option value="archived" {{ old('status', $post->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                </select>
                @error('status')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image en vedette -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">
                    <i class="fas fa-image mr-2"></i>
                    Image en vedette
                </label>
                <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-blue-500 transition cursor-pointer" id="featured-image-drop">
                    <input type="file" name="featured_image" accept="image/*" class="hidden" id="featured-image-input">
                    <div id="featured-image-placeholder" class="{{ $post->featured_image ? 'hidden' : '' }}">
                        <i class="fas fa-cloud-upload-alt text-4xl text-slate-400 mb-3"></i>
                        <p class="text-slate-600 font-medium mb-1">Cliquez pour sélectionner une image ou glissez-déposez</p>
                        <p class="text-xs text-slate-500">Formats acceptés: JPEG, PNG, WebP (Max 5MB)</p>
                    </div>
                    <div id="featured-image-preview" class="{{ $post->featured_image ? '' : 'hidden' }}">
                        @if($post->featured_image)
                            <img src="{{ asset($post->featured_image) }}" alt="Aperçu" class="max-h-48 mx-auto mb-3">
                        @else
                            <img src="" alt="Aperçu" class="max-h-48 mx-auto mb-3">
                        @endif
                        <button type="button" class="text-sm text-red-600 hover:text-red-700 font-semibold" onclick="removeFeaturedImage()">
                            <i class="fas fa-trash mr-1"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
                @error('featured_image')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex gap-4 pt-6 border-t border-slate-200">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>
                    Mettre à jour
                </button>
                <a href="{{ route('admin.posts.index') }}" class="bg-slate-300 text-slate-900 px-6 py-2 rounded-lg hover:bg-slate-400 transition font-semibold">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    <script>
        function updateContentFields() {
            const contentType = document.getElementById('content_type').value;
            const fields = document.querySelectorAll('.content-field');
            const contentTextarea = document.getElementById('content-textarea');
            
            // Masquer tous les champs
            fields.forEach(field => field.classList.add('hidden'));
            
            // Afficher le champ approprié et gérer l'attribut required
            if (contentType === 'text') {
                document.getElementById('text-content').classList.remove('hidden');
                contentTextarea.setAttribute('required', 'required');
            } else if (contentType === 'video') {
                document.getElementById('video-content').classList.remove('hidden');
                contentTextarea.removeAttribute('required');
            } else if (contentType === 'image') {
                document.getElementById('image-content').classList.remove('hidden');
                contentTextarea.removeAttribute('required');
            } else if (contentType === 'audio') {
                document.getElementById('audio-content').classList.remove('hidden');
                contentTextarea.removeAttribute('required');
            }
        }

        function toggleVideoSource(source) {
            if (source === 'url') {
                document.getElementById('video-url-section').classList.remove('hidden');
                document.getElementById('video-file-section').classList.add('hidden');
            } else {
                document.getElementById('video-url-section').classList.add('hidden');
                document.getElementById('video-file-section').classList.remove('hidden');
            }
        }

        function toggleImageSource(source) {
            if (source === 'url') {
                document.getElementById('image-url-section').classList.remove('hidden');
                document.getElementById('image-file-section').classList.add('hidden');
            } else {
                document.getElementById('image-url-section').classList.add('hidden');
                document.getElementById('image-file-section').classList.remove('hidden');
            }
        }

        function toggleAudioSource(source) {
            if (source === 'url') {
                document.getElementById('audio-url-section').classList.remove('hidden');
                document.getElementById('audio-file-section').classList.add('hidden');
            } else {
                document.getElementById('audio-url-section').classList.add('hidden');
                document.getElementById('audio-file-section').classList.remove('hidden');
            }
        }

        // Gestion du featured_image
        const featuredImageDrop = document.getElementById('featured-image-drop');
        const featuredImageInput = document.getElementById('featured-image-input');
        const featuredImagePlaceholder = document.getElementById('featured-image-placeholder');
        const featuredImagePreview = document.getElementById('featured-image-preview');

        // Click pour sélectionner
        featuredImageDrop.addEventListener('click', () => featuredImageInput.click());

        // Drag and drop
        featuredImageDrop.addEventListener('dragover', (e) => {
            e.preventDefault();
            featuredImageDrop.classList.add('border-blue-500', 'bg-blue-50');
        });

        featuredImageDrop.addEventListener('dragleave', () => {
            featuredImageDrop.classList.remove('border-blue-500', 'bg-blue-50');
        });

        featuredImageDrop.addEventListener('drop', (e) => {
            e.preventDefault();
            featuredImageDrop.classList.remove('border-blue-500', 'bg-blue-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                featuredImageInput.files = files;
                previewFeaturedImage();
            }
        });

        // Change event
        featuredImageInput.addEventListener('change', previewFeaturedImage);

        function previewFeaturedImage() {
            const file = featuredImageInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    featuredImagePreview.querySelector('img').src = e.target.result;
                    featuredImagePlaceholder.classList.add('hidden');
                    featuredImagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function removeFeaturedImage() {
            featuredImageInput.value = '';
            featuredImagePlaceholder.classList.remove('hidden');
            featuredImagePreview.classList.add('hidden');
        }
        
        // Initialiser à la charge
        document.addEventListener('DOMContentLoaded', updateContentFields);
    </script>
@endsection
