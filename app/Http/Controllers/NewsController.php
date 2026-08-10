<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::published()->orderByDesc('published_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('categorie')) {
            $query->where('category', $category);
        }

        if ($author = $request->get('auteur')) {
            $query->where('author', $author);
        }

        $news = $query->paginate(12)->withQueryString();

        $categories = News::categories();
        $authors = News::published()->whereNotNull('author')->distinct()->pluck('author')->sort()->values();

        return view('news.index', compact('news', 'search', 'categories', 'authors'));
    }

    public function show(string $slug)
    {
        $article = News::where('slug', $slug)->published()->firstOrFail();

        // Incrémenter les vues
        $article->increment('views');
        $article->load(['comments.replies']);

        $recent = News::published()
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('news.show', compact('article', 'recent'));
    }
}
