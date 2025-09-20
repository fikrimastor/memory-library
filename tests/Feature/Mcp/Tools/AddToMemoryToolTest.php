<?php

use App\Mcp\Tools\AddToMemoryTool;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();
});

test('it can add memory through MCP tool', function () {
    $tool = app(AddToMemoryTool::class);
    
    $params = [
        'user_id' => $this->user->id,
        'content' => 'This is a test memory content from MCP tool',
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory',
        'generate_embedding' => false
    ];
    
    $result = $tool->handle($params);
    
    expect($result['success'])->toBeTrue();
    expect($result['memory_id'])->toBeInt();
    
    // Verify the memory was actually created
    $memory = UserMemory::find($result['memory_id']);
    expect($memory)->not->toBeNull();
    expect($memory->thing_to_remember)->toBe('This is a test memory content from MCP tool');
    expect($memory->title)->toBe('MCP Test Memory');
    expect($memory->project_name)->toBe('MCP Test Project');
});