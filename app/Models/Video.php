<?php

namespace App\Models;

use App\Enums\VideoPlatform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Video extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     * Les champs de métadonnées sont remplis automatiquement par YouTubeApiService.
     */
    protected $fillable = [
        'title',
        'platform',
        'video_id',
        'post_url',
        // -- Métadonnées synchronisées via API --
        'description',
        'thumbnail_url',
        'channel_id',
        'channel_title',
        'published_at',
        'duration',
        'view_count',
        'likes_count',
        'comments_count',
        'synced_at',
        'raw_metadata',
    ];

    /**
     * Les attributs à caster automatiquement.
     */
    protected $casts = [
        'platform'       => VideoPlatform::class,
        'published_at'   => 'datetime',
        'synced_at'      => 'datetime',
        'view_count'     => 'integer',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'raw_metadata'   => 'array',
    ];

    /**
     * Accessor : URL d'embed (sans autoplay).
     * Accessible via $video->embed_url
     */
    protected function embedUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->platform->embedUrl($this->video_id),
        );
    }

    /**
     * Accessor : URL d'embed avec autoplay=1 (utilisée au click-to-play).
     * Accessible via $video->embed_url_autoplay
     */
    protected function embedUrlAutoplay(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->platform->embedUrl($this->video_id, autoplay: true),
        );
    }

    /**
     * Accessor : miniature — priorité à la valeur stockée (API), sinon calcul à la volée.
     * Accessible via $video->thumbnail_url
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $value ?? $this->platform->thumbnailUrl($this->video_id),
        );
    }

    /**
     * Accessor : indique si la vidéo est en format vertical (portrait).
     * Accessible via $video->is_vertical
     */
    protected function isVertical(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->platform->isVertical(),
        );
    }
}
