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
        if (Schema::hasTable('user_memories')) {
            Schema::table('user_memories', function (Blueprint $table) {
                // Add index for user_id and project_name if not exists
                if (!Schema::hasIndex('user_memories', 'idx_user_memories_user_project')) {
                    $table->index(['user_id', 'project_name'], 'idx_user_memories_user_project');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_memories')) {
            Schema::table('user_memories', function (Blueprint $table) {
                if (Schema::hasIndex('user_memories', 'idx_user_memories_user_project')) {
                    $table->dropIndex('idx_user_memories_user_project');
                }
            });
        }
    }
};
