<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique des newsletters automatiques (une ligne par envoi hebdomadaire).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->timestamp('scheduled_for')->index();      // créneau hebdomadaire (jour + heure réglés)
            $table->timestamp('period_start')->nullable();    // nouveautés publiées depuis…
            $table->timestamp('period_end')->nullable();      // … jusqu'à
            $table->json('items')->nullable();                // contenus inclus
            $table->string('status', 20)->default('sending'); // sending | sent | skipped | failed
            $table->unsignedInteger('recipients_total')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedBigInteger('last_subscriber_id')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaigns');
    }
};
