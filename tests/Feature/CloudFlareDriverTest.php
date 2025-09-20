<?php

use App\Drivers\Embedding\CloudFlareDriver;
use App\Contracts\EmbeddingDriverInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('cloudflare driver implements embedding driver interface', function () {
    $config = [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ];
    
    $driver = new CloudFlareDriver($config);
    
    expect($driver)->toBeInstanceOf(EmbeddingDriverInterface::class);
    expect($driver->getName())->toBe('cloudflare');
    expect($driver->getDimensions())->toBe(1024);
});

test('cloudflare driver can generate embeddings', function () {
    $config = [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ];
    
    $driver = new CloudFlareDriver($config);
    
    // Mock the HTTP response
    Http::fake([
        'https://api.cloudflare.com/client/v4/accounts/test-account/ai/run/@cf/baai/bge-m3' => Http::response([
            'result' => [
                'data' => [[0.1, 0.2, 0.3, 0.4, 0.5]]
            ]
        ], 200),
    ]);
    
    $embedding = $driver->embed('This is a test sentence');
    
    expect($embedding)->toBeArray();
    expect(count($embedding))->toBe(5);
    expect($embedding[0])->toBe(0.1);
});

test('cloudflare driver handles api errors', function () {
    $config = [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ];
    
    $driver = new CloudFlareDriver($config);
    
    // Mock a failed HTTP response
    Http::fake([
        'https://api.cloudflare.com/client/v4/accounts/test-account/ai/run/@cf/baai/bge-m3' => Http::response([
            'error' => 'Invalid API token'
        ], 401),
    ]);
    
    expect(fn() => $driver->embed('This is a test sentence'))->toThrow(RuntimeException::class);
});

test('cloudflare driver handles invalid response format', function () {
    $config = [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ];
    
    $driver = new CloudFlareDriver($config);
    
    // Mock an invalid response format
    Http::fake([
        'https://api.cloudflare.com/client/v4/accounts/test-account/ai/run/@cf/baai/bge-m3' => Http::response([
            'result' => [
                'invalid' => 'format'
            ]
        ], 200),
    ]);
    
    expect(fn() => $driver->embed('This is a test sentence'))->toThrow(RuntimeException::class);
});

test('cloudflare driver can check health status', function () {
    $config = [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ];
    
    $driver = new CloudFlareDriver($config);
    
    // Mock a successful health check
    Http::fake([
        'https://api.cloudflare.com/client/v4/accounts/test-account' => Http::response([], 200),
    ]);
    
    expect($driver->isHealthy())->toBeTrue();
});

test('cloudflare driver handles health check failures', function () {
    $config = [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ];
    
    $driver = new CloudFlareDriver($config);
    
    // Mock a failed health check
    Http::fake([
        'https://api.cloudflare.com/client/v4/accounts/test-account' => Http::response([], 500),
    ]);
    
    expect($driver->isHealthy())->toBeFalse();
});