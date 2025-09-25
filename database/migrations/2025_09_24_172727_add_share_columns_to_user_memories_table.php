<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_memories', function (Blueprint $table) {
            $table->string('share_token', 26)->nullable()->unique()->index()->after('tags');
            $table->enum('visibility', ['private', 'public', 'unlisted'])->default('private')->index()->after('share_token');
            $table->timestamp('shared_at')->nullable()->after('visibility');
            $table->json('share_options')->nullable()->after('shared_at');

            // Add composite index for efficient querying
            $table->index(['visibility', 'shared_at'], 'idx_visibility_shared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_memories', function (Blueprint $table) {
            $table->dropIndex(['idx_visibility_shared_at']);
            $table->dropIndex(['visibility']);
            $table->dropUnique(['share_token']);
            $table->dropIndex(['share_token']);

            $table->dropColumn([
                'share_token',
                'visibility',
                'shared_at',
                'share_options',
            ]);
        });
    }
};
