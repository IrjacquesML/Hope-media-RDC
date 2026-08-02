<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        // Publications admin (posts + actualités) triées par date
        $posts = Post::published()
            ->with(['category'])
            ->withCount('comments')
            ->limit(12)
            ->get()
            ->map(function (Post $post) {
                return (object) [
                    'kind' => 'post',
                    'title' => $post->title,
                    'excerpt' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 100),
                    'image' => $post->featured_image,
                    'url' => $post->url,
                    'author' => $post->author ?? 'Hope Media RDC',
                    'date' => $post->published_at ?? $post->created_at,
                    'badge' => $post->category?->name ?? ucfirst((string) $post->content_type),
                    'icon' => $post->getContentIconAttribute(),
                ];
            });

        $news = News::published()
            ->orderByDesc('published_at')
            ->limit(12)
            ->get()
            ->map(function (News $article) {
                return (object) [
                    'kind' => 'news',
                    'title' => $article->title,
                    'excerpt' => $article->excerpt,
                    'image' => $article->featured_image,
                    'url' => route('news.show', $article->slug),
                    'author' => $article->author ?? 'Hope Media RDC',
                    'date' => $article->published_at ?? $article->created_at,
                    'badge' => $article->category ?: 'Écho de la mission',
                    'icon' => 'newspaper',
                ];
            });

        $latestPublications = $posts->concat($news)
            ->sortByDesc(fn ($item) => $item->date?->timestamp ?? 0)
            ->take(6)
            ->values();

        $latestVideos = Video::orderByDesc('published_at')->limit(12)->get();

        $videosByPlatform = [
            'youtube'   => Video::where('platform', 'youtube')->orderByDesc('published_at')->limit(8)->get(),
            'facebook'  => Video::where('platform', 'facebook')->orderByDesc('published_at')->limit(8)->get(),
            'instagram' => Video::where('platform', 'instagram')->orderByDesc('published_at')->limit(8)->get(),
            'tiktok'    => Video::where('platform', 'tiktok')->orderByDesc('published_at')->limit(8)->get(),
        ];

        return view('home', [
            'latestPublications' => $latestPublications,
            'latestVideos'       => $latestVideos,
            'videosByPlatform'   => $videosByPlatform,
        ]);
    }
}
