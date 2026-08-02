<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('category')->nullable()->after('is_featured');
            $table->json('tags')->nullable()->after('category');
            $table->string('meta_title')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['category', 'tags', 'meta_title']);
        });
    }
};
