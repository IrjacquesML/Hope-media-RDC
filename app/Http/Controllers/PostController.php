<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * PostController
 * 
 * Contrôleur pour l'affichage public des posts
 * Gère la liste des posts et l'affichage détaillé
 */
class PostController extends Controller
{
    /**
     * Afficher la liste paginée des posts publiés
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $posts = Post::published()
            ->withCount('comments')
            ->paginate(12);

        return view('posts.index', [
            'posts' => $posts,
            'title' => 'Tous les enseignements',
        ]);
    }

    /**
     * Afficher un post spécifique par slug
     * 
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $post = Post::findBySlug($slug);
        
        // Incrémenter le compteur de vues
        $post->incrementViews();
        $post->load(['comments.replies']);

        // Récupérer les posts associés (même catégorie)
        $relatedPosts = $post->category
            ? $post->category->posts()->where('id', '!=', $post->id)->limit(3)->get()
            : collect();

        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    /**
     * Afficher les posts d'une catégorie spécifique
     * 
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function category($slug)
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = $category->posts()
            ->withCount('comments')
            ->paginate(12);

        return view('posts.category', [
            'category' => $category,
            'posts' => $posts,
        ]);
    }

    /**
     * Rechercher des posts
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 3) {
            return redirect()->route('posts.index')
                ->with('error', 'Veuillez entrer au moins 3 caractères');
        }

        $posts = Post::published()
            ->withCount('comments')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->paginate(12);

        return view('posts.search', [
            'posts' => $posts,
            'query' => $query,
        ]);
    }

    /**
     * Afficher les posts d'un type spécifique (video, audio, text, image)
     * 
     * @param string $type
     * @return \Illuminate\View\View
     */
    public function byType($type)
    {
        $validTypes = ['text', 'video', 'audio', 'image'];

        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $posts = Post::published()
            ->withCount('comments')
            ->where('content_type', $type)
            ->paginate(12);

        $typeLabels = [
            'text' => 'Lectures',
            'video' => 'Vidéos',
            'audio' => 'Audios',
            'image' => 'Images',
        ];

        return view('posts.by-type', [
            'posts' => $posts,
            'type' => $type,
            'typeLabel' => $typeLabels[$type],
        ]);
    }
}
