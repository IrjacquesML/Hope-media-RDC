@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Posts -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm">Total Posts</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['total_posts'] }}</p>
                </div>
                <div class="text-5xl text-blue-600 opacity-20">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
        
        <!-- Published Posts -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm">Posts Publiés</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['published_posts'] }}</p>
                </div>
                <div class="text-5xl text-green-600 opacity-20">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <!-- Draft Posts -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm">Posts en Brouillon</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['draft_posts'] }}</p>
                </div>
                <div class="text-5xl text-yellow-600 opacity-20">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>
        
        <!-- New Messages -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm">Nouveaux Messages</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['new_contacts'] }}</p>
                </div>
                <div class="text-5xl text-red-600 opacity-20">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>

        <!-- Total Écho de la mission -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm">Écho de la mission publiés</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['published_news'] }}</p>
                </div>
                <div class="text-5xl text-purple-600 opacity-20">
                    <i class="fas fa-newspaper"></i>
                </div>
            </div>
        </div>

        <!-- Newsletter Abonnés -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-teal-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm">Abonnés newsletter</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['newsletter_subscribers'] }}</p>
                </div>
                <div class="text-5xl text-teal-600 opacity-20">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Recent Posts -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900">
                    <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                    Posts Récents
                </h2>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($recent_posts as $post)
                    <div class="p-4 hover:bg-slate-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-slate-900 hover:text-blue-600">
                                    {{ $post->title }}
                                </a>
                                <p class="text-sm text-slate-600 mt-1">
                                    {{ $post->category?->name ?? 'Sans catégorie' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>Aucun post</p>
                    </div>
                @endforelse
            </div>
            <div class="border-t border-slate-200 p-6">
                <a href="{{ route('admin.posts.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Voir tous les posts →
                </a>
            </div>
        </div>
        
        <!-- Recent Messages -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900">
                    <i class="fas fa-envelope mr-2 text-red-600"></i>
                    Messages Récents
                </h2>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($recent_contacts as $contact)
                    <div class="p-4 hover:bg-slate-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900">{{ $contact->name }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $contact->email }}</p>
                                <p class="text-xs text-slate-500 mt-2">{{ $contact->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $contact->status === 'new' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800' }}">
                                {{ ucfirst($contact->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>Aucun message</p>
                    </div>
                @endforelse
            </div>
            <div class="border-t border-slate-200 p-6">
                <a href="{{ route('admin.contacts.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Voir tous les messages →
                </a>
            </div>
        </div>
    </div>
@endsection
