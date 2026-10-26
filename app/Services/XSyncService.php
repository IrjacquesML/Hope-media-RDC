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
 * Service de synchronisation des vidéos publiées sur X (ex-Twitter).
 *
 * Utilise X API v2 avec un Bearer Token (authentification d'application) :
 *   GET https://api.x.com/2/users/by/username/{username}
 *   GET https://api.x.com/2/users/{id}/tweets?exclude=retweets,replies
 *        &expansions=attachments.media_keys
 *        &tweet.fields=created_at,public_metrics,attachments
 *        &media.fields=type,preview_image_url,duration_ms,public_metrics
 *
 * Seules les publications contenant une vidéo (ou un GIF animé) sont importées.
 * La lecture des publications d'un compte nécessite l'offre « Basic » ou supérieure.
 *
 * Documentation : https://docs.x.com/x-api/users/user-posts-timeline-by-user-id
 */
final class XSyncService
{
    private const API_BASE = 'https://api.x.com/2';

    public function __construct(
        private readonly string $bearerToken,
        private readonly string $username,
        private readonly int    $maxResults = 10,
    ) {}

    // -------------------------------------------------------------------------
    // API publique
    // -------------------------------------------------------------------------

    /**
     * Récupère les dernières publications vidéo du compte et les persiste en base.
     *
     * @return int Nombre de vidéos créées ou mises à jour.
     */
    public function syncLatestPosts(): int
    {
        $user = $this->user();

        $response = $this->request("/users/{$user['id']}/tweets", [
            'max_results'  => min(max($this->maxResults, 5), 100),   // X : entre 5 et 100
            'exclude'      => 'retweets,replies',
            'expansions'   => 'attachments.media_keys',
            'tweet.fields' => 'created_at,public_metrics,attachments',
            'media.fields' => 'type,preview_image_url,duration_ms,public_metrics',
        ]);

        $media = [];
        foreach ($response['includes']['media'] ?? [] as $m) {
            $media[$m['media_key']] = $m;
        }

        $synced = 0;
        foreach ($response['data'] ?? [] as $tweet) {
            $video = null;
            foreach ($tweet['attachments']['media_keys'] ?? [] as $key) {
                if (in_array($media[$key]['type'] ?? '', ['video', 'animated_gif'], true)) {
                    $video = $media[$key];
                    break;
                }
            }
            if ($video) {
                $this->upsertPost($tweet, $video, $user);
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Vérifie le Bearer Token et le compte, sans rien enregistrer.
     *
     * @return string Nom du compte (Nom (@username)).
     */
    public function testConnection(): string
    {
        $user = $this->user();

        return "{$user['name']} (@{$user['username']})";
    }

    // -------------------------------------------------------------------------
    // Méthodes privées
    // -------------------------------------------------------------------------

    /** @return array{id: string, name: string, username: string} */
    private function user(): array
    {
        $username = ltrim(trim($this->username), '@');

        return $this->request("/users/by/username/{$username}", [])['data']
            ?? throw new \RuntimeException("Compte X introuvable : @{$username}.");
    }

    private function request(string $path, array $params): array
    {
        try {
            $response = Http::timeout(15)->withToken($this->bearerToken)->get(self::API_BASE . $path, $params);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('XSyncService: connexion impossible.', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Impossible de joindre X. Vérifiez la connexion internet du serveur.', 0, $e);
        }

        if ($response->failed()) {
            $detail = $response->json('detail') ?? $response->json('title') ?? $response->body();
            Log::error('XSyncService: erreur API.', ['status' => $response->status(), 'error' => $detail]);

            throw new \RuntimeException(match ($response->status()) {
                401     => 'X : Bearer Token invalide. Générez-en un nouveau dans le portail développeur X.',
                403     => 'X : votre offre d’API ne permet pas de lire les publications (offre « Basic » ou supérieure nécessaire).',
                429     => 'X : limite de requêtes atteinte. La prochaine synchronisation réessaiera plus tard.',
                default => "X : {$detail}",
            });
        }

        // X peut renvoyer 200 avec une liste d'erreurs et sans données (ex. compte suspendu)
        if (! isset($response['data']) && isset($response['errors'][0]['detail'])) {
            throw new \RuntimeException('X : ' . $response['errors'][0]['detail']);
        }

        return $response->json() ?? [];
    }

    private function upsertPost(array $tweet, array $video, array $user): void
    {
        $id          = (string) $tweet['id'];
        $text        = trim(preg_replace('#\s*https://t\.co/\S+$#', '', $tweet['text'] ?? ''));
        $title       = $this->extractTitle($text);
        $url         = "https://x.com/{$user['username']}/status/{$id}";
        $publishedAt = isset($tweet['created_at']) ? Carbon::parse($tweet['created_at']) : null;
        $duration    = isset($video['duration_ms']) ? 'PT' . (int) round($video['duration_ms'] / 1000) . 'S' : null;
        $views       = (int) ($video['public_metrics']['view_count'] ?? $tweet['public_metrics']['impression_count'] ?? 0);
        $likes       = (int) ($tweet['public_metrics']['like_count'] ?? 0);
        $replies     = (int) ($tweet['public_metrics']['reply_count'] ?? 0);

        SocialFeed::updateOrCreate(
            ['platform' => VideoPlatform::X->value, 'platform_id' => $id],
            [
                'media_type'     => 'video',
                'title'          => $title,
                'body'           => $text ?: null,
                'thumbnail_url'  => $video['preview_image_url'] ?? null,
                'post_url'       => $url,
                'source_id'      => $user['id'],
                'source_name'    => $user['name'],
                'published_at'   => $publishedAt,
                'duration'       => $duration,
                'view_count'     => $views,
                'likes_count'    => $likes,
                'comments_count' => $replies,
                'synced_at'      => now(),
                'raw_data'       => ['tweet' => $tweet, 'media' => $video],
            ]
        );

        Video::updateOrCreate(
            ['platform' => VideoPlatform::X->value, 'video_id' => $id],
            [
                'title'          => $title,
                'description'    => $text ?: null,
                'thumbnail_url'  => $video['preview_image_url'] ?? null,
                'post_url'       => $url,
                'channel_id'     => $user['id'],
                'channel_title'  => $user['name'],
                'published_at'   => $publishedAt,
                'duration'       => $duration,
                'view_count'     => $views,
                'likes_count'    => $likes,
                'comments_count' => $replies,
                'synced_at'      => now(),
                'raw_metadata'   => ['tweet' => $tweet, 'media' => $video],
            ]
        );
    }

    private function extractTitle(string $text): string
    {
        if ($text === '') {
            return 'Vidéo sur X';
        }
        $lines = array_filter(explode("\n", $text));
        $first = reset($lines) ?: $text;

        return mb_strlen($first) > 80 ? mb_substr($first, 0, 77) . '...' : $first;
    }
}
