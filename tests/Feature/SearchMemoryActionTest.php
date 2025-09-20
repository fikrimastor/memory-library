<?php

use App\Actions\SearchMemoryAction;
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
        'project_name' => 'Test Project'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Vue.js',
        'title' => 'Vue Memory',
        'project_name' => 'Test Project'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about PHP',
        'title' => 'PHP Memory',
        'project_name' => 'Another Project'
    ]);
});

test('it can search memories by content', function () {
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'Laravel',
        limit: 10
    );
    
    expect($results)->toHaveCount(1);
    expect($results->first()->thing_to_remember)->toContain('Laravel');
});

test('it can search memories by title', function () {
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'Vue',
        limit: 10
    );
    
    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toContain('Vue');
});

test('it can search memories by project name', function () {
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'Test Project',
        limit: 10
    );
    
    expect($results)->toHaveCount(2);
});