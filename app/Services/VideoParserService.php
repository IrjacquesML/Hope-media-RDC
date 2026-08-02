<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ParsedVideo;
use App\Enums\VideoPlatform;

/**
 * Service de parsing d'URLs vidéo.
 *
 * Stratégie anti-ReDoS :
 * - On délègue le découpage de l'URL à parse_url() (C natif, pas de regex).
 * - Les regex ne s'appliquent plus que sur des segments courts et bornés
 *   (chemin ou valeur d'un paramètre), éliminant tout risque de backtracking
 *   catastrophique.
 * - Les quantificateurs possessifs (++, *+) sont utilisés dès qu'un segment
 *   peut être long, pour interdire le backtracking interne à PCRE.
 */
final class VideoParserService
{
    // -------------------------------------------------------------------------
    // Constantes de validation des identifiants
    // -------------------------------------------------------------------------

    /** Longueur exacte d'un ID vidéo YouTube (toujours 11 caractères). */
    private const YOUTUBE_ID_PATTERN = '[a-zA-Z0-9_-]{11}';

    /** ID numérique TikTok (jusqu'à 25 chiffres pour les IDs récents). */
    private const TIKTOK_ID_PATTERN  = '\d{1,25}';

    /** Code court des liens vm.tiktok.com (alphanumérique, 5-20 chars). */
    private const TIKTOK_SHORT_PATTERN = '[a-zA-Z0-9]{5,20}';

    /** ID numérique Facebook (jusqu'à 25 chiffres). */
    private const FACEBOOK_ID_PATTERN = '\d{1,25}';

    /** Code court des liens fb.watch (alphanumérique + tiret, 4-30 chars). */
    private const FACEBOOK_SHORT_PATTERN = '[a-zA-Z0-9_-]{4,30}';

    // -------------------------------------------------------------------------
    // Point d'entrée public
    // -------------------------------------------------------------------------

    /**
     * Analyse une URL brute et retourne un DTO typé ou null si non reconnue.
     *
     * @param  string       $url  URL brute soumise par l'utilisateur.
     * @return ParsedVideo|null   DTO contenant platform + video_id, ou null.
     */
    public function parse(string $url): ?ParsedVideo
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // parse_url() fait le vrai travail de découpage (pas de regex ici).
        $components = parse_url($url);

        // URL structurellement invalide ou sans hôte.
        if ($components === false || empty($components['host'])) {
            return null;
        }

        // Normalisation : minuscules, suppression du préfixe "www.".
        $host  = strtolower((string) $components['host']);
        $host  = preg_replace('/^www\./u', '', $host) ?? $host;
        $path  = $components['path'] ?? '';
        $query = $components['query'] ?? '';

