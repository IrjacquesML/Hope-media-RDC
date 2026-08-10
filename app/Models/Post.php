<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

/**
 * Modèle Post
 * 
 * Représente un enseignement (texte, vidéo, audio, image)
 * 
 * Relations:
 * - belongsTo: category
 * - hasMany: medias
 */
class Post extends Model
{
    use HasFactory, Sluggable;

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'content_type',
        'featured_image',
        'author',
        'author_email',
        'status',
        'published_at',
        'teaching_date',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views',
        'likes',
        'order',
        'is_featured',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'published_at' => 'datetime',
        'teaching_date' => 'date',
        'is_featured' => 'boolean',
        'content_type' => 'string',
    ];

    /**
     * Configuration des slugs automatiques
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    /**
     * Un post appartient à une catégorie
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Un post a plusieurs médias
     */
    public function medias()
    {
        return $this->hasMany(Media::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Un post a plusieurs commentaires
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->orderByDesc('created_at');
    }

    /**
     * Récupérer les posts publiés, triés par date décroissante
     */
    public static function published()
    {
        return self::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');
    }

    /**
     * Récupérer les posts en vedette
     */
    public static function featured()
    {
        return self::published()
            ->where('is_featured', true)
            ->limit(6);
    }

    /**
     * Récupérer par slug de manière sécurisée
     */
    public static function findBySlug($slug)
    {
        return self::published()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Incrémenter le compteur de vues
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Formater la date et l'heure de publication
     */
    public function getFormattedDateAttribute()
    {
        return $this->published_at?->format('d M Y \à H:i');
    }

    /**
     * Déterminer l'icône selon le type de contenu
     */
    public function getContentIconAttribute()
    {
        return match ($this->content_type) {
            'video' => 'play-circle',
            'audio' => 'music',
            'image' => 'image',
            default => 'file-text',
        };
    }

    /**
     * Obtenir le label du type de contenu avec icône
     */
    public function getContentTypeWithIconAttribute()
    {
        $icons = [
            'text' => 'fas fa-file-alt',
            'video' => 'fas fa-video',
            'audio' => 'fas fa-music',
            'image' => 'fas fa-image',
        ];
        
        $labels = [
            'text' => 'Texte',
            'video' => 'Vidéo',
            'audio' => 'Audio',
            'image' => 'Image',
        ];

        return [
            'icon' => $icons[$this->content_type] ?? 'fas fa-file',
            'label' => $labels[$this->content_type] ?? ucfirst($this->content_type),
        ];
    }

    /**
     * Récupérer l'URL publique du post
     */
    public function getUrlAttribute()
    {
        return route('posts.show', $this->slug);
    }
}
