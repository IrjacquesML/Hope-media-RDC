<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une newsletter automatique (envoi hebdomadaire).
 */
class NewsletterCampaign extends Model
{
    protected $fillable = [
        'subject', 'scheduled_for', 'period_start', 'period_end', 'items', 'status',
        'recipients_total', 'sent_count', 'failed_count', 'last_subscriber_id', 'error', 'finished_at',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'period_start'  => 'datetime',
        'period_end'    => 'datetime',
        'finished_at'   => 'datetime',
        'items'         => 'array',
    ];

    public const STATUSES = [
        'sending' => ['En cours d’envoi', 'bg-blue-100 text-blue-800'],
        'sent'    => ['Envoyée', 'bg-green-100 text-green-800'],
        'skipped' => ['Pas de nouveautés', 'bg-slate-100 text-slate-600'],
        'failed'  => ['Échec', 'bg-red-100 text-red-800'],
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-slate-100 text-slate-600';
    }
}
