<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de récupération de contenu depuis les réseaux sociaux.
 *
 * Plateformes supportées:
 *  - YouTube  : YouTube Data API v3  (YOUTUBE_API_KEY)
 *  - TikTok   : oEmbed public        (aucune clé requise)
 *  - Instagram: Facebook Graph API   (INSTAGRAM_ACCESS_TOKEN)
 *  - Facebook : Facebook Graph API   (FACEBOOK_CLIENT_TOKEN)
 */
final class SocialMediaFetchService
{
    private const YOUTUBE_API_BASE  = 'https://www.googleapis.com/youtube/v3';
    private const TIKTOK_OEMBED_URL = 'https://www.tiktok.com/oembed';
    private const FACEBOOK_GRAPH    = 'https://graph.facebook.com/v18.0';

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Détecte la plateforme à partir d'une URL.
     *
     * @return string|null youtube|tiktok|instagram|facebook|null
     */
    public function detectPlatform(string $url): ?string
    {
        $lower = strtolower($url);

        if (str_contains($lower, 'youtube.com') || str_contains($lower, 'youtu.be')) {
            return 'youtube';
        }
        if (str_contains($lower, 'tiktok.com')) {
            return 'tiktok';
        }
        if (str_contains($lower, 'instagram.com')) {
            return 'instagram';
        }
        if (str_contains($lower, 'facebook.com') || str_contains($lower, 'fb.com') || str_contains($lower, 'fb.watch')) {
            return 'facebook';
        }

        return null;
    }

    /**
     * Récupère les métadonnées d'un contenu social media.
     *
     * @return array{success: bool, platform?: string, title?: string, description?: string,
     *               thumbnail?: string|null, author?: string, published_at?: string|null,
     *               url?: string, embed_html?: string|null, embed_url?: string|null, error?: string}
     */
    public function fetchContent(string $url): array
    {
        $platform = $this->detectPlatform($url);

        return match ($platform) {
            'youtube'   => $this->fetchYouTube($url),
            'tiktok'    => $this->fetchTikTok($url),
            'instagram' => $this->fetchInstagram($url),
            'facebook'  => $this->fetchFacebook($url),
            default     => [
                'success' => false,
                'error'   => 'Plateforme non supportée. Utilisez une URL YouTube, TikTok, Instagram ou Facebook.',
            ],
        };
    }

    // -------------------------------------------------------------------------
    // Plateformes
    // -------------------------------------------------------------------------

    private function fetchYouTube(string $url): array
    {
        $apiKey = config('services.youtube.api_key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Clé API YouTube non configurée (YOUTUBE_API_KEY dans .env).'];
        }

        $videoId = $this->extractYouTubeId($url);
        if ($videoId === null) {
            return ['success' => false, 'error' => 'Impossible d\'extraire l\'ID de la vidéo YouTube depuis cette URL.'];
        }

        try {
            $response = Http::timeout(10)->get(self::YOUTUBE_API_BASE . '/videos', [
                'id'   => $videoId,
                'key'  => $apiKey,
                'part' => 'snippet,contentDetails',
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message') ?? 'Erreur inconnue';
                return ['success' => false, 'error' => 'API YouTube: ' . $errorMsg];
            }

            $items = $response->json('items');
            if (empty($items)) {
                return ['success' => false, 'error' => 'Vidéo YouTube introuvable ou privée.'];
            }

            $snippet   = $items[0]['snippet'];
            $thumbnail = $snippet['thumbnails']['maxres']['url']
                ?? $snippet['thumbnails']['high']['url']
                ?? $snippet['thumbnails']['medium']['url']
                ?? null;

            return [
                'success'      => true,
                'platform'     => 'youtube',
                'title'        => $snippet['title'],
                'description'  => $snippet['description'],
                'thumbnail'    => $thumbnail,
                'author'       => $snippet['channelTitle'],
                'published_at' => substr($snippet['publishedAt'], 0, 16), // YYYY-MM-DDTHH:MM
                'url'          => $url,
                'embed_url'    => 'https://www.youtube.com/embed/' . $videoId,
                'embed_html'   => null,
            ];
        } catch (\Exception $e) {
            Log::error('SocialMediaFetchService YouTube error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur réseau lors de la connexion à l\'API YouTube.'];
        }
    }

