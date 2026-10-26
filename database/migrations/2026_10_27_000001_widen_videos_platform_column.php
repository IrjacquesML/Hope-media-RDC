<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La colonne videos.platform a été créée en ENUM('youtube','tiktok','facebook').
 * Sous SQLite, la migration 2024_01_01_000010 n'avait pas pu l'élargir : la contrainte
 * CHECK refusait donc Instagram. On la transforme en VARCHAR(20) pour accepter
 * youtube, facebook, instagram, tiktok et x (les données existantes sont conservées).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('platform', 20)->change();
        });
    }

    public function down(): void
    {
        // Pas de retour à l'ENUM : il supprimerait les vidéos Instagram et X.
    }
};
