@extends('admin.layout')

@section('title', 'Commentaires')
@section('page_title', 'Commentaires des articles')

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-comments text-blue-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500">Commentaires</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-amber-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['unanswered'] }}</p>
            <p class="text-xs text-slate-500">Sans réponse</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-reply text-green-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">{{ $stats['replies'] }}</p>
            <p class="text-xs text-slate-500">Réponses admin</p>
        </div>
    </div>
</div>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <form method="GET" action="{{ route('admin.comments.index') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <div class="relative">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Rechercher..."
                   class="border border-slate-300 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        </div>
        <label class="inline-flex items-center gap-2 border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white cursor-pointer">
            <input type="checkbox" name="sans_reponse" value="1" {{ request('sans_reponse') === '1' ? 'checked' : '' }}
                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                   onchange="this.form.submit()">
            Sans réponse
        </label>
        <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition text-sm">
            <i class="fas fa-filter mr-1"></i>Filtrer
        </button>
    </form>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
        <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
    </div>
@endif

<div class="space-y-4">
    @forelse($comments as $comment)
        @php
            $article = $comment->commentable;
            $articleTitle = $article->title ?? 'Article supprimé';
            $articleUrl = null;
            if ($article instanceof \App\Models\Post) {
                $articleUrl = route('posts.show', $article->slug);
                $articleType = 'Enseignement';
            } elseif ($article instanceof \App\Models\News) {
                $articleUrl = route('news.show', $article->slug);
                $articleType = 'Écho de la mission';
            } else {
                $articleType = 'Article';
            }
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
            <div class="flex flex-col lg:flex-row lg:items-start gap-4 justify-between mb-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-semibold text-slate-900">{{ $comment->author_name }}</span>
                        <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                        @if($comment->replies->isEmpty())
                            <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Sans réponse</span>
                        @else
                            <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Répondu</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                    <p class="text-xs text-slate-500 mt-2">
                        <span class="font-medium">{{ $articleType }} :</span>
                        @if($articleUrl)
                            <a href="{{ $articleUrl }}" target="_blank" class="text-blue-600 hover:underline">{{ $articleTitle }}</a>
                        @else
                            {{ $articleTitle }}
                        @endif
                    </p>
                </div>
                <form action="{{ route('admin.comments.delete', $comment) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce commentaire et ses réponses ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-400 hover:text-red-600 transition text-sm" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>

            @if($comment->replies->isNotEmpty())
                <div class="ml-0 sm:ml-6 mb-4 space-y-3 border-l-2 border-blue-200 pl-4">
                    @foreach($comment->replies as $reply)
                        <div class="bg-blue-50 rounded-lg p-3">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">
                                        <i class="fas fa-shield-alt mr-1"></i>Admin
                                    </span>
                                    <span class="font-semibold text-slate-800 text-sm">{{ $reply->author_name }}</span>
                                    <span class="text-xs text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <form action="{{ route('admin.comments.delete', $reply) }}" method="POST"
                                      onsubmit="return confirm('Supprimer cette réponse ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600 transition text-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $reply->content }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <details class="group" {{ $errors->any() && (int) old('reply_to') === $comment->id ? 'open' : '' }}>
                <summary class="cursor-pointer list-none inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                    <i class="fas fa-reply"></i>
                    {{ $comment->replies->isEmpty() ? 'Répondre' : 'Ajouter une réponse' }}
                </summary>
                <form action="{{ route('admin.comments.reply', $comment) }}" method="POST" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="reply_to" value="{{ $comment->id }}">
                    <textarea name="content" rows="3" required maxlength="2000"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-400 @enderror"
                              placeholder="Votre réponse en tant qu'administrateur...">{{ (int) old('reply_to') === $comment->id ? old('content') : '' }}</textarea>
                    @error('content')
                        @if((int) old('reply_to') === $comment->id)
                            <p class="text-red-500 text-xs">{{ $message }}</p>
                        @endif
                    @enderror
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                        <i class="fas fa-paper-plane"></i>
                        Publier la réponse
                    </button>
                </form>
            </details>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center text-slate-500">
            <i class="fas fa-comments text-4xl opacity-30 mb-3 block"></i>
            Aucun commentaire pour le moment.
        </div>
    @endforelse
</div>

@if($comments->hasPages())
    <div class="mt-6">{{ $comments->links() }}</div>
@endif

@endsection
