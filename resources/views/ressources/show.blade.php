@extends('layouts.app')

@section('title', $resource->title)
@section('meta_description', Str::limit(strip_tags($resource->description), 160))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Fil d'Ariane -->
    <nav class="text-sm text-slate-500 mb-6 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="{{ route('ressources.index') }}" class="hover:text-blue-600">Ressources</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-slate-800 font-medium">{{ Str::limit($resource->title, 50) }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Couverture + téléchargement -->
        <div class="md:col-span-1">
            @if($resource->cover_image)
                <img src="{{ asset($resource->cover_image) }}" alt="{{ $resource->title }}"
                     class="w-full rounded-xl shadow-lg object-cover">
            @else
                <div class="w-full aspect-[3/4] bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-7xl text-blue-400"></i>
                </div>
            @endif

            <!-- Télécharger -->
            <a href="{{ route('ressources.download', $resource) }}"
               class="mt-5 block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-md text-lg">
                <i class="fas fa-download mr-2"></i>Télécharger gratuitement
            </a>

            <!-- Infos fichier -->
            <div class="mt-4 bg-slate-50 rounded-xl p-4 space-y-2 text-sm text-slate-600">
                <div class="flex justify-between">
                    <span class="font-semibold">Format</span>
                    <span class="uppercase font-mono text-blue-700">{{ $resource->file_type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Taille</span>
                    <span>{{ $resource->formatted_file_size }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Téléchargements</span>
                    <span>{{ number_format($resource->downloads) }}</span>
                </div>
            </div>
        </div>

        <!-- Détails -->
        <div class="md:col-span-2">
            <!-- Badge catégorie -->
            <span class="inline-block bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full font-semibold mb-3">
                {{ $resource->category_label }}
            </span>

            <h1 class="text-3xl font-bold text-slate-900 mb-4">{{ $resource->title }}</h1>

            <!-- Méta -->
            <div class="flex flex-wrap gap-4 text-sm text-slate-500 mb-6 border-b border-slate-100 pb-4">
                @if($resource->author)
                    <span><i class="fas fa-user mr-1 text-blue-400"></i>{{ $resource->author }}</span>
                @endif
                @if($resource->publisher)
                    <span><i class="fas fa-building mr-1 text-blue-400"></i>{{ $resource->publisher }}</span>
                @endif
                @if($resource->published_year)
                    <span><i class="fas fa-calendar mr-1 text-blue-400"></i>{{ $resource->published_year }}</span>
                @endif
            </div>

            <!-- Description -->
            @if($resource->description)
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
                    {!! nl2br(e($resource->description)) !!}
                </div>
            @endif

            <!-- Partage -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-sm font-semibold text-slate-600 mb-3">Partager cette ressource</p>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank"
                       class="bg-blue-800 text-white px-4 py-2 rounded-lg text-sm hover:opacity-80 transition">
                        <i class="fab fa-facebook mr-1"></i>Facebook
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($resource->title . ' - ' . url()->current()) }}"
                       target="_blank"
                       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:opacity-80 transition">
                        <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Ressources similaires -->
    @if($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                <i class="fas fa-books text-blue-500"></i>Ressources similaires
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($related as $item)
                    <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden">
                        @if($item->cover_image)
                            <img src="{{ asset($item->cover_image) }}" alt="{{ $item->title }}"
                                 class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                                <i class="fas fa-book text-3xl text-blue-300"></i>
                            </div>
                        @endif
                        <div class="p-3">
                            <a href="{{ route('ressources.show', $item->slug) }}"
                               class="font-semibold text-slate-900 hover:text-blue-600 transition line-clamp-2 text-sm">
                                {{ $item->title }}
                            </a>
                            <a href="{{ route('ressources.download', $item) }}"
                               class="mt-2 block text-center bg-blue-600 text-white text-xs py-1.5 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-download mr-1"></i>Télécharger
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
