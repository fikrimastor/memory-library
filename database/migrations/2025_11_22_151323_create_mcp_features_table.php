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
        Schema::create('mcp_features', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['tool', 'resource', 'prompt'])->index();
            $table->string('name')->comment('Unique identifier for the feature');
            $table->string('title');
            $table->text('description');
            $table->string('class_name')->nullable()->comment('Fully qualified class name for class-based features');
            $table->text('handler_code')->nullable()->comment('Custom PHP code for dynamic features');
            $table->json('schema_definition')->nullable()->comment('JSON schema for tool inputs');
            $table->json('arguments_definition')->nullable()->comment('Arguments definition for prompts');
            $table->boolean('is_system')->default(false)->index()->comment('True for built-in features');
            $table->boolean('is_active_by_default')->default(true)->comment('Default active state for new users');
            $table->timestamps();

            // Ensure unique name per type
            $table->unique(['type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_features');
    }
};
