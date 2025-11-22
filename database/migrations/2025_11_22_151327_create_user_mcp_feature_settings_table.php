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
        Schema::create('user_mcp_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mcp_feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->json('custom_config')->nullable()->comment('User-specific configuration overrides');
            $table->timestamps();

            // One setting per user per feature
            $table->unique(['user_id', 'mcp_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_mcp_feature_settings');
    }
};
