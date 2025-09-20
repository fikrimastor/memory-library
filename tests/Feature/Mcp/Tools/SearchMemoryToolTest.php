<?php

use App\Mcp\Tools\SearchMemoryTool;
use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;

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
        'title' => 'Vue.js Memory',
        'project_name' => 'Test Project'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about PHP',
        'title' => 'PHP Memory',
        'project_name' => 'Another Project'
    ]);
});

test('it can search memories through MCP tool', function () {
    $tool = app(SearchMemoryTool::class);
    
    $params = [
        'user_id' => $this->user->id,
        'query' => 'Laravel'
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['total'])->toBeGreaterThan(0);
    expect($result['search_method'])->toBeString();
    expect($result['query'])->toBe('Laravel');
    
    // Verify that the results contain the expected memory
    $found = collect($result['results'])->first(function ($memory) {
        return strpos($memory['thing_to_remember'], 'Laravel') !== false;
    });
    
    expect($found)->not->toBeNull();
});

test('it returns error when query is missing', function () {
    $tool = app(SearchMemoryTool::class);
    
    $params = [
        'user_id' => $this->user->id
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBeString();
    expect($result['message'])->toContain('query is required');
});

test('it can search with different parameters', function () {
    $tool = app(SearchMemoryTool::class);
    
    $params = [
        'user_id' => $this->user->id,
        'query' => 'test',
        'limit' => 5,
        'threshold' => 0.5,
        'use_embedding' => false
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['limit'])->toBe(5);
    expect($result['threshold'])->toBe(0.5);
    expect($result['search_method'])->toBe('database');
});