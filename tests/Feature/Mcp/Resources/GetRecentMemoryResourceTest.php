<?php

use App\Mcp\Resources\GetRecentMemory;
use App\Mcp\Servers\MemoryLibraryServer;
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
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Laravel VILT Stack',
        'title' => 'Laravel Memory',
        'project_name' => 'Test Project',
    ]);
});

test('it can get recent memories through MCP resource for his own', function () {

    $response = MemoryLibraryServer::actingAs($this->user)->resource(GetRecentMemory::class);

    $response->assertOk()
        ->assertSee('VILT Stack')
        ->assertSee('Laravel Memory');
});

test('it cannot get recent memories through MCP resource for others memories', function () {

    $response = MemoryLibraryServer::actingAs($this->user2)->resource(GetRecentMemory::class);

    $response->assertOk()->assertSee('No Recent Memory Found');
});
