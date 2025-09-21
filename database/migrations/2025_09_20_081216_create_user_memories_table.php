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
        Schema::create('user_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('thing_to_remember');
            $table->string('title')->nullable();
            $table->string('document_type')->default('Memory');
            $table->string('project_name')->nullable();
            $table->json('tags')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'project_name'], 'idx_user_memories_user_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_memories');
    }
};
