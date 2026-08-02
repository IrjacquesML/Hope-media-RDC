<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - Convertit la colonne `platform` de ENUM vers VARCHAR(20) afin de supporter
 *   facilement de nouvelles plateformes sans migration supplémentaire.
 * - Ajoute la colonne `post_url` pour stocker le lien permanent original
 *   (utile pour Facebook, Instagram, TikTok où l'URL publique est importante).
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite ne supporte pas MODIFY COLUMN : on utilise DB::statement pour MySQL/Postgres
        // et on ignore l'erreur pour SQLite (la colonne restera TEXT, ce qui est compatible).
        try {
            DB::statement("ALTER TABLE videos MODIFY COLUMN platform VARCHAR(20) NOT NULL");
        } catch (\Exception) {
            // SQLite — aucune action requise, TEXT accepte n'importe quelle valeur.
        }

        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'post_url')) {
                $table->string('post_url')->nullable()->after('video_id')
                    ->comment('Lien permanent du post (Instagram, Facebook, TikTok)');
            }
            if (! Schema::hasColumn('videos', 'likes_count')) {
                $table->unsignedBigInteger('likes_count')->default(0)->after('view_count');
            }
            if (! Schema::hasColumn('videos', 'comments_count')) {
                $table->unsignedBigInteger('comments_count')->default(0)->after('likes_count');
            }
        });
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE videos MODIFY COLUMN platform ENUM('youtube','tiktok','facebook') NOT NULL");
        } catch (\Exception) {
            // SQLite — pas de rollback possible sur le type
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['post_url', 'likes_count', 'comments_count']);
        });
    }
};
