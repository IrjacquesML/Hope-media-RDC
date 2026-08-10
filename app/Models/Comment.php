<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'author_name',
        'author_email',
        'content',
        'is_approved',
        'is_admin_reply',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_admin_reply' => 'boolean',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_approved', true)
            ->orderBy('created_at');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
