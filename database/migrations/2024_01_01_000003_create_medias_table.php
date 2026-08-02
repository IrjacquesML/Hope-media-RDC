<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table des médias
 * Gère les fichiers vidéo, audio, images liés aux posts
 * Supporte YouTube, Vimeo, fichiers locaux
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            
            // Référence au post parent
            $table->foreignId('post_id')
                ->constrained('posts')
                ->onDelete('cascade');
            
            // Type de média: local, youtube, vimeo
            $table->enum('media_type', ['local', 'youtube', 'vimeo'])->default('local');
            
            // Titre du média
            $table->string('title');
            
            // Description du média
            $table->text('description')->nullable();
            
            // Chemin/URL du fichier ou ID externe
            // Pour YouTube: ID de la vidéo (ex: "dQw4w9WgXcQ")
            // Pour Vimeo: ID ou URL
            // Pour local: chemin dans storage (ex: "posts/video.mp4")
            $table->string('url');
            
            // Durée en secondes (pour vidéos/audios)
            $table->integer('duration')->nullable();
            
            // Taille du fichier en bytes (pour fichiers locaux)
            $table->bigInteger('file_size')->nullable();
            
            // Type MIME (video/mp4, audio/mpeg, image/jpeg, etc.)
            $table->string('mime_type')->nullable();
            
            // Thumbnail/Poster image
            $table->string('thumbnail')->nullable();
            
            // Ordre d'affichage
            $table->integer('order')->default(0);
            
            // Statut (actif/inactif)
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Index
            $table->index('post_id');
            $table->index('media_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
