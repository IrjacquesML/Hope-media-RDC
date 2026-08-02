<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VideoPlatform;
use App\Models\SocialFeed;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de synchronisation automatique des posts d'une Page Facebook.
 *
 * Utilise Facebook Graph API v19.0 :
 *   GET /{page-id}/posts?fields=id,message,story,full_picture,permalink_url,
 *                              created_time,attachments{media,type,title,description}
 *                        &access_token={page-access-token}
 *
 * Prérequis :
 *   - Une Page Facebook gérée par votre compte
 *   - Un Page Access Token (long-lived) avec la permission pages_read_engagement
 *   - Variables .env : FACEBOOK_PAGE_ID, FACEBOOK_PAGE_ACCESS_TOKEN
 *
 * Note : seuls les posts publics de type vidéo / photo / lien sont synchronisés.
 */
final class FacebookSyncService
{
    private const GRAPH_BASE = 'https://graph.facebook.com/v19.0';

    public function __construct(
        private readonly string $pageId,
        private readonly string $pageAccessToken,
        private readonly int    $maxResults = 20,
    ) {}

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les derniers posts de la Page et les persiste en base.
     *
     * @return int Nombre de posts créés ou mis à jour.
     */
    public function syncLatestPosts(): int
    {
        $posts = $this->fetchPosts();

        $synced = 0;
        foreach ($posts as $post) {
            $this->upsertPost($post);
            $synced++;
        }

        return $synced;
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — appels API
    // -------------------------------------------------------------------------

    /**
     * Récupère les posts depuis l'API Graph Facebook.
     *
     * @return array<int, array>
     */
    private function fetchPosts(): array
    {
        try {
            $response = Http::timeout(15)->get(self::GRAPH_BASE . "/{$this->pageId}/posts", [
                'fields'       => 'id,message,story,full_picture,permalink_url,created_time,'
                    . 'attachments{media,type,title,description,url}',
                'limit'        => $this->maxResults,
                'access_token' => $this->pageAccessToken,
            ]);

            if ($response->failed()) {
                $error = $response->json('error.message') ?? $response->body();
                Log::error('FacebookSyncService: erreur API.', ['error' => $error]);
                throw new \RuntimeException("Facebook Graph API error: {$error}");
            }

            return $response->json('data') ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('FacebookSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — persistance
    // -------------------------------------------------------------------------

    /**
     * Crée ou met à jour un enregistrement Video à partir d'un post Facebook.
     *
     * L'identifiant unique est l'ID du post Facebook (format: pageId_postId).
     */
    private function upsertPost(array $post): void
    {
        $postId      = $post['id'];                         // ex: "123456789_987654321"
        $message     = $post['message'] ?? $post['story'] ?? 'Post Facebook';
        $title       = $this->extractTitle($message);
        $thumbnail   = $post['full_picture'] ?? null;
        $permalink   = $post['permalink_url'] ?? null;
        $publishedAt = isset($post['created_time'])
            ? Carbon::parse($post['created_time'])
            : null;

        // Cherche une thumbnail dans les pièces jointes si absente du post principal
        if (! $thumbnail) {
            $thumbnail = $this->extractThumbnailFromAttachments($post['attachments']['data'] ?? []);
        }

        // Détermine le type de média depuis les pièces jointes du post
        $mediaType = $this->detectMediaType($post['attachments']['data'] ?? []);

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::Facebook->value, 'platform_id' => $postId],
            [
                'media_type'    => $mediaType,
                'title'         => $title,
                'body'          => $message,
                'thumbnail_url' => $thumbnail,
                'post_url'      => $permalink,
                'source_id'     => $this->pageId,
                'source_name'   => null,
                'published_at'  => $publishedAt,
                'synced_at'     => now(),
                'raw_data'      => $post,
            ]
        );

        Video::updateOrCreate(
            ['platform' => VideoPlatform::Facebook->value, 'video_id' => $postId],
            [
                'title'          => $title,
                'description'    => $message,
                'thumbnail_url'  => $thumbnail,
                'post_url'       => $permalink,
                'channel_id'     => $this->pageId,
                'channel_title'  => 'Facebook',
                'published_at'   => $publishedAt,
                'duration'       => null,
                'view_count'     => 0,
                'likes_count'    => 0,
                'comments_count' => 0,
                'synced_at'      => now(),
                'raw_metadata'   => $post,
            ]
        );
    }

    /**
     * Déduit le type de média (video|image|carousel|text) depuis les pièces jointes.
     */
    private function detectMediaType(array $attachments): string
    {
        if (empty($attachments)) {
            return 'text';
        }

        $type = strtolower($attachments[0]['type'] ?? '');

        return match (true) {
            str_contains($type, 'video')  => 'video',
            str_contains($type, 'photo')  => 'image',
            str_contains($type, 'album')  => 'carousel',
            default                       => 'text',
        };
    }

    /**
     * Extrait un titre court depuis le message du post (première phrase ou 80 chars).
     */
    private function extractTitle(string $message): string
    {
        // Première ligne non vide
        $lines = array_filter(explode("\n", trim($message)));
        $first = reset($lines) ?: $message;

        return mb_strlen($first) > 80
            ? mb_substr($first, 0, 77) . '...'
            : $first;
    }

    /**
     * Cherche l'URL d'image dans les pièces jointes d'un post.
     */
    private function extractThumbnailFromAttachments(array $attachments): ?string
    {
        foreach ($attachments as $attachment) {
            if (isset($attachment['media']['image']['src'])) {
                return $attachment['media']['image']['src'];
            }
        }

        return null;
    }
}
