<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('news', 'likes')) {
            Schema::table('news', function (Blueprint $table) {
                $table->unsignedInteger('likes')->default(0)->after('views');
            });
        }

        if (! Schema::hasColumn('comments', 'commentable_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->string('commentable_type')->nullable()->after('id');
                $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');
                $table->index(['commentable_type', 'commentable_id']);
            });

            if (Schema::hasColumn('comments', 'post_id')) {
                DB::table('comments')->orderBy('id')->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('comments')->where('id', $row->id)->update([
                            'commentable_type' => Post::class,
                            'commentable_id'   => $row->post_id,
                        ]);
                    }
                });

                Schema::table('comments', function (Blueprint $table) {
                    $table->dropForeign(['post_id']);
                    $table->dropColumn('post_id');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('comments', 'commentable_id') && ! Schema::hasColumn('comments', 'post_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->foreignId('post_id')->nullable()->constrained()->cascadeOnDelete();
            });

            DB::table('comments')
                ->where('commentable_type', Post::class)
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('comments')->where('id', $row->id)->update([
                            'post_id' => $row->commentable_id,
                        ]);
                    }
                });

            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex(['commentable_type', 'commentable_id']);
                $table->dropColumn(['commentable_type', 'commentable_id']);
            });
        }

        if (Schema::hasColumn('news', 'likes')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn('likes');
            });
        }
    }
};
