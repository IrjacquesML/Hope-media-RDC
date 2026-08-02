@extends('layouts.app')

@section('title', $article->meta_title ?? $article->title)
@section('meta_description', $article->excerpt)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Fil d'Ariane --}}
    <nav class="text-sm text-slate-500 mb-6 flex items-center gap-1 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
        <i class="fas fa-chevron-right text-xs text-slate-300"></i>
        <a href="{{ route('news.index') }}" class="hover:text-blue-600">Écho de la mission</a>
        <i class="fas fa-chevron-right text-xs text-slate-300"></i>
        <span class="text-slate-700">{{ Str::limit($article->title, 50) }}</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-10">

        {{-- ── Colonne principale ────────────────────────────── --}}
        <div class="flex-1 min-w-0">
            <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
                @if($article->featured_image)
                    <img src="{{ asset($article->featured_image) }}"
                         alt="{{ $article->title }}"
                         class="w-full max-h-96 object-cover">
                @endif

                <div class="p-6 md:p-10">
                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @if($article->is_featured)
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-white bg-red-500 px-3 py-1 rounded-full">
                                <i class="fas fa-star text-[10px]"></i> À la une
                            </span>
                        @endif
                        @if($article->category)
                            <a href="{{ route('news.index', ['categorie' => $article->category]) }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700 bg-purple-100 hover:bg-purple-200 px-3 py-1 rounded-full transition">
                                <i class="fas fa-tag text-[10px]"></i>{{ $article->category }}
                            </a>
                        @endif
                    </div>

                    {{-- Titre --}}
                    <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-5 leading-tight">
                        {{ $article->title }}
                    </h1>

                    {{-- Méta --}}
                    <div class="flex flex-wrap gap-4 text-sm text-slate-500 border-y border-slate-100 py-3 mb-6">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-calendar text-blue-400"></i>
                            {{ $article->published_at?->translatedFormat('d F Y') ?? $article->created_at->translatedFormat('d F Y') }}
                        </span>
                        @if($article->author)
                            <span class="flex items-center gap-1">
                                <i class="fas fa-user text-blue-400"></i>
                                <a href="{{ route('news.index', ['auteur' => $article->author]) }}"
                                   class="hover:text-blue-600 transition">{{ $article->author }}</a>
                            </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <i class="fas fa-eye text-blue-400"></i>{{ number_format($article->views) }} vue(s)
                        </span>
                        @php
                            $words   = str_word_count(strip_tags($article->content));
                            $minutes = max(1, (int) ceil($words / 200));
                        @endphp
                        <span class="flex items-center gap-1">
                            <i class="fas fa-clock text-blue-400"></i>~{{ $minutes }} min de lecture
                        </span>
                    </div>

                    {{-- Tags --}}
                    @if($article->tags)
                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach($article->tags as $tag)
                                <a href="{{ route('news.index', ['q' => $tag]) }}"
                                   class="bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-700 text-xs px-3 py-1 rounded-full transition">
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Contenu --}}
                    <div class="prose prose-slate prose-lg max-w-none text-slate-700 leading-relaxed">
                        {!! nl2br(e($article->content)) !!}
                    </div>

                    {{-- Partage social --}}
                    <div class="mt-10 pt-6 border-t border-slate-100">
                        <p class="text-sm font-semibold text-slate-600 mb-3">
                            <i class="fas fa-share-alt mr-2 text-blue-500"></i>Partager cet article :
                        </p>
                        <div class="flex gap-3 flex-wrap">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                               target="_blank" rel="noopener"
                               class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($article->title . ' — ' . request()->url()) }}"
                               target="_blank" rel="noopener"
                               class="flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-600 transition">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(request()->url()) }}"
                               target="_blank" rel="noopener"
                               class="flex items-center gap-2 bg-sky-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-sky-600 transition">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <button onclick="navigator.clipboard.writeText('{{ request()->url() }}').then(()=>{ this.innerHTML='<i class=\'fas fa-check mr-2\'></i>Copié !'; setTimeout(()=>{ this.innerHTML='<i class=\'fas fa-link mr-2\'></i>Copier le lien'; },2000); })"
                                    class="flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-200 transition">
                                <i class="fas fa-link mr-2"></i>Copier le lien
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Newsletter inline --}}
            <div class="mt-8 bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-6 md:p-8 text-white text-center shadow-lg">
                <h3 class="text-xl font-bold mb-2">
                    <i class="fas fa-bell mr-2"></i>Ne manquez aucun Écho de la mission
                </h3>
                <p class="text-blue-100 text-sm mb-5">Inscrivez-vous à notre newsletter gratuite.</p>

                @if(session('newsletter_success'))
                    <div class="mb-4 bg-green-500 text-white px-4 py-2 rounded-lg text-sm inline-block">
                        <i class="fas fa-check-circle mr-1"></i>{{ session('newsletter_success') }}
                    </div>
                @elseif(session('newsletter_info'))
                    <div class="mb-4 bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm inline-block">
                        <i class="fas fa-info-circle mr-1"></i>{{ session('newsletter_info') }}
                    </div>
                @endif

                <form action="{{ route('newsletter.subscribe') }}" method="POST"
                      class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto">
                    @csrf
                    <input type="email" name="email" required placeholder="Votre adresse e-mail"
                           class="flex-1 border-0 px-4 py-3 rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-white text-sm">
                    <button type="submit"
                            class="bg-white text-blue-700 font-bold px-5 py-3 rounded-lg hover:bg-blue-50 transition whitespace-nowrap text-sm">
                        <i class="fas fa-paper-plane mr-2"></i>S'abonner
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Sidebar --}}
        <aside class="w-full lg:w-80 flex-shrink-0 space-y-6">

            {{-- Articles récents --}}
            @if($recent->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-900 text-base">
                            <i class="fas fa-newspaper mr-2 text-blue-600"></i>Autres articles
                        </h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($recent as $item)
                            <a href="{{ route('news.show', $item->slug) }}"
                               class="flex gap-3 p-4 hover:bg-slate-50 transition group">
                                @if($item->featured_image)
                                    <img src="{{ asset($item->featured_image) }}"
                                         alt="{{ $item->title }}"
                                         class="w-16 h-14 object-cover rounded-lg flex-shrink-0">
                                @else
                                    <div class="w-16 h-14 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-newspaper text-blue-300"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 transition leading-snug line-clamp-2">
                                        {{ $item->title }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        <i class="fas fa-calendar mr-1"></i>{{ $item->published_at?->format('d/m/Y') ?? $item->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="p-4 border-t border-slate-100">
                        <a href="{{ route('news.index') }}"
                           class="block text-center text-sm font-semibold text-blue-600 hover:underline">
                            Voir tous les articles <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Catégories --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-base">
                        <i class="fas fa-tags mr-2 text-purple-600"></i>Catégories
                    </h2>
                </div>
                <div class="p-4 flex flex-wrap gap-2">
                    @foreach(\App\Models\News::categories() as $cat)
                        <a href="{{ route('news.index', ['categorie' => $cat]) }}"
                           class="text-xs font-semibold px-3 py-1.5 rounded-full transition
                                  {{ $article->category === $cat
                                       ? 'bg-purple-600 text-white'
                                       : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            </div>

        </aside>
    </div>

</div>
@endsection
