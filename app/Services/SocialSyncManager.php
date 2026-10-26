<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SocialPlatform;
use App\Support\SocialPlatforms;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Point d'entrée unique de la synchronisation des réseaux sociaux.
 *
 *  - Lit les identifiants saisis dans l'admin (table social_platforms, chiffrés),
 *    avec repli sur config/services.php (.env) quand un champ est vide.
 *  - Construit le service de chaque réseau et lance la synchronisation.
 *  - Enregistre le résultat (date, nombre, erreur) affiché dans l'admin.
 */
final class SocialSyncManager
{
    public const SCHEDULER_HEARTBEAT = 'social-sync:last-run';

    /** Identifiants effectifs d'un réseau (admin en priorité, sinon .env). */
    public function credentials(string $platform): array
    {
        $model = SocialPlatform::for($platform);
        $values = [];
        foreach (SocialPlatforms::get($platform)['fields'] as $field => $_) {
            $values[$field] = $model->storedCredential($field) ?? $this->envValue($platform, $field);
        }

        return $values;
    }

    /** D'où vient la valeur d'un champ : « admin », « env » ou null. */
    public function source(string $platform, string $field): ?string
    {
        if (SocialPlatform::for($platform)->storedCredential($field) !== null) {
            return 'admin';
        }

        return $this->envValue($platform, $field) !== null ? 'env' : null;
    }

    /** Libellés des champs obligatoires encore vides. */
    public function missing(string $platform): array
    {
        $def = SocialPlatforms::get($platform);
        $values = $this->credentials($platform);
        $missing = [];
        foreach ($def['fields'] as $field => $meta) {
            if ($meta['required'] && empty($values[$field])) {
                $missing[] = $meta['label'];
            }
        }
        // Instagram : l'ID du compte devient obligatoire avec un jeton Facebook
        if ($platform === 'instagram' && ! empty($values['access_token'])
            && ! str_starts_with($values['access_token'], 'IG') && empty($values['user_id'])) {
            $missing[] = $def['fields']['user_id']['label'];
        }

        return $missing;
    }

    public function isConfigured(string $platform): bool
    {
        return $this->missing($platform) === [];
    }

    /** Vérifie les identifiants sans rien enregistrer. Retourne le nom du compte trouvé. */
    public function test(string $platform): string
    {
        return $this->service($platform)->testConnection();
    }

    /**
     * Synchronise un réseau et enregistre le résultat.
     *
     * @return int Nombre de vidéos créées ou mises à jour.
     * @throws \Throwable L'erreur est enregistrée, puis relancée pour l'appelant.
     */
    public function sync(string $platform): int
    {
        $model = SocialPlatform::for($platform);

        // Configuration incomplète : ce n'est pas une tentative, on n'enregistre rien.
        if ($missing = $this->missing($platform)) {
            throw new \RuntimeException('Configuration incomplète : ' . implode(', ', $missing) . '.');
        }

        try {
            $service = $this->service($platform);
            $count = match ($platform) {
                'youtube', 'tiktok' => $service->syncLatestVideos(),
                'facebook', 'x'     => $service->syncLatestPosts(),
                'instagram'         => $service->syncLatestMedia(),
            };

            $model->forceFill([
                'last_synced_at' => now(), 'last_status' => 'ok', 'last_count' => $count, 'last_message' => null,
            ])->save();

            return $count;
        } catch (\Throwable $e) {
            Log::error("Synchronisation {$platform} échouée", ['error' => $e->getMessage()]);
            $model->forceFill([
                'last_synced_at' => now(), 'last_status' => 'error', 'last_count' => null,
                'last_message' => mb_substr($e->getMessage(), 0, 1000),
            ])->save();

            throw $e;
        }
    }

    /**
     * Synchronise tous les réseaux activés dont l'intervalle est écoulé (planificateur).
     *
     * @return array<string, int|string> nombre de vidéos, ou message d'erreur, par réseau
     */
    public function syncDue(): array
    {
        Cache::forever(self::SCHEDULER_HEARTBEAT, now()->toIso8601String());

        $results = [];
        foreach (SocialPlatforms::keys() as $platform) {
            $model = SocialPlatform::for($platform);
            if (! $model->isDue()) {
                continue;
            }
            try {
                $results[$platform] = $this->sync($platform);
            } catch (\Throwable $e) {
                $results[$platform] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Recopie les identifiants de l'admin dans config('services.*'), pour que les
     * anciennes commandes (youtube:sync, facebook:sync…) utilisent les mêmes valeurs.
     */
    public function applyToConfig(): void
    {
        if (! Schema::hasTable('social_platforms')) {
            return;
        }

        foreach (SocialPlatform::all() as $model) {
            $def = SocialPlatforms::all()[$model->platform] ?? null;
            if (! $def) {
                continue;
            }
            foreach ($def['fields'] as $field => $_) {
                if (($value = $model->storedCredential($field)) !== null) {
                    config([SocialPlatforms::configKey($model->platform, $field) => $value]);
                }
            }
            config(["services.{$model->platform}.max_results" => $model->max_results]);
        }
    }

    // -------------------------------------------------------------------------

    /** Construit le service d'un réseau à partir des identifiants effectifs. */
    public function service(string $platform): object
    {
        if ($missing = $this->missing($platform)) {
            throw new \RuntimeException('Configuration incomplète : ' . implode(', ', $missing) . '.');
        }

        $c = $this->credentials($platform);
        $max = min(SocialPlatform::for($platform)->max_results, SocialPlatforms::get($platform)['max_limit']);

        return match ($platform) {
            'youtube'   => new YouTubeApiService($c['api_key'], $c['channel_id'], $max),
            'facebook'  => new FacebookSyncService($c['page_id'], $c['page_access_token'], $max),
            'instagram' => new InstagramSyncService((string) ($c['user_id'] ?? ''), $c['access_token'], $max),
            'tiktok'    => new TikTokSyncService(
                $c['client_key'], $c['client_secret'], $c['access_token'], (string) ($c['open_id'] ?? ''), $max,
                $c['refresh_token'],
                fn (string $access, ?string $refresh) => $this->storeCredentials('tiktok', ['access_token' => $access, 'refresh_token' => $refresh]),
            ),
            'x'         => new XSyncService($c['bearer_token'], $c['username'], $max),
        };
    }

    /** Enregistre des identifiants (ex. jetons TikTok renouvelés) sans toucher aux autres. */
    public function storeCredentials(string $platform, array $values): void
    {
        $model = SocialPlatform::for($platform);
        $model->credentials = array_merge($model->credentials ?? [], array_filter($values, fn ($v) => $v !== null && $v !== ''));
        $model->save();
    }

    private function envValue(string $platform, string $field): ?string
    {
        $value = config(SocialPlatforms::configKey($platform, $field));

        return ($value === null || $value === '') ? null : (string) $value;
    }
}
