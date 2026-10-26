<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\SocialPlatforms;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuration d'un réseau social, saisie dans l'administration.
 *
 * @property string                 $platform
 * @property bool                   $enabled         synchronisation automatique active
 * @property array|null             $credentials     identifiants (chiffrés en base)
 * @property int                    $max_results
 * @property int                    $interval_hours
 * @property \Carbon\Carbon|null    $last_synced_at
 * @property string|null            $last_status     ok | error
 * @property string|null            $last_message
 * @property int|null               $last_count
 */
class SocialPlatform extends Model
{
    protected $fillable = [
        'platform', 'enabled', 'credentials', 'max_results', 'interval_hours',
        'last_synced_at', 'last_status', 'last_message', 'last_count',
    ];

    protected $casts = [
        'enabled'        => 'boolean',
        'credentials'    => 'encrypted:array',
        'max_results'    => 'integer',
        'interval_hours' => 'integer',
        'last_synced_at' => 'datetime',
        'last_count'     => 'integer',
    ];

    /** Retourne (ou crée avec les valeurs par défaut) la configuration d'un réseau. */
    public static function for(string $platform): self
    {
        $def = SocialPlatforms::get($platform);

        return static::firstOrCreate(
            ['platform' => $platform],
            ['enabled' => false, 'max_results' => $def['max_results'], 'interval_hours' => $def['interval']]
        );
    }

    /** Valeur saisie dans l'admin pour un champ (null si vide). */
    public function storedCredential(string $field): ?string
    {
        $value = $this->credentials[$field] ?? null;

        return ($value === null || $value === '') ? null : (string) $value;
    }

    /** La synchronisation automatique doit-elle tourner maintenant ? */
    public function isDue(): bool
    {
        return $this->enabled
            && (! $this->last_synced_at || $this->last_synced_at->lte(now()->subHours(max(1, $this->interval_hours))));
    }
}
