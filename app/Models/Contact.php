<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Contact
 * 
 * Représente une demande de prière ou un message de contact
 * Permet aux visiteurs de communiquer avec l'organisation
 */
class Contact extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'request_type',
        'subject',
        'message',
        'attachments',
        'status',
        'admin_reply',
        'replied_at',
        'admin_notes',
        'ip_address',
        'accepted_terms',
        'subscribed_to_newsletter',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'replied_at' => 'datetime',
        'accepted_terms' => 'boolean',
        'subscribed_to_newsletter' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Récupérer les nouveaux messages (non lus)
     */
    public static function unread()
    {
        return self::where('status', 'new');
    }

    /**
     * Récupérer les demandes de prière
     */
    public static function prayerRequests()
    {
        return self::where('request_type', 'prayer');
    }

    /**
     * Récupérer les demandes de questions
     */
    public static function questions()
    {
        return self::where('request_type', 'question');
    }

    /**
     * Récupérer les témoignages
     */
    public static function testimonies()
    {
        return self::where('request_type', 'testimony');
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead()
    {
        $this->update(['status' => 'read']);
    }

    /**
     * Marquer comme répondu
     */
    public function markAsReplied($reply)
    {
        $this->update([
            'status' => 'replied',
            'admin_reply' => $reply,
            'replied_at' => now(),
        ]);
    }

    /**
     * Marquer comme archivé
     */
    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Vérifier si le message est nouveau
     */
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Vérifier si c'est une demande de prière
     */
    public function isPrayerRequest(): bool
    {
        return $this->request_type === 'prayer';
    }

    /**
     * Obtenir le type de demande formaté
     */
    public function getFormattedRequestTypeAttribute(): string
    {
        return match ($this->request_type) {
            'prayer' => 'Demande de prière',
            'question' => 'Question',
            'testimony' => 'Témoignage',
            'contact' => 'Contact',
            'feedback' => 'Commentaire',
            default => $this->request_type,
        };
    }

    /**
     * Obtenir le statut formaté
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Nouveau',
            'read' => 'Lu',
            'replied' => 'Répondu',
            'archived' => 'Archivé',
            default => $this->status,
        };
    }

    /**
     * Scope pour les contacts abonnés à la newsletter
     */
    public function scopeSubscribedToNewsletter($query)
    {
        return $query->where('subscribed_to_newsletter', true);
    }
}
