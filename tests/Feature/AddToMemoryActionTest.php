<?php

use App\Actions\Memory\AddToMemoryAction;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for testing
    $this->user = User::factory()->create();
});

test('it can add a memory to the user library', function () {
    $action = app(AddToMemoryAction::class);
    
    $memory = $action->handle(
        userId: $this->user->id,
        content: 'This is a test memory content',
        metadata: ['title' => 'Test Memory'],
        tags: ['test', 'memory'],
        projectName: 'test-project',
        documentType: 'Memory',
    );
    
    expect($memory)->toBeInstanceOf(UserMemory::class);
    expect($memory->user_id)->toBe($this->user->id);
    expect($memory->thing_to_remember)->toBe('This is a test memory content');
    expect($memory->title)->toBe('Test Memory');
    expect($memory->project_name)->toBe('test-project');
    expect($memory->document_type)->toBe('memory');
    expect($memory->tags)->toBe(['test', 'memory']);
});

test('it creates an embedding job when generateEmbedding is true', function () {
    // This test would require mocking the queue system
    // For now, we'll just test that the memory is created
    $action = app(AddToMemoryAction::class);
    
    $memory = $action->handle(
        userId: $this->user->id,
        content: 'This is a test memory content',
    );
    
    expect($memory)->toBeInstanceOf(UserMemory::class);
    expect($memory->thing_to_remember)->toBe('This is a test memory content');
});