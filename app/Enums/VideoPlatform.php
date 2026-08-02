<?php

namespace App\Enums;

enum VideoPlatform: string
{
    case YouTube   = 'youtube';
    case TikTok    = 'tiktok';
    case Facebook  = 'facebook';
    case Instagram = 'instagram';

    /**
     * Retourne le libellé lisible pour l'affichage.
     */
    public function label(): string
    {
        return match($this) {
            self::YouTube   => 'YouTube',
            self::TikTok    => 'TikTok',
            self::Facebook  => 'Facebook',
            self::Instagram => 'Instagram',
        };
    }

    /**
     * Retourne l'URL d'embed à partir de l'identifiant de la vidéo.
     *
     * @param bool $autoplay Ajoute autoplay=1 (utilisé lors du click-to-play).
     */
    public function embedUrl(string $videoId, bool $autoplay = false): string
    {
        $auto = $autoplay ? '&autoplay=1' : '';

        return match($this) {
            self::YouTube   => 'https://www.youtube.com/embed/' . $videoId
                . '?rel=0' . ($autoplay ? '&autoplay=1' : ''),
            self::TikTok    => 'https://www.tiktok.com/embed/v2/' . $videoId,
            self::Facebook  => 'https://www.facebook.com/plugins/video.php?href='
                . rawurlencode('https://www.facebook.com/facebook/videos/' . $videoId . '/')
                . '&show_text=false' . ($autoplay ? '&autoplay=true' : ''),
            self::Instagram => 'https://www.instagram.com/p/' . $videoId . '/embed/',
        };
    }

    /**
     * Retourne l'URL de la miniature (thumbnail) de la vidéo.
     *
     * - YouTube : miniature publique servie par YouTube (aucune clé API requise).
     * - TikTok / Facebook : null (pas d'API publique sans authentification).
     */
    public function thumbnailUrl(string $videoId): ?string
    {
        return match($this) {
            self::YouTube  => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg',
            default        => null, // thumbnail récupérée via API et stockée dans thumbnail_url
        };
    }

    /**
     * Indique si la vidéo est au format vertical (portrait).
     */
    public function isVertical(): bool
    {
        return $this === self::TikTok || $this === self::Instagram;
    }

    /**
     * Retourne toutes les valeurs possibles (utile pour la validation).
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
