<?php

use App\Services\EmbeddingManager;
use App\Contracts\EmbeddingDriverInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
    expect($driver->getDimensions())->toBe(768);
});

test('it can resolve the cohere embedding driver', function () {
    $manager = app(EmbeddingManager::class);
    $driver = $manager->driver('cohere');
    
    expect($driver)->toBeInstanceOf(EmbeddingDriverInterface::class);
    expect($driver->getName())->toBe('cohere');
    expect($driver->getDimensions())->toBe(1024);
});
