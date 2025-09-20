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
        Schema::create('provider_health', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->unique();
            $table->boolean('is_healthy')->default(true);
            $table->timestamp('last_check')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->text('last_error')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            
            $table->index(['provider', 'is_healthy'], 'idx_provider_health');
            $table->index('last_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_health');
    }
};
