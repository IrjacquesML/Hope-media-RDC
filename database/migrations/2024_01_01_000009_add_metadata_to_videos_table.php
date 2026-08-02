<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute les colonnes de métadonnées YouTube/API à la table videos.
 *
 * Colonnes ajoutées :
 *  - description    : texte libre retourné par l'API
 *  - thumbnail_url  : URL HTTPS de la miniature haute qualité
 *  - channel_id     : identifiant de la chaîne source (ex: UCxxxxxx)
 *  - channel_title  : nom lisible de la chaîne
 *  - published_at   : date/heure de publication sur la plateforme
 *  - duration       : durée ISO 8601 (PT4M13S) telle que retournée par l'API
 *  - view_count     : nombre de vues au moment de la dernière synchro
 *  - synced_at      : dernière synchronisation réussie avec l'API
 *  - raw_metadata   : snapshot JSON brut (utile pour le débogage / évolutions futures)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->text('description')->nullable()->after('video_id');
            $table->string('thumbnail_url')->nullable()->after('description');
            $table->string('channel_id')->nullable()->after('thumbnail_url');
            $table->string('channel_title')->nullable()->after('channel_id');
            $table->dateTime('published_at')->nullable()->after('channel_title');
            $table->string('duration')->nullable()->after('published_at');     // ex: "PT4M13S"
            $table->unsignedBigInteger('view_count')->default(0)->after('duration');
            $table->timestamp('synced_at')->nullable()->after('view_count');
            $table->json('raw_metadata')->nullable()->after('synced_at');

            // Index pour filtrer rapidement par chaîne ou par date de publication
            $table->index('channel_id');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['channel_id']);
            $table->dropIndex(['published_at']);
            $table->dropColumn([
                'description', 'thumbnail_url', 'channel_id', 'channel_title',
                'published_at', 'duration', 'view_count', 'synced_at', 'raw_metadata',
            ]);
        });
    }
};