    private function fetchTikTok(string $url): array
    {
        try {
            $response = Http::timeout(10)->get(self::TIKTOK_OEMBED_URL, ['url' => $url]);

            if ($response->failed()) {
                return ['success' => false, 'error' => 'Erreur TikTok: impossible de récupérer la vidéo. Vérifiez que l\'URL est publique.'];
            }

            $data = $response->json();

            return [
                'success'      => true,
                'platform'     => 'tiktok',
                'title'        => $data['title'] ?? '',
                'description'  => $data['title'] ?? '',
                'thumbnail'    => $data['thumbnail_url'] ?? null,
                'author'       => $data['author_name'] ?? '',
                'published_at' => null,
                'url'          => $url,
                'embed_url'    => null,
                'embed_html'   => $data['html'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('SocialMediaFetchService TikTok error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur réseau lors de la connexion à TikTok.'];
        }
    }

    private function fetchInstagram(string $url): array
    {
        $accessToken = config('services.instagram.access_token');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Token Instagram non configuré (INSTAGRAM_ACCESS_TOKEN dans .env).'];
        }

        try {
            $response = Http::timeout(10)->get(self::FACEBOOK_GRAPH . '/instagram_oembed', [
                'url'          => $url,
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message') ?? 'Erreur inconnue';
                return ['success' => false, 'error' => 'API Instagram: ' . $errorMsg];
            }

            $data = $response->json();

            return [
                'success'      => true,
                'platform'     => 'instagram',
                'title'        => $data['title'] ?? 'Post Instagram',
                'description'  => '',
                'thumbnail'    => $data['thumbnail_url'] ?? null,
                'author'       => $data['author_name'] ?? '',
                'published_at' => null,
                'url'          => $url,
                'embed_url'    => null,
                'embed_html'   => $data['html'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('SocialMediaFetchService Instagram error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur réseau lors de la connexion à Instagram.'];
        }
    }

    private function fetchFacebook(string $url): array
    {
        $accessToken = config('services.facebook.page_access_token')
            ?: config('services.facebook.client_token');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Token Facebook non configuré (FACEBOOK_PAGE_ACCESS_TOKEN dans .env).'];
        }

        try {
            // Essayer d'abord oembed_post, puis oembed_video
            $response = Http::timeout(10)->get(self::FACEBOOK_GRAPH . '/oembed_post', [
                'url'          => $url,
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                $response = Http::timeout(10)->get(self::FACEBOOK_GRAPH . '/oembed_video', [
                    'url'          => $url,
                    'access_token' => $accessToken,
                ]);
            }

            if ($response->failed()) {
                $errorMsg = $response->json('error.message') ?? 'Erreur inconnue';
                return ['success' => false, 'error' => 'API Facebook: ' . $errorMsg];
            }

            $data = $response->json();

            return [
                'success'      => true,
                'platform'     => 'facebook',
                'title'        => $data['title'] ?? 'Post Facebook',
                'description'  => '',
                'thumbnail'    => $data['thumbnail_url'] ?? null,
                'author'       => $data['author_name'] ?? '',
                'published_at' => null,
                'url'          => $url,
                'embed_url'    => null,
                'embed_html'   => $data['html'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('SocialMediaFetchService Facebook error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur réseau lors de la connexion à Facebook.'];
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function extractYouTubeId(string $url): ?string
    {
        // youtu.be/ID
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }
        // youtube.com/watch?v=ID  (avec éventuels autres paramètres)
        if (preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }
        // youtube.com/shorts/ID
        if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }
        // youtube.com/embed/ID
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
