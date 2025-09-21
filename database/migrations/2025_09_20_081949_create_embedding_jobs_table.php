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
        Schema::create('embedding_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\UserMemory::class,'memory_id')->constrained('user_memories')->onDelete('cascade');
            $table->string('provider', 50);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'provider'], 'idx_status_provider');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embedding_jobs');
    }
};
