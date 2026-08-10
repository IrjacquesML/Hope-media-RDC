<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggleLike(Post $post, Request $request): JsonResponse
    {
        return $this->toggle($post, $request, 'liked_posts');
    }

    public function checkLike(Post $post, Request $request): JsonResponse
    {
        return $this->check($post, $request, 'liked_posts');
    }

    public function toggleNewsLike(News $news, Request $request): JsonResponse
    {
        return $this->toggle($news, $request, 'liked_news');
    }

    public function checkNewsLike(News $news, Request $request): JsonResponse
    {
        return $this->check($news, $request, 'liked_news');
    }

    private function toggle(Model $model, Request $request, string $sessionPrefix): JsonResponse
    {
        $sessionKey = $sessionPrefix . '_' . $request->ip();
        $likedIds = session($sessionKey, []);

        if (in_array($model->id, $likedIds, true)) {
            $model->decrement('likes');
            $likedIds = array_values(array_filter($likedIds, fn ($id) => (int) $id !== (int) $model->id));
            $isLiked = false;
            $message = 'Like retiré';
        } else {
            $model->increment('likes');
            $likedIds[] = $model->id;
            $isLiked = true;
            $message = 'Publication aimée !';
        }

        session([$sessionKey => $likedIds]);
        $model->refresh();

        return response()->json([
            'success' => true,
            'likes'   => (int) $model->likes,
            'isLiked' => $isLiked,
            'message' => $message,
        ]);
    }

    private function check(Model $model, Request $request, string $sessionPrefix): JsonResponse
    {
        $sessionKey = $sessionPrefix . '_' . $request->ip();
        $likedIds = session($sessionKey, []);

        return response()->json([
            'isLiked' => in_array($model->id, $likedIds, true),
            'likes'   => (int) $model->likes,
        ]);
    }
}
