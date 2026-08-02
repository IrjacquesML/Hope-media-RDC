<?php

namespace App\Helpers;

class MediaHelper
{
    /**
     * Détecte le type de source média à partir d'une URL
     * Retourne: youtube, vimeo, dailymotion, local, etc.
     */
    public static function getMediaSourceType($url)
    {
        if (!$url) {
            return 'local';
        }

        $url = strtolower($url);

        // YouTube
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'youtube';
        }

        // Vimeo
        if (strpos($url, 'vimeo.com') !== false) {
            return 'vimeo';
        }

        // Dailymotion
        if (strpos($url, 'dailymotion.com') !== false || strpos($url, 'dai.ly') !== false) {
            return 'dailymotion';
        }

        // TikTok / Instagram / Facebook
        if (strpos($url, 'tiktok.com') !== false) {
            return 'tiktok';
        }
        if (strpos($url, 'instagram.com') !== false) {
            return 'instagram';
        }
        if (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false) {
            return 'facebook';
        }

        // Fichiers locaux
        if (strpos($url, '/storage/') !== false) {
            return 'local';
        }

        return 'external';
    }

    /**
     * Retourne l'icône Font Awesome pour un type de contenu
     */
    public static function getContentIcon($contentType)
    {
        $icons = [
            'text' => 'fas fa-file-alt',
            'video' => 'fas fa-video',
            'audio' => 'fas fa-music',
            'image' => 'fas fa-image',
        ];

        return $icons[$contentType] ?? 'fas fa-file';
    }

    /**
     * Retourne l'étiquette pour un type de source média
     */
    public static function getSourceLabel($mediaType)
    {
        $labels = [
            'youtube' => '▶️ YouTube',
            'vimeo' => '▶️ Vimeo',
            'dailymotion' => '▶️ Dailymotion',
            'tiktok' => '▶️ TikTok',
            'instagram' => '📷 Instagram',
            'facebook' => '📘 Facebook',
            'local' => '💾 Local',
            'external' => '🌐 Externe',
        ];

        return $labels[$mediaType] ?? $mediaType;
    }

    /**
     * Convertit une durée ISO 8601 (ex: PT4M13S) en format lisible (4:13).
     * Retourne null si la durée est invalide ou nulle.
     */
    public static function formatDuration(?string $isoDuration): ?string
    {
        if (! $isoDuration) {
            return null;
        }

        if (! preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $isoDuration, $m)) {
            return null;
        }

        $hours   = (int) ($m[1] ?? 0);
        $minutes = (int) ($m[2] ?? 0);
        $seconds = (int) ($m[3] ?? 0);

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
