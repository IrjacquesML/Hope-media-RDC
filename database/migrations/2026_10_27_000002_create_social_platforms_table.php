<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuration des réseaux sociaux, modifiable depuis l'administration.
 *
 *  - credentials : identifiants et jetons d'API, chiffrés (APP_KEY) dans la base.
 *  - interval_hours : fréquence de la synchronisation automatique.
 *  - last_* : résultat de la dernière synchronisation, affiché dans l'admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 20)->unique();           // youtube | facebook | instagram | tiktok | x
            $table->boolean('enabled')->default(false);
            $table->text('credentials')->nullable();             // JSON chiffré
            $table->unsignedSmallInteger('max_results')->default(20);
            $table->unsignedSmallInteger('interval_hours')->default(6);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_status', 10)->nullable();       // ok | error
            $table->text('last_message')->nullable();
            $table->unsignedInteger('last_count')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_platforms');
    }
};
