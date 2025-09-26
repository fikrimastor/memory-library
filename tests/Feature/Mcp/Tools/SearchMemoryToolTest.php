<?php

use App\Mcp\Servers\MemoryLibraryServer;
use App\Mcp\Tools\SearchMemory;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();

    // Create some test memories
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Laravel',
        'title' => 'Laravel Memory',
        'project_name' => 'Test Project',
    ]);

    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Vue.js',
        'title' => 'Vue.js Memory',
        'project_name' => 'Test Project',
    ]);

    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about PHP',
        'title' => 'PHP Memory',
        'project_name' => 'Another Project',
    ]);
});

test('it can search memories through MCP tool', function () {

    $response = MemoryLibraryServer::actingAs($this->user)->tool(SearchMemory::class, [
        'query' => 'Vue Js and Laravel Stack',
    ]);

    $response->assertOk()
        ->assertSee('"total":2')
        ->assertSee('This is a test memory about Vue.js')
        ->assertSee('This is a test memory about Laravel');
});

test('it returns error when query is missing', function () {
    $response = MemoryLibraryServer::actingAs($this->user)->tool(SearchMemory::class, [
        'use_embedding' => false,
    ]);

    $response->assertHasErrors(['query is required']);
});

test('it can s search with different parameters', function () {
    $response = MemoryLibraryServer::actingAs($this->user)->tool(SearchMemory::class, [
        'query' => 'what preference Laravel best practices',
        'limit' => 5,
        'threshold' => 0.5,
        'use_embedding' => false,
    ]);

    $response->assertOk()
        ->assertSee('This is a test memory about Laravel')
        ->assertSee('"success":true')
        ->assertSee('"total":1')
        ->assertSee('"title":"Laravel Memory"');
});
