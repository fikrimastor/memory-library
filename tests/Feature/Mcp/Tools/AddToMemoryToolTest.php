<?php

use App\Mcp\Tools\AddToMemory;
use App\Models\EmbeddingJob;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();
});

test('it can add memory through MCP tool', function () {
    $tool = app(AddToMemory::class);
    
    $params = [
        'user_id' => $this->user->id,
        'content' => 'This is a test memory content from MCP tool',
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory',
        'generate_embedding' => false
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['memory_id'])->toBeInt();
    expect($result['embedding_queued'])->toBeFalse();
    
    // Verify the memory was actually created
    $memory = UserMemory::find($result['memory_id']);
    expect($memory)->not->toBeNull();
    expect($memory->thing_to_remember)->toBe('This is a test memory content from MCP tool');
    expect($memory->title)->toBe('MCP Test Memory');
    expect($memory->project_name)->toBe('MCP Test Project');
});

test('it can add memory with embedding generation', function () {
    $tool = app(AddToMemory::class);
    
    $params = [
        'user_id' => $this->user->id,
        'content' => 'This is a test memory content from MCP tool',
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory',
        'generate_embedding' => true
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['memory_id'])->toBeInt();
    expect($result['embedding_queued'])->toBeTrue();
    
    // Verify the memory was actually created
    $memory = UserMemory::find($result['memory_id']);
    expect($memory)->not->toBeNull();
});

test('it returns error when content is missing', function () {
    $tool = app(AddToMemory::class);
    
    $params = [
        'user_id' => $this->user->id,
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory'
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBeString();
    expect($result['message'])->toContain('content is required');
});