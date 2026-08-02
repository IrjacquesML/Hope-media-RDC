<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'token',
        'confirmed_at',
        'is_active',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
