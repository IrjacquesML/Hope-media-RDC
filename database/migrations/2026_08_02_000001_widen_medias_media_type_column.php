<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Élargit media_type pour accepter youtube, vimeo, dailymotion, external, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE medias MODIFY media_type VARCHAR(32) NOT NULL DEFAULT 'local'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE medias ALTER COLUMN media_type TYPE VARCHAR(32)');
            return;
        }

        // SQLite: enum Laravel = VARCHAR — rien à faire
    }

    public function down(): void
    {
        // Irreversible volontairement (données hors enum perdues)
    }
};
