<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Partenaire affiché dans le pied de page (nom cliquable vers son site).
 */
class Partner extends Model
{
    protected $fillable = ['name', 'url', 'logo', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
