<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Resource extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'author',
        'publisher',
        'category',
        'published_year',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'cover_image',
        'status',
        'downloads',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured'  => 'boolean',
        'file_size'    => 'integer',
        'downloads'    => 'integer',
    ];

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

    /**
     * Taille du fichier formatée (ex: 2.5 Mo)
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' Mo';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' Ko';
        }
        return $bytes . ' octets';
    }

    public static function categoryLabels(): array
    {
        return [
            'livre'     => 'Livre',
            'ouvrage'   => 'Ouvrage',
            'manuel'    => 'Manuel',
            'brochure'  => 'Brochure',
            'autre'     => 'Autre',
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryLabels()[$this->category] ?? ucfirst($this->category);
    }
}
