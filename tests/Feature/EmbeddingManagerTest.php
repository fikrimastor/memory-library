<?php

use App\Services\EmbeddingManager;
use App\Contracts\EmbeddingDriverInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up the configuration for our tests
    config(['embedding.providers.cloudflare' => [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
        'model' => '@cf/baai/bge-m3',
        'dimensions' => 1024,
    ]]);
    
    config(['embedding.providers.openai' => [
        'api_key' => 'test-key',
        'model' => 'text-embedding-3-small',
        'dimensions' => 1536,
    ]]);
    
    config(['embedding.providers.cohere' => [
        'api_key' => 'test-key',
        'model' => 'embed-english-v3.0',
        'dimensions' => 1024,
    ]]);
});

test('it can resolve the default embedding driver', function () {
    $manager = app(EmbeddingManager::class);
    $driver = $manager->driver();
    
    expect($driver)->toBeInstanceOf(EmbeddingDriverInterface::class);
    expect($driver->getName())->toBeString();
});

test('it can resolve the openai embedding driver', function () {
    $manager = app(EmbeddingManager::class);
    $driver = $manager->driver('openai');
    
    expect($driver)->toBeInstanceOf(EmbeddingDriverInterface::class);
    expect($driver->getName())->toBe('openai');
    expect($driver->getDimensions())->toBe(1536);
});

test('it can resolve the cloudflare embedding driver', function () {
    $manager = app(EmbeddingManager::class);
    $driver = $manager->driver('cloudflare');
    
    expect($driver)->toBeInstanceOf(EmbeddingDriverInterface::class);
    expect($driver->getName())->toBe('cloudflare');
    expect($driver->getDimensions())->toBe(1024);
});

test('it can resolve the cohere embedding driver', function () {
    $manager = app(EmbeddingManager::class);
    $driver = $manager->driver('cohere');
    
    expect($driver)->toBeInstanceOf(EmbeddingDriverInterface::class);
    expect($driver->getName())->toBe('cohere');
    expect($driver->getDimensions())->toBe(1024);
});
