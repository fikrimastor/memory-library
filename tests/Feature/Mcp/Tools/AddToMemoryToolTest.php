<?php

use App\Mcp\Servers\MemoryLibraryServer;
use App\Mcp\Tools\AddToMemory;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();
});

test('it can add memory through MCP tool', function () {
    $response = MemoryLibraryServer::actingAs($this->user)->tool(AddToMemory::class, [
        'thing_to_remember' => 'This is a test memory content from MCP tool',
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory',
        'generate_embedding' => false,
    ]);

    $response->assertOk()
        ->assertSee('"title": "MCP Test Memory"')
        ->assertSee('"embedding_queued": false')
        ->assertSee('Memory added successfully');
});

test('it can add memory with embedding generation', function () {
    $response = MemoryLibraryServer::actingAs($this->user)->tool(AddToMemory::class, [
        'thing_to_remember' => 'This is a test memory content from MCP tool',
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory',
        'generate_embedding' => true,
    ]);

    $response->assertOk()
        ->assertSee('"title": "MCP Test Memory"')
        ->assertSee('"embedding_queued": true')
        ->assertSee('Memory added successfully');

    // Verify the memory was actually created
    $memory = UserMemory::first();

    expect($memory)->not->toBeNull();
    expect($memory->id)->toBeInt();
});

test('it returns error when content is missing', function () {
    $response = MemoryLibraryServer::actingAs($this->user)->tool(AddToMemory::class, [
        'metadata' => ['title' => 'MCP Test Memory'],
        'tags' => ['mcp', 'test'],
        'project_name' => 'MCP Test Project',
        'document_type' => 'Memory',
    ]);

    $response->assertOk()
        ->assertSee('"success": false')
        ->assertSee('"message": "thing_to_remember is required"');
});
