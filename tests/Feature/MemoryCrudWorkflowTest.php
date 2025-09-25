<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserMemory;

test('user can perform complete CRUD operations on memories', function () {
    $user = User::factory()->create();

    // Create a memory
    $response = $this->actingAs($user)->post('/memories', [
        'title' => 'Test Memory',
        'thing_to_remember' => 'This is test content for a memory.',
        'document_type' => 'Test Document',
        'project_name' => 'Test Project',
        'tags' => ['test', 'crud'],
    ]);

    $response->assertRedirect(route('memories.index'));

    $memory = UserMemory::where('user_id', $user->id)->first();
    expect($memory)->not->toBeNull();
    expect($memory->title)->toBe('Test Memory');
    expect($memory->thing_to_remember)->toBe('This is test content for a memory.');

    // View/Show the memory
    $response = $this->actingAs($user)->get(route('memories.show', $memory));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('memories/Show')
        ->has('memory')
        ->where('memory.title', 'Test Memory')
    );

    // Edit form
    $response = $this->actingAs($user)->get(route('memories.edit', $memory));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('memories/Edit')
        ->has('memory')
        ->where('memory.title', 'Test Memory')
    );

    // Update the memory
    $response = $this->actingAs($user)->put(route('memories.update', $memory), [
        'title' => 'Updated Test Memory',
        'thing_to_remember' => 'This is updated test content.',
        'document_type' => 'Updated Document',
        'project_name' => 'Updated Project',
        'tags' => ['updated', 'test'],
    ]);

    $response->assertRedirect(route('memories.show', $memory));

    $memory->refresh();
    expect($memory->title)->toBe('Updated Test Memory');
    expect($memory->thing_to_remember)->toBe('This is updated test content.');

    // Delete the memory
    $response = $this->actingAs($user)->delete(route('memories.destroy', $memory));
    $response->assertRedirect(route('memories.index'));

    expect(UserMemory::find($memory->id))->toBeNull();
});

test('user cannot access another users memory', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $memory = UserMemory::factory()->create(['user_id' => $user1->id]);

    // User 2 cannot view user 1's memory
    $response = $this->actingAs($user2)->get(route('memories.show', $memory));
    $response->assertStatus(403);

    // User 2 cannot edit user 1's memory
    $response = $this->actingAs($user2)->get(route('memories.edit', $memory));
    $response->assertStatus(403);

    // User 2 cannot update user 1's memory
    $response = $this->actingAs($user2)->put(route('memories.update', $memory), [
        'title' => 'Hacked',
        'thing_to_remember' => 'Hacked content',
    ]);
    $response->assertStatus(403);

    // User 2 cannot delete user 1's memory
    $response = $this->actingAs($user2)->delete(route('memories.destroy', $memory));
    $response->assertStatus(403);
});
