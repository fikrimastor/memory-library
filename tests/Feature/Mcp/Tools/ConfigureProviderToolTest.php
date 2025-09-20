<?php

use App\Mcp\Tools\ConfigureProviderTool;
use App\Models\ProviderHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;

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
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['configuration'])->toBeArray();
    expect($result['provider_health'])->toBeArray();
    expect(count($result['provider_health']))->toBe(2);
});

test('it can perform health check through MCP tool', function () {
    $tool = app(ConfigureProviderTool::class);
    
    $params = [
        'health_check' => true
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['health_status'])->toBeArray();
});

test('it can test a specific provider through MCP tool', function () {
    $tool = app(ConfigureProviderTool::class);
    
    $params = [
        'test_provider' => true,
        'provider_name' => 'openai'
    ];
    
    $request = new Request($params);
    $response = $tool->handle($request);
    $content = $response->content();
    $result = json_decode((string) $content, true);
    
    expect($result['success'])->toBeTrue();
    expect($result['provider_test'])->toBeArray();
    expect($result['provider_test']['provider'])->toBe('openai');
});