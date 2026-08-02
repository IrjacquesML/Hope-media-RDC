<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table des posts (enseignements)
 * Contient tous les contenus: textes, vidéos, audios
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            
            // Référence à la catégorie parent
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade');
            
            // Titre du post
            $table->string('title');
            
            // Slug pour les URLs propres
            $table->string('slug')->unique();
            
            // Contenu principal (HTML)
            $table->longText('content');
            
            // Résumé/Description courte
            $table->text('excerpt')->nullable();
            
            // Type de contenu: texte, video, audio, image
            $table->enum('content_type', ['text', 'video', 'audio', 'image'])->default('text');
            
            // Image de couverture/thumbnail
            $table->string('featured_image')->nullable();
            
            // Auteur (pasteur, prédicateur)
            $table->string('author')->nullable();
            $table->string('author_email')->nullable();
            
            // Statut: brouillon, publié, archivé
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            
            // Dates importantes
            $table->dateTime('published_at')->nullable();
            $table->date('teaching_date')->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Engagement
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            
            // Ordre d'affichage
            $table->integer('order')->default(0);
            $table->boolean('is_featured')->default(false);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('content_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
