<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

/**
 * Modèle Category
 * 
 * Représente une catégorie d'enseignement ou de service
 * 
 * Relations:
 * - hasMany: posts
 */
class Category extends Model
{
    use HasFactory, Sluggable;

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'icon',
        'color',
        'order',
        'is_active',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'is_active' => 'boolean',
        'type' => 'string',
    ];

    /**
     * Configuration des slugs automatiques
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Une catégorie a plusieurs posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class)
            ->where('status', 'published')
            ->orderBy('published_at', 'desc');
    }

    /**
     * Tous les posts, y compris les brouillons
     */
    public function allPosts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Récupérer les catégories actives triées par ordre
     */
    public static function active()
    {
        return self::where('is_active', true)->orderBy('order');
    }

    /**
     * Récupérer le nombre de posts publiés
     */
    public function publishedPostsCount()
    {
        return $this->posts()->count();
    }
}