        return match (true) {
            $this->isYouTubeHost($host)  => $this->parseYouTube($host, $path, $query),
            $this->isTikTokHost($host)   => $this->parseTikTok($host, $path, $query),
            $this->isFacebookHost($host) => $this->parseFacebook($host, $path, $query),
            default                      => null,
        };
    }

    // -------------------------------------------------------------------------
    // Détection des hôtes
    // -------------------------------------------------------------------------

    private function isYouTubeHost(string $host): bool
    {
        return $host === 'youtube.com' || $host === 'youtu.be';
    }

    private function isTikTokHost(string $host): bool
    {
        return $host === 'tiktok.com' || $host === 'vm.tiktok.com';
    }

    private function isFacebookHost(string $host): bool
    {
        return $host === 'facebook.com' || $host === 'fb.watch';
    }

    // -------------------------------------------------------------------------
    // Parsers par plateforme
    // -------------------------------------------------------------------------

    /**
     * Parse une URL YouTube.
     *
     * Formats gérés :
     *  - youtube.com/watch?v=VIDEO_ID
     *  - youtube.com/shorts/VIDEO_ID
     *  - youtube.com/embed/VIDEO_ID
     *  - youtube.com/v/VIDEO_ID
     *  - youtube.com/live/VIDEO_ID
     *  - youtu.be/VIDEO_ID
     */
    private function parseYouTube(string $host, string $path, string $query): ?ParsedVideo
    {
        $pattern = self::YOUTUBE_ID_PATTERN;

        // --- Lien court : youtu.be/VIDEO_ID ---
        if ($host === 'youtu.be') {
            // Le chemin vaut exactement /VIDEO_ID (+ éventuel slug ignoré).
            if (preg_match("~^/({$pattern})(?:[/?#]|$)~", $path, $m)) {
                return new ParsedVideo(VideoPlatform::YouTube, $m[1]);
            }
            return null;
        }

        // --- youtube.com ---

        // /watch?v= : on utilise parse_str pour éviter tout regex sur la query.
        if (str_starts_with($path, '/watch')) {
            parse_str($query, $params);
            $videoId = $params['v'] ?? '';
            if (preg_match("~^{$pattern}$~", $videoId)) {
                return new ParsedVideo(VideoPlatform::YouTube, $videoId);
            }
            return null;
        }

        // /shorts/, /embed/, /v/, /live/ : chemin structuré, regex simple.
        if (preg_match("~^/(?:shorts|embed|v|live)/({$pattern})(?:[/?#]|$)~", $path, $m)) {
            return new ParsedVideo(VideoPlatform::YouTube, $m[1]);
        }

        return null;
    }

    /**
     * Parse une URL TikTok.
     *
     * Formats gérés :
     *  - tiktok.com/@username/video/VIDEO_ID
     *  - tiktok.com/embed/v2/VIDEO_ID
     *  - vm.tiktok.com/SHORTCODE   (lien de partage mobile, code extrait tel quel)
     */
    private function parseTikTok(string $host, string $path, string $query): ?ParsedVideo
    {
        // --- Lien court mobile : vm.tiktok.com/SHORTCODE ---
        if ($host === 'vm.tiktok.com') {
            $short = self::TIKTOK_SHORT_PATTERN;
            if (preg_match("~^/({$short})/?$~", $path, $m)) {
                return new ParsedVideo(VideoPlatform::TikTok, $m[1]);
            }
            return null;
        }

        // --- tiktok.com ---

        $id = self::TIKTOK_ID_PATTERN;

        // /@username/video/VIDEO_ID
        // Le segment username est capturé de manière possessive pour éviter
        // tout backtracking sur des usernames longs ou malformés.
        if (preg_match("~^/@[^/]++/video/({$id})(?:[/?#]|$)~", $path, $m)) {
            return new ParsedVideo(VideoPlatform::TikTok, $m[1]);
        }

        // /embed/v2/VIDEO_ID
        if (preg_match("~^/embed/v2/({$id})(?:[/?#]|$)~", $path, $m)) {
            return new ParsedVideo(VideoPlatform::TikTok, $m[1]);
        }

        return null;
    }

    /**
     * Parse une URL Facebook.
     *
     * Formats gérés :
     *  - facebook.com/watch?v=VIDEO_ID
     *  - facebook.com/watch/?v=VIDEO_ID
     *  - facebook.com/video.php?v=VIDEO_ID
     *  - facebook.com/username/videos/VIDEO_ID
     *  - fb.watch/SHORTCODE
     */
    private function parseFacebook(string $host, string $path, string $query): ?ParsedVideo
    {
        // --- Lien court : fb.watch/SHORTCODE ---
        if ($host === 'fb.watch') {
            $short = self::FACEBOOK_SHORT_PATTERN;
            if (preg_match("~^/({$short})/?$~", $path, $m)) {
                return new ParsedVideo(VideoPlatform::Facebook, $m[1]);
            }
            return null;
        }

        // --- facebook.com ---

        $id = self::FACEBOOK_ID_PATTERN;

        // /watch ou /watch/ avec ?v= (parse_str pour la query)
        if (preg_match('~^/watch/?$~', $path)) {
            parse_str($query, $params);
            $videoId = $params['v'] ?? '';
            if (preg_match("~^{$id}$~", $videoId)) {
                return new ParsedVideo(VideoPlatform::Facebook, $videoId);
            }
            return null;
        }

        // /video.php?v=
        if ($path === '/video.php') {
            parse_str($query, $params);
            $videoId = $params['v'] ?? '';
            if (preg_match("~^{$id}$~", $videoId)) {
                return new ParsedVideo(VideoPlatform::Facebook, $videoId);
            }
            return null;
        }

        // /username/videos/VIDEO_ID
        // Le segment username est capturé de manière possessive.
        if (preg_match("~^/[^/]++/videos/({$id})(?:[/?#]|$)~", $path, $m)) {
            return new ParsedVideo(VideoPlatform::Facebook, $m[1]);
        }

        return null;
    }
}
