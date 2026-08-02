<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table des contacts/demandes de prières
 * Permet aux visiteurs de soumettre des demandes de prières ou des messages
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            
            // Informations du visiteur
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            
            // Type de demande: prière, question, témoignage, autre
            $table->enum('request_type', ['prayer', 'question', 'testimony', 'contact', 'feedback'])->default('contact');
            
            // Sujet du message
            $table->string('subject');
            
            // Contenu du message
            $table->longText('message');
            
            // Pièces jointes (optionnel - chemin JSON)
            $table->json('attachments')->nullable();
            
            // Statut du traitement: nouveau, en-lecture, repondu, archivé
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            
            // Réponse de l'administrateur (si applicable)
            $table->longText('admin_reply')->nullable();
            $table->dateTime('replied_at')->nullable();
            
            // Notes internes pour l'admin
            $table->text('admin_notes')->nullable();
            
            // IP de l'expéditeur (pour modération)
            $table->ipAddress('ip_address')->nullable();
            
            // Consentement aux conditions
            $table->boolean('accepted_terms')->default(false);
            
            // Newsletter opt-in
            $table->boolean('subscribed_to_newsletter')->default(false);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('status');
            $table->index('request_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
