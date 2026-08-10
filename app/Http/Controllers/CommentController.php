<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\News;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        return $this->storeFor($request, $post);
    }

    public function storeNews(Request $request, News $news)
    {
        return $this->storeFor($request, $news);
    }

    private function storeFor(Request $request, Model $commentable)
    {
        $validated = $request->validate([
            'author_name'  => ['required', 'string', 'max:100'],
            'author_email' => ['nullable', 'email', 'max:255'],
            'content'      => ['required', 'string', 'max:2000'],
        ]);

        $commentable->comments()->create([
            'author_name'  => $validated['author_name'],
            'author_email' => $validated['author_email'] ?? null,
            'content'      => $validated['content'],
            'is_approved'  => true,
        ]);

        return back()->with('comment_success', 'Merci pour votre commentaire ! Il est maintenant visible.');
    }
}
