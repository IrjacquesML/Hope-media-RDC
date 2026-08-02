<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Liker ou disliker un post via AJAX
     * 
     * @param Post $post
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLike(Post $post, Request $request)
    {
        // Vérifier si l'utilisateur a déjà liké ce post
        // Utiliser l'IP de l'utilisateur ou la session pour tracker les likes
        $clientIp = $request->ip();
        $sessionKey = "liked_posts_{$clientIp}";
        
        // Récupérer les posts likés de la session/cookie
        $likedPosts = session($sessionKey, []);
        
        if (in_array($post->id, $likedPosts)) {
            // Disliker - retirer le like
            $post->decrement('likes');
            $likedPosts = array_filter($likedPosts, function ($id) use ($post) {
                return $id !== $post->id;
            });
            $isLiked = false;
            $message = 'Like retiré';
        } else {
            // Liker - ajouter le like
            $post->increment('likes');
            $likedPosts[] = $post->id;
            $isLiked = true;
            $message = 'Publication aimée!';
        }
        
        // Sauvegarder l'état des likes dans la session
        session([$sessionKey => $likedPosts]);
        
        return response()->json([
            'success' => true,
            'likes' => $post->likes,
            'isLiked' => $isLiked,
            'message' => $message,
        ]);
    }

    /**
     * Vérifier si l'utilisateur a liké un post
     * 
     * @param Post $post
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLike(Post $post, Request $request)
    {
        $clientIp = $request->ip();
        $sessionKey = "liked_posts_{$clientIp}";
        $likedPosts = session($sessionKey, []);
        $isLiked = in_array($post->id, $likedPosts);

        return response()->json([
            'isLiked' => $isLiked,
            'likes' => $post->likes,
        ]);
    }
}
