<?php

use App\Mcp\Tools\ConfigureProviderTool;
use App\Models\ProviderHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create some provider health records
    ProviderHealth::factory()->create([
        'provider' => 'openai',
        'is_healthy' => true
    ]);
    
    ProviderHealth::factory()->create([
        'provider' => 'cloudflare',
        'is_healthy' => false
    ]);
});

test('it can get configuration and health status through MCP tool', function () {
    $tool = app(ConfigureProviderTool::class);
    
    $params = [];
    
    $result = $tool->handle($params);
    
    expect($result['success'])->toBeTrue();
    expect($result['configuration'])->toBeArray();
    expect($result['provider_health'])->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
});

test('it can perform health check through MCP tool', function () {
    $tool = app(ConfigureProviderTool::class);
    
    $params = [
        'health_check' => true
    ];
    
    $result = $tool->handle($params);
    
    expect($result['success'])->toBeTrue();
    expect($result['health_status'])->toBeArray();
});

test('it can test a specific provider through MCP tool', function () {
    $tool = app(ConfigureProviderTool::class);
    
    $params = [
        'test_provider' => true,
        'provider_name' => 'openai'
    ];
    
    $result = $tool->handle($params);
    
    expect($result['success'])->toBeTrue();
    expect($result['provider_test'])->toBeArray();
    expect($result['provider_test']['provider'])->toBe('openai');
});