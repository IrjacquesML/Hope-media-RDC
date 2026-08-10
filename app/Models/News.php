<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class News extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'author',
        'status',
        'published_at',
        'views',
        'likes',
        'is_featured',
        'category',
        'tags',
        'meta_title',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->orderByDesc('created_at');
    }

    public static function categories(): array
    {
        return [
            'Évangélisation',
            'Projets',
            'Événements',
            'Témoignages',
            'Éducation',
            'Santé',
            'Communauté',
            'International',
        ];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        return \Illuminate\Support\Str::limit(strip_tags($this->content), 160);
    }

    public function setExcerptAttribute(?string $value): void
    {
        // Auto-truncate to 500 chars before saving — prevents DB/validation errors
        $this->attributes['excerpt'] = $value
            ? \Illuminate\Support\Str::limit(trim($value), 500, '')
            : null;
    }
}
