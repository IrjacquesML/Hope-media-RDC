<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Image du carrousel d'un article de l'Écho de la mission.
 */
class NewsImage extends Model
{
    protected $fillable = [
        'news_id',
        'image',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
