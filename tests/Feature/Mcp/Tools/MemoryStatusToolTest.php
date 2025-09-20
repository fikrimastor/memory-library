<?php

use App\Mcp\Tools\MemoryStatusTool;
use App\Models\EmbeddingJob;
use App\Models\ProviderHealth;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();
    
    // Create some test memories with specific document types
    UserMemory::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'document_type' => 'Memory'
    ]);
    
    UserMemory::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'document_type' => 'Document'
    ]);
    
    // Create some provider health records
    ProviderHealth::factory()->create([
        'provider' => 'openai',
        'is_healthy' => true
    ]);
    
    ProviderHealth::factory()->create([
        'provider' => 'cloudflare',
        'is_healthy' => false
    ]);
    
    // Create some embedding jobs
    $memory = UserMemory::first();
    EmbeddingJob::factory()->create([
        'memory_id' => $memory->id,
        'status' => 'completed'
    ]);
    
    EmbeddingJob::factory()->create([
        'memory_id' => $memory->id,
        'status' => 'pending'
    ]);
});

test('it can get memory status through MCP tool', function () {
    $tool = app(MemoryStatusTool::class);
    
    $params = [
        'user_id' => $this->user->id
    ];
    
    $result = $tool->handle($params);
    
    expect($result['success'])->toBeTrue();
    
    // Check memory stats
    expect($result['memory_stats'])->toBeArray();
    expect($result['memory_stats']['total_count'])->toBe(5);
    expect($result['memory_stats']['recent_memories'])->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result['memory_stats']['by_document_type'])->toBeArray();
    
    // Check that we have the expected document types
    expect($result['memory_stats']['by_document_type'])->toHaveKey('Memory');
    expect($result['memory_stats']['by_document_type'])->toHaveKey('Document');
    
    // Check provider health
    expect($result['provider_health'])->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result['provider_health'])->toHaveCount(2);
    
    // Check embedding jobs
    expect($result['embedding_jobs'])->toBeArray();
    expect($result['embedding_jobs']['completed'])->toBeInt();
    expect($result['embedding_jobs']['pending'])->toBeInt();
});