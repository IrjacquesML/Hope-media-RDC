<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Compte d'administration.
 *
 * Rôles (du plus limité au plus étendu) :
 *  - editor      : Rédacteur — contenus (enseignements, Écho de la mission, vidéos, ressources, carrousel)
 *  - admin       : Administrateur — + messages, commentaires, newsletter, réseaux sociaux, rapports
 *  - super_admin : Super administrateur — + paramètres du site, partenaires et comptes
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'editor'      => ['label' => 'Rédacteur', 'level' => 1, 'color' => 'bg-slate-100 text-slate-700', 'description' => 'Publie et modifie les contenus : enseignements, Écho de la mission, vidéos, ressources, carrousel.'],
        'admin'       => ['label' => 'Administrateur', 'level' => 2, 'color' => 'bg-blue-100 text-blue-700', 'description' => 'Tout ce que fait un rédacteur, plus les messages, commentaires, newsletter, réseaux sociaux et rapports PDF.'],
        'super_admin' => ['label' => 'Super administrateur', 'level' => 3, 'color' => 'bg-amber-100 text-amber-800', 'description' => 'Accès complet : paramètres du site, partenaires et gestion des comptes.'],
    ];

    /** Valeurs par défaut d'un nouveau compte. */
    protected $attributes = [
        'role' => 'editor',
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** Le compte a-t-il au moins ce rôle ? */
    public function hasRole(string $role): bool
    {
        return (self::ROLES[$this->role]['level'] ?? 0) >= (self::ROLES[$role]['level'] ?? PHP_INT_MAX);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role]['label'] ?? $this->role;
    }

    public function getRoleColorAttribute(): string
    {
        return self::ROLES[$this->role]['color'] ?? 'bg-slate-100 text-slate-700';
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials ?: '?';
    }
}
