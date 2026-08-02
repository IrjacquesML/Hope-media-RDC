<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VideoPlatform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent pour la table social_feeds.
 *
 * Représente une publication synchronisée depuis une plateforme sociale
 * (YouTube, Facebook, Instagram ou TikTok).
 *
 * @property int                         $id
 * @property string                      $platform       youtube|facebook|instagram|tiktok
 * @property string                      $platform_id    ID natif sur la plateforme
 * @property string                      $media_type     video|image|carousel|text|short|reel
 * @property string|null                 $title
 * @property string|null                 $body           description / caption / message
 * @property string|null                 $thumbnail_url
 * @property string|null                 $media_url      URL CDN directe (Instagram…)
 * @property string|null                 $post_url       Lien permanent vers le post
 * @property string|null                 $source_id      channelId / pageId / igUserId…
 * @property string|null                 $source_name    Nom de la chaîne / page / compte
 * @property string|null                 $duration       ISO 8601 ou secondes
 * @property int                         $view_count
 * @property int                         $likes_count
 * @property int                         $comments_count
 * @property int                         $shares_count
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $synced_at
 * @property array|null                  $raw_data
 * @property \Illuminate\Support\Carbon  $created_at
 * @property \Illuminate\Support\Carbon  $updated_at
 */
final class SocialFeed extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'social_feeds';

    /** @var list<string> */
    protected $fillable = [
        'platform',
        'platform_id',
        'media_type',
        'title',
        'body',
        'thumbnail_url',
        'media_url',
        'post_url',
        'source_id',
        'source_name',
        'duration',
        'view_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'published_at',
        'synced_at',
        'raw_data',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'view_count'     => 'integer',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'shares_count'   => 'integer',
        'published_at'   => 'datetime',
        'synced_at'      => 'datetime',
        'raw_data'       => 'array',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * URL d'embed iFrame (sans autoplay).
     * Exemple : https://www.youtube.com/embed/dQw4w9WgXcQ?rel=0
     *
     * Accessible via $feed->embed_url
     */
    protected function embedUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $platform = VideoPlatform::tryFrom($this->platform);
                return $platform?->embedUrl($this->platform_id);
            }
        );
    }

    /**
     * URL d'embed avec autoplay (pour le click-to-play).
     * Accessible via $feed->embed_url_autoplay
     */
    protected function embedUrlAutoplay(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $platform = VideoPlatform::tryFrom($this->platform);
                return $platform?->embedUrl($this->platform_id, autoplay: true);
            }
        );
    }

    /**
     * Libellé lisible de la plateforme (ex : "YouTube", "TikTok").
     * Accessible via $feed->platform_label
     */
    protected function platformLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                return VideoPlatform::tryFrom($this->platform)?->label()
                    ?? ucfirst($this->platform);
            }
        );
    }

    /**
     * Indique si le contenu est au format vertical (portrait).
     * Utile pour appliquer des classes CSS différentes.
     * Accessible via $feed->is_vertical
     */
    protected function isVertical(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => in_array($this->media_type, ['short', 'reel'], true)
                || $this->platform === VideoPlatform::TikTok->value
                || $this->platform === VideoPlatform::Instagram->value,
        );
    }

    // -------------------------------------------------------------------------
    // Scopes de requête
    // -------------------------------------------------------------------------

    /**
     * Filtre par plateforme.
     *
     * Usage : SocialFeed::platform('youtube')->latest('published_at')->get()
     */
    public function scopePlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /**
     * Filtre par type de média.
     *
     * Usage : SocialFeed::mediaType('video')->get()
     */
    public function scopeMediaType(Builder $query, string $type): Builder
    {
        return $query->where('media_type', $type);
    }

    /**
     * Retourne uniquement les contenus vidéo (tous formats : video, short, reel).
     *
     * Usage : SocialFeed::videos()->latest('published_at')->get()
     */
    public function scopeVideos(Builder $query): Builder
    {
        return $query->whereIn('media_type', ['video', 'short', 'reel']);
    }

    /**
     * Retourne les enregistrements dont la synchro date de plus de $hours heures.
     * Utile pour identifier les contenus à re-synchroniser.
     *
     * Usage : SocialFeed::staleAfter(6)->get()
     */
    public function scopeStaleAfter(Builder $query, int $hours = 6): Builder
    {
        return $query->where(function (Builder $q) use ($hours) {
            $q->whereNull('synced_at')
              ->orWhere('synced_at', '<', now()->subHours($hours));
        });
    }

    /**
     * Ordonne par date de publication décroissante (fil d'actualité).
     *
     * Usage : SocialFeed::latest('published_at')->paginate(12)
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }
}
