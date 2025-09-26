<?php

use App\Mcp\Servers\MemoryLibraryServer;
use App\Mcp\Tools\FetchMemory;
use App\Mcp\Tools\SearchMemory;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();

    $this->user2 = User::factory()->create();

    // Create some test memories
    $this->memory1 = UserMemory::factory()->create([
        'user_id' => $this->user2->id,
        'thing_to_remember' => 'This is a test memory about Laravel',
        'title' => 'Laravel Memory',
        'project_name' => 'Test Project',
        'visibility' => 'public',
    ]);

    $this->memory2 = UserMemory::factory()->create([
        'user_id' => $this->user2->id,
        'thing_to_remember' => 'This is a test memory about Vue.js',
        'title' => 'Vue.js Memory',
        'project_name' => 'Javascript Project',
        'visibility' => 'private',
    ]);

    $this->memory3 = UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about PHP',
        'title' => 'PHP Memory',
        'project_name' => 'Another Project',
    ]);
});

test('it can fetch memories through MCP tool', function () {

    $response = MemoryLibraryServer::actingAs($this->user)->tool(FetchMemory::class, [
        'id' => $this->memory3->share_token,
    ]);

    $response->assertOk()
        ->assertSee('PHP Memory')
        ->assertSee('This is a test memory about PHP');
});

test('it cannot fetch memories through MCP tool from others even public visibility', function () {

    $response = MemoryLibraryServer::actingAs($this->user)->tool(FetchMemory::class, [
        'id' => $this->memory1->share_token,
    ]);

    $response->assertOk()->assertSee('No memory found for the given ID.');
});

test('it cannot fetch memories through MCP tool from others if not public visibility', function () {

    $response = MemoryLibraryServer::actingAs($this->user)->tool(FetchMemory::class, [
        'id' => $this->memory2->share_token,
    ]);

    $response->assertOk()
        ->assertSee('No memory found for the given ID.');
});
