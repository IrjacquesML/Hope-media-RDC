<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table social_feeds
 *
 * Rôle : stocker de façon générique et unifiée les publications récupérées
 * depuis toutes les plateformes sociales (YouTube, Facebook, Instagram, TikTok).
 *
 * Principes de conception :
 *  - Contrainte UNIQUE sur (platform, platform_id) → empêche les doublons lors
 *    des synchros répétées (utilisé avec updateOrCreate dans les Services).
 *  - Colonne raw_data (JSON) → conserve le snapshot brut retourné par l'API,
 *    ce qui permet d'extraire de nouveaux champs sans nouvelle migration.
 *  - Noms de colonnes intentionnellement neutres (body, source_id…) pour rester
 *    indépendants du vocabulaire propre à chaque plateforme.
 */
return new class extends Migration
{
    // -------------------------------------------------------------------------
    // up
    // -------------------------------------------------------------------------

    public function up(): void
    {
        Schema::create('social_feeds', function (Blueprint $table) {

            // ── Identité ─────────────────────────────────────────────────────
            $table->id();

            /**
             * Plateforme source.
             * Valeurs attendues : 'youtube' | 'facebook' | 'instagram' | 'tiktok'
             * Stocké en VARCHAR(20) (pas enum) → facilite l'ajout d'une nouvelle
             * plateforme sans modification de schéma.
             */
            $table->string('platform', 20);

            /**
             * Identifiant natif de la publication sur la plateforme.
             * Ex : videoId YouTube, postId Facebook, shortcode Instagram, videoId TikTok.
             */
            $table->string('platform_id', 255);

            // ── Contenu ──────────────────────────────────────────────────────

            /**
             * Type de média.
             * Valeurs : 'video' | 'image' | 'carousel' | 'text' | 'short' | 'reel'
             */
            $table->string('media_type', 20)->default('video');

            /** Titre ou première ligne de la publication (max 255 chars). */
            $table->string('title', 255)->nullable();

            /** Corps du texte : description YouTube, caption Instagram, message Facebook. */
            $table->text('body')->nullable();

            // ── URLs ─────────────────────────────────────────────────────────

            /** URL de la miniature (HTTPS). */
            $table->string('thumbnail_url', 500)->nullable();

            /**
             * URL directe du fichier média (surtout utile pour Instagram où
             * l'API retourne l'URL CDN de l'image ou de la vidéo).
             * Note : ces URLs expirent sur certaines plateformes.
             */
            $table->string('media_url', 500)->nullable();

            /** Lien permanent vers la publication originale sur la plateforme. */
            $table->string('post_url', 500)->nullable();

            // ── Source ───────────────────────────────────────────────────────

            /**
             * Identifiant de la source (chaîne, page, compte).
             * Ex : channelId YouTube, pageId Facebook, igUserId, openId TikTok.
             */
            $table->string('source_id', 255)->nullable();

            /** Nom lisible de la source (chaîne, page, compte). */
            $table->string('source_name', 255)->nullable();

            // ── Métadonnées temporelles & métriques ──────────────────────────

            /**
             * Durée du média.
             * YouTube : format ISO 8601 (ex: "PT4M13S")
             * TikTok  : secondes entières sous forme de chaîne (ex: "42")
             */
            $table->string('duration', 50)->nullable();

            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);

            /** Date/heure de publication originale sur la plateforme. */
            $table->timestamp('published_at')->nullable();

            /** Date/heure de la dernière synchronisation réussie avec l'API. */
            $table->timestamp('synced_at')->nullable();

            // ── Snapshot brut ─────────────────────────────────────────────────

            /**
             * Réponse JSON brute retournée par l'API.
             * Permet d'extraire de nouveaux champs à l'avenir sans re-synchro.
             */
            $table->json('raw_data')->nullable();

            $table->timestamps();

            // ── Contraintes & index ──────────────────────────────────────────

            /**
             * Clé d'unicité : une même publication ne peut être stockée qu'une
             * seule fois, quelle que soit la fréquence de synchro.
             */
            $table->unique(['platform', 'platform_id'], 'social_feeds_platform_id_unique');

            /** Index pour filtrer par plateforme (affichage par onglets). */
            $table->index('platform', 'social_feeds_platform_idx');

            /** Index pour trier par date de publication (fil chronologique). */
            $table->index('published_at', 'social_feeds_published_at_idx');

            /** Index pour filtrer par type de média. */
            $table->index('media_type', 'social_feeds_media_type_idx');

            /** Index pour identifier facilement les enregistrements à re-synchroniser. */
            $table->index('synced_at', 'social_feeds_synced_at_idx');
        });
    }

    // -------------------------------------------------------------------------
    // down
    // -------------------------------------------------------------------------

    public function down(): void
    {
        Schema::dropIfExists('social_feeds');
    }
};
