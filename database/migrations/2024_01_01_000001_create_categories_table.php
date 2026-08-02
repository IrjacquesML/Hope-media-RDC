<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table des catégories
 * Contient les catégories des enseignements (Sermons, Études bibliques, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Nom de la catégorie (ex: "Sermons", "Études bibliques")
            $table->string('name')->unique();
            
            // Slug pour les URLs propres (ex: "sermons", "etudes-bibliques")
            $table->string('slug')->unique();
            
            // Description de la catégorie
            $table->text('description')->nullable();
            
            // Type: enseignement ou service
            $table->enum('type', ['enseignement', 'service'])->default('enseignement');
            
            // Icône ou couleur pour l'affichage front-end
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            
            // Ordre d'affichage
            $table->integer('order')->default(0);
            
            // Statut (actif/inactif)
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
