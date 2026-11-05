<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paramètre du site (clé → valeur). Lire via App\Support\Site, qui gère le cache et les valeurs par défaut.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];
}
