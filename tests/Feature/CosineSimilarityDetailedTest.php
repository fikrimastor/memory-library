<?php

use App\Actions\Memory\SearchMemoryAction;
use App\Models\User;
use App\Services\EmbeddingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('cosine similarity calculation produces expected results', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    // Test case 1: Same direction vectors
    $vectorA = [1.0, 2.0, 3.0];
    $vectorB = [2.0, 4.0, 6.0]; // Same direction, double magnitude
    $similarity = $method->invoke($action, $vectorA, $vectorB);
    expect($similarity)->toBeGreaterThan(0.999); // Should be exactly 1.0
    
    // Test case 2: Orthogonal vectors
    $vectorC = [1.0, 0.0, 0.0];
    $vectorD = [0.0, 1.0, 0.0];
    $similarity = $method->invoke($action, $vectorC, $vectorD);
    expect($similarity)->toBeLessThan(0.001); // Should be exactly 0.0
    expect($similarity)->toBeGreaterThanOrEqual(0.0);
    
    // Test case 3: Opposite vectors
    $vectorE = [1.0, 2.0, 3.0];
    $vectorF = [-1.0, -2.0, -3.0];
    $similarity = $method->invoke($action, $vectorE, $vectorF);
    expect($similarity)->toBeLessThan(-0.999); // Should be exactly -1.0
    
    // Test case 4: Partial similarity
    $vectorG = [1.0, 1.0, 0.0];
    $vectorH = [1.0, 0.0, 1.0];
    $similarity = $method->invoke($action, $vectorG, $vectorH);
    // Both have magnitude sqrt(2), dot product = 1, so similarity = 1/(sqrt(2)*sqrt(2)) = 0.5
    expect($similarity)->toBeGreaterThan(0.499);
    expect($similarity)->toBeLessThan(0.501);
    
    // Test case 5: Zero vector with non-zero vector
    $vectorI = [0.0, 0.0, 0.0];
    $vectorJ = [1.0, 2.0, 3.0];
    $similarity = $method->invoke($action, $vectorI, $vectorJ);
    expect($similarity)->toBe(0.0); // Should be 0.0
    
    // Test case 6: Different length vectors
    $vectorK = [1.0, 2.0, 3.0];
    $vectorL = [1.0, 2.0];
    $similarity = $method->invoke($action, $vectorK, $vectorL);
    expect($similarity)->toBe(0.0); // Should be 0.0
    
    // Test case 7: Empty vectors
    $similarity = $method->invoke($action, [], []);
    expect($similarity)->toBe(0.0); // Should be 0.0
});

test('vector search returns memories sorted by similarity', function () {
    // Mock the embedding manager and driver
    $embeddingDriver = Mockery::mock(App\Contracts\EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(true);
    $embeddingDriver->shouldReceive('embed')->with('test query')->andReturn([1.0, 0.0, 0.0]);
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    // Create memories with different embeddings (different similarity to [1,0,0])
    $memory1 = App\Models\UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'High similarity memory',
        'embedding' => [0.9, 0.1, 0.1] // Cosine similarity ≈ 0.98
    ]);
    
    $memory2 = App\Models\UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Medium similarity memory',
        'embedding' => [0.5, 0.5, 0.0] // Cosine similarity = 0.5
    ]);
    
    $memory3 = App\Models\UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Low similarity memory',
        'embedding' => [0.1, 0.9, 0.0] // Cosine similarity ≈ 0.1
    ]);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'test query',
        limit: 10,
        threshold: 0.05, // Low threshold to get all memories
        useEmbedding: true
    );
    
    // Should have 3 results
    expect($results)->toHaveCount(3);
    
    // Should be sorted by similarity (highest first)
    expect($results->first()->thing_to_remember)->toBe('High similarity memory');
    expect($results->items()[1]->thing_to_remember)->toBe('Medium similarity memory');
    expect($results->items()[2]->thing_to_remember)->toBe('Low similarity memory');
});

afterEach(function () {
    Mockery::close();
});