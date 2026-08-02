<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Media
 * 
 * Représente un fichier média (vidéo, audio, image)
 * Supporte les fichiers locaux et les URLs externes (YouTube, Vimeo)
 * 
 * Relations:
 * - belongsTo: post
 */
class Media extends Model
{
    use HasFactory;

    /**
     * Le nom de la table
     */
    protected $table = 'medias';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'post_id',
        'media_type',
        'title',
        'description',
        'url',
        'duration',
        'file_size',
        'mime_type',
        'thumbnail',
        'order',
        'is_active',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Un média appartient à un post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Récupérer les médias actifs
     */
    public static function active()
    {
        return self::where('is_active', true);
    }

    /**
     * Vérifier si c'est une vidéo YouTube
     */
    public function isYouTube(): bool
    {
        return $this->media_type === 'youtube';
    }

    /**
     * Vérifier si c'est une vidéo Vimeo
     */
    public function isVimeo(): bool
    {
        return $this->media_type === 'vimeo';
    }

    /**
     * Vérifier si c'est une vidéo Dailymotion
     */
    public function isDailyMotion(): bool
    {
        return $this->media_type === 'dailymotion';
    }

    /**
     * Vérifier si c'est un fichier local
     */
    public function isLocal(): bool
    {
        return $this->media_type === 'local';
    }

    /**
     * Récupérer l'URL d'embed pour YouTube
     */
    public function getYouTubeEmbedUrl(): string
    {
        $videoId = $this->extractYouTubeVideoId($this->url);
        return "https://www.youtube.com/embed/{$videoId}?rel=0";
    }

    /**
     * Extraire l'ID vidéo YouTube d'une URL
     */
    private function extractYouTubeVideoId($url): string
    {
        // Si c'est déjà juste un ID
        if (strlen($url) === 11 && preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        // Format: https://www.youtube.com/watch?v=dQw4w9WgXcQ
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return $url;
    }

    /**
     * Récupérer l'URL d'embed pour Vimeo
     */
    public function getVimeoEmbedUrl(): string
    {
        $videoId = $this->extractVimeoVideoId($this->url);
        return "https://player.vimeo.com/video/{$videoId}";
    }

    /**
     * Extraire l'ID vidéo Vimeo d'une URL
     */
    private function extractVimeoVideoId($url): string
    {
        // Si c'est déjà juste un ID
        if (is_numeric($url)) {
            return $url;
        }

        // Format: https://vimeo.com/123456789
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return $url;
    }

    /**
     * Récupérer l'URL du fichier local
     */
    public function getLocalUrl(): string
    {
        return asset("storage/{$this->url}");
    }

    /**
     * Récupérer l'URL appropriée selon le type
     */
    public function getEmbedUrl(): string
    {
        return match ($this->media_type) {
            'youtube' => $this->getYouTubeEmbedUrl(),
            'vimeo' => $this->getVimeoEmbedUrl(),
            'dailymotion' => $this->getDailyMotionEmbedUrl(),
            default => $this->getLocalUrl(),
        };
    }

    /**
     * Récupérer l'URL d'embed pour Dailymotion
     */
    public function getDailyMotionEmbedUrl(): string
    {
        $videoId = $this->extractDailyMotionVideoId($this->url);
        return "https://www.dailymotion.com/embed/video/{$videoId}";
    }

    /**
     * Extraire l'ID vidéo Dailymotion d'une URL
     */
    private function extractDailyMotionVideoId($url): string
    {
        // Format: https://www.dailymotion.com/video/x123456
        if (preg_match('/dailymotion\.com\/video\/(x[a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Format court: https://dai.ly/x123456
        if (preg_match('/dai\.ly\/(x[a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        return $url;
    }

    /**
     * Récupérer l'URL du thumbnail
     */
    public function getThumbnailUrl(): string
    {
        if ($this->thumbnail) {
            return asset("storage/{$this->thumbnail}");
        }

        // Thumbnail par défaut selon le type
        $videoId = match ($this->media_type) {
            'youtube' => $this->extractYouTubeVideoId($this->url),
            default => null,
        };

        if ($this->media_type === 'youtube' && $videoId) {
            return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        }

        return asset('images/no-image.png');
    }

    /**
     * Formater la durée en format HH:MM:SS
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Formater la taille du fichier
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
