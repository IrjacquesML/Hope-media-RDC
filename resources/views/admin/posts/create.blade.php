@extends('admin.layout')

@section('title', 'Nouveau Post')
@section('page_title', 'Créer un Post')

@section('content')
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">

        {{-- ═══════════════════════════════════════════════════════════════
             PANNEAU : IMPORT DEPUIS LES RÉSEAUX SOCIAUX
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="mb-8 border-2 border-dashed border-indigo-300 rounded-xl bg-indigo-50 p-6">
            <h2 class="text-base font-bold text-indigo-800 mb-1 flex items-center gap-2">
                <i class="fas fa-cloud-download-alt"></i>
                Importer depuis un réseau social
            </h2>
            <p class="text-sm text-indigo-600 mb-4">
                Collez une URL YouTube, TikTok, Instagram ou Facebook pour remplir automatiquement le formulaire.
            </p>

            <!-- Logos plateformes -->
            <div class="flex items-center gap-3 mb-4 flex-wrap">
                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                    <i class="fab fa-youtube"></i> YouTube
                </span>
                <span class="inline-flex items-center gap-1.5 bg-black text-white text-xs font-semibold px-3 py-1 rounded-full">
                    <i class="fab fa-tiktok"></i> TikTok
                </span>
                <span class="inline-flex items-center gap-1.5 bg-pink-100 text-pink-700 text-xs font-semibold px-3 py-1 rounded-full">
                    <i class="fab fa-instagram"></i> Instagram
                </span>
                <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                    <i class="fab fa-facebook"></i> Facebook
                </span>
            </div>

            <!-- Champ URL + bouton -->
            <div class="flex gap-2">
                <input
                    type="url"
                    id="social-import-url"
                    placeholder="https://www.youtube.com/watch?v=... ou lien TikTok / Instagram / Facebook"
                    class="flex-1 px-4 py-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    oninput="detectPlatformBadge(this.value)"
                />
                <button
                    type="button"
                    id="social-import-btn"
                    onclick="importSocialMedia()"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm whitespace-nowrap flex items-center gap-2"
                >
                    <i class="fas fa-download" id="import-btn-icon"></i>
                    <span id="import-btn-text">Récupérer</span>
                </button>
            </div>

            <!-- Badge plateforme détectée -->
            <div id="platform-badge" class="mt-2 hidden"></div>

            <!-- Résultat import -->
            <div id="social-import-result" class="mt-4 hidden">
                <!-- Aperçu -->
                <div class="bg-white border border-indigo-200 rounded-lg p-4 flex gap-4">
                    <img id="import-thumbnail" src="" alt="Miniature" class="w-28 h-20 object-cover rounded-lg flex-shrink-0 hidden">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span id="import-platform-badge" class="text-xs font-bold px-2 py-0.5 rounded-full"></span>
                        </div>
                        <p id="import-title-preview" class="font-semibold text-slate-900 text-sm truncate"></p>
                        <p id="import-author-preview" class="text-xs text-slate-500 mt-0.5"></p>
                        <p id="import-desc-preview" class="text-xs text-slate-600 mt-1 line-clamp-2"></p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="applyImportedContent()"
                    class="mt-3 w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-semibold text-sm flex items-center justify-center gap-2"
                >
                    <i class="fas fa-check-circle"></i>
                    Utiliser ce contenu pour remplir le formulaire
                </button>
            </div>

            <!-- Erreur import -->
            <div id="social-import-error" class="mt-3 hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 flex items-start gap-2">
                <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                <span id="social-import-error-text"></span>
            </div>
        </div>

        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Titre -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Titre</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Auteur -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Auteur</label>
                <input type="text" name="author" value="{{ old('author') }}" placeholder="ex: Pasteur Jean Mulolo" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('author')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catégorie -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Catégorie</label>
                <select name="category_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">Choisir une catégorie...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date et heure de publication -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Date et heure de publication</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('published_at')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type de contenu -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Type de contenu</label>
                <select name="content_type" id="content_type" onchange="updateContentFields()" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="text">📝 Texte</option>
                    <option value="video">🎥 Vidéo</option>
                    <option value="audio">🎵 Audio</option>
                    <option value="image">🖼️ Image</option>
                </select>
                @error('content_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Extrait -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Extrait</label>
                <textarea name="excerpt" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('excerpt') }}</textarea>
                @error('excerpt')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contenu (dynamique selon le type) -->
            <div id="text-content" class="content-field">
                <label class="block text-sm font-semibold text-slate-900 mb-2">Contenu</label>
                <textarea id="content-textarea" name="content" rows="8" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- URL Vidéo -->
            <div id="video-content" class="content-field hidden space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex gap-4 mb-4">
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
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" value="{{ old('video_url') }}">
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
                <textarea name="video_description" rows="4" placeholder="Description de la vidéo" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
            </div>

            <!-- URL Image -->
            <div id="image-content" class="content-field hidden space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex gap-4 mb-4">
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
                        <input type="url" name="image_url" placeholder="https://exemple.com/image.jpg" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
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
                <textarea name="image_description" rows="4" placeholder="Description de l'image" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
            </div>

            <!-- URL Audio -->
            <div id="audio-content" class="content-field hidden space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex gap-4 mb-4">
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
                        <input type="url" name="audio_url" placeholder="https://example.com/audio.mp3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
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
                <textarea name="audio_description" rows="4" placeholder="Description de l'audio" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
            </div>

            <!-- Statut -->
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Statut</label>
                <select name="status" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="draft">Brouillon</option>
                    <option value="published">Publié</option>
                    <option value="archived">Archivé</option>
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
                    <div id="featured-image-placeholder">
                        <i class="fas fa-cloud-upload-alt text-4xl text-slate-400 mb-3"></i>
                        <p class="text-slate-600 font-medium mb-1">Cliquez pour sélectionner une image ou glissez-déposez</p>
                        <p class="text-xs text-slate-500">Formats acceptés: JPEG, PNG, WebP (Max 5MB)</p>
                    </div>
                    <div id="featured-image-preview" class="hidden">
                        <img src="" alt="Aperçu" class="max-h-48 mx-auto mb-3">
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
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>
                    Créer le post
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

        // =====================================================================
        // IMPORT RÉSEAUX SOCIAUX
        // =====================================================================

        // Données du dernier import réussi
        let _importedData = null;

        const PLATFORM_STYLES = {
            youtube:   { label: '▶ YouTube',   cls: 'bg-red-100 text-red-700' },
            tiktok:    { label: '♪ TikTok',    cls: 'bg-black text-white' },
            instagram: { label: '📷 Instagram', cls: 'bg-pink-100 text-pink-700' },
            facebook:  { label: '👍 Facebook',  cls: 'bg-blue-100 text-blue-800' },
        };

        function detectPlatform(url) {
            const u = url.toLowerCase();
            if (u.includes('youtube.com') || u.includes('youtu.be')) return 'youtube';
            if (u.includes('tiktok.com'))    return 'tiktok';
            if (u.includes('instagram.com')) return 'instagram';
            if (u.includes('facebook.com') || u.includes('fb.com') || u.includes('fb.watch')) return 'facebook';
            return null;
        }

        function detectPlatformBadge(url) {
            const badge = document.getElementById('platform-badge');
            const platform = detectPlatform(url.trim());
            if (!platform) { badge.classList.add('hidden'); badge.innerHTML = ''; return; }
            const style = PLATFORM_STYLES[platform];
            badge.innerHTML = `<span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full ${style.cls}">Plateforme détectée : ${style.label}</span>`;
            badge.classList.remove('hidden');
        }

        async function importSocialMedia() {
            const url   = document.getElementById('social-import-url').value.trim();
            const btn   = document.getElementById('social-import-btn');
            const icon  = document.getElementById('import-btn-icon');
            const label = document.getElementById('import-btn-text');

            if (!url) return;

            // Reset UI
            document.getElementById('social-import-result').classList.add('hidden');
            document.getElementById('social-import-error').classList.add('hidden');
            _importedData = null;

            // Loading state
            btn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin';
            label.textContent = 'Chargement…';

            try {
                const fetchUrl = "{{ route('admin.social-media.fetch') }}" + '?url=' + encodeURIComponent(url);
                const res  = await fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (!data.success) {
                    showImportError(data.error ?? 'Erreur inconnue.');
                    return;
                }

                _importedData = data;
                showImportPreview(data);

            } catch (e) {
                showImportError('Erreur réseau. Veuillez réessayer.');
            } finally {
                btn.disabled = false;
                icon.className = 'fas fa-download';
                label.textContent = 'Récupérer';
            }
        }

        function showImportError(msg) {
            document.getElementById('social-import-error-text').textContent = msg;
            document.getElementById('social-import-error').classList.remove('hidden');
        }

        function showImportPreview(data) {
            const style = PLATFORM_STYLES[data.platform] ?? { label: data.platform, cls: 'bg-slate-100 text-slate-700' };

            // Badge plateforme
            const platformBadge = document.getElementById('import-platform-badge');
            platformBadge.textContent = style.label;
            platformBadge.className   = 'text-xs font-bold px-2 py-0.5 rounded-full ' + style.cls;

            document.getElementById('import-title-preview').textContent  = data.title        ?? '';
            document.getElementById('import-author-preview').textContent = data.author ? '@ ' + data.author : '';
            document.getElementById('import-desc-preview').textContent   = (data.description ?? '').substring(0, 200);

            // Miniature
            const thumb = document.getElementById('import-thumbnail');
            if (data.thumbnail) {
                thumb.src = data.thumbnail;
                thumb.classList.remove('hidden');
            } else {
                thumb.classList.add('hidden');
            }

            document.getElementById('social-import-result').classList.remove('hidden');
        }

        function applyImportedContent() {
            if (!_importedData) return;

            // Titre
            const titleInput = document.querySelector('input[name="title"]');
            if (titleInput && !titleInput.value) titleInput.value = _importedData.title ?? '';

            // Auteur
            const authorInput = document.querySelector('input[name="author"]');
            if (authorInput && !authorInput.value) authorInput.value = _importedData.author ?? '';

            // Date
            if (_importedData.published_at) {
                const dateInput = document.querySelector('input[name="published_at"]');
                if (dateInput) dateInput.value = _importedData.published_at;
            }

            // Excerpt / description
            const excerptInput = document.querySelector('textarea[name="excerpt"]');
            if (excerptInput && !excerptInput.value && _importedData.description) {
                excerptInput.value = _importedData.description.substring(0, 500);
            }

            // Contenu
            const contentTextarea = document.getElementById('content-textarea');
            if (contentTextarea && !contentTextarea.value && _importedData.description) {
                contentTextarea.value = _importedData.description;
            }

            // Basculer en type vidéo et remplir l'URL
            const contentTypeSelect = document.getElementById('content_type');
            contentTypeSelect.value = 'video';
            updateContentFields();

            // URL de la vidéo
            const videoUrlInput = document.querySelector('input[name="video_url"]');
            if (videoUrlInput) videoUrlInput.value = _importedData.url ?? '';

            // Description vidéo
            const videoDesc = document.querySelector('textarea[name="video_description"]');
            if (videoDesc && !videoDesc.value) videoDesc.value = _importedData.description ?? '';

            // Confirmation visuelle
            const applyBtn = document.querySelector('#social-import-result button');
            if (applyBtn) {
                applyBtn.innerHTML = '<i class="fas fa-check-double mr-2"></i>Formulaire rempli !';
                applyBtn.classList.replace('bg-green-600', 'bg-green-700');
                setTimeout(() => {
                    applyBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Utiliser ce contenu pour remplir le formulaire';
                    applyBtn.classList.replace('bg-green-700', 'bg-green-600');
                }, 3000);
            }
        }
    </script>
@endsection
