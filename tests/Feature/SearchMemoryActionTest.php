<?php

use App\Actions\SearchMemoryAction;
use App\Models\User;
use App\Models\UserMemory;
use App\Services\EmbeddingManager;
use App\Contracts\EmbeddingDriverInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a fresh user for each test
    $this->user = User::factory()->create();
});

test('it can search memories by content', function () {
    // Create specific test data
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Laravel',
        'title' => 'Laravel Memory',
        'project_name' => 'Web Project'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Vue.js',
        'title' => 'Vue Memory',
        'project_name' => 'Frontend Project'
    ]);
    
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'Laravel',
        limit: 10
    );
    
    expect($results['results'])->toHaveCount(1);
    expect($results['results'][0]['thing_to_remember'])->toContain('Laravel');
    expect($results['metadata']['success'])->toBeTrue();
    expect($results['metadata']['total'])->toBe(1);
});

test('it can search memories by title', function () {
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about Vue.js',
        'title' => 'Vue Memory',
        'project_name' => 'Frontend Project'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory about React',
        'title' => 'React Memory',
        'project_name' => 'Frontend Project'
    ]);
    
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
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'First memory for special project',
        'title' => 'Memory 1',
        'project_name' => 'SpecialProject'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Second memory for special project',
        'title' => 'Memory 2',
        'project_name' => 'SpecialProject'
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Different memory',
        'title' => 'Memory 3',
        'project_name' => 'OtherProject'
    ]);
    
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'SpecialProject',
        limit: 10
    );
    
    expect($results)->toHaveCount(2);
});

test('it returns empty results when no matches found', function () {
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This is a test memory',
        'title' => 'Test Memory',
        'project_name' => 'Test Project'
    ]);
    
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'NonexistentTerm12345',
        limit: 10
    );
    
    expect($results)->toHaveCount(0);
});

test('it respects the limit parameter', function () {
    // Create 5 memories with same searchable term
    UserMemory::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'SearchableTerm memory content',
    ]);
    
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'SearchableTerm',
        limit: 3
    );
    
    expect($results->perPage())->toBe(3);
});

test('it only returns memories for the specified user', function () {
    // Create another user with memories
    $otherUser = User::factory()->create();
    UserMemory::factory()->create([
        'user_id' => $otherUser->id,
        'thing_to_remember' => 'UniqueSearchTerm should not appear in results',
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'UniqueSearchTerm memory for current user',
    ]);
    
    $action = app(SearchMemoryAction::class);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'UniqueSearchTerm',
        limit: 10
    );
    
    expect($results)->toHaveCount(1);
    expect($results->every(fn($memory) => $memory->user_id === $this->user->id))->toBeTrue();
});

test('it calculates cosine similarity correctly for identical vectors', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    $vector = [1.0, 2.0, 3.0, 4.0];
    $similarity = $method->invoke($action, $vector, $vector);
    
    expect($similarity)->toBe(1.0);
});

test('it calculates cosine similarity correctly for orthogonal vectors', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    $vectorA = [1.0, 0.0];
    $vectorB = [0.0, 1.0];
    $similarity = $method->invoke($action, $vectorA, $vectorB);
    
    expect($similarity)->toBe(0.0);
});

test('it calculates cosine similarity correctly for opposite vectors', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    $vectorA = [1.0, 2.0, 3.0];
    $vectorB = [-1.0, -2.0, -3.0];
    $similarity = $method->invoke($action, $vectorA, $vectorB);
    
    expect($similarity)->toBe(-1.0);
});

test('it handles zero vectors in cosine similarity calculation', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    $vectorA = [0.0, 0.0, 0.0];
    $vectorB = [1.0, 2.0, 3.0];
    $similarity = $method->invoke($action, $vectorA, $vectorB);
    
    expect($similarity)->toBe(0.0);
});

test('it handles different length vectors in cosine similarity calculation', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    $vectorA = [1.0, 2.0, 3.0];
    $vectorB = [1.0, 2.0];
    $similarity = $method->invoke($action, $vectorA, $vectorB);
    
    expect($similarity)->toBe(0.0);
});

test('it handles empty vectors in cosine similarity calculation', function () {
    $action = app(SearchMemoryAction::class);
    
    // Use reflection to test the protected method
    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);
    
    $similarity = $method->invoke($action, [], []);
    
    expect($similarity)->toBe(0.0);
});

test('it performs vector search when embeddings are available and provider is healthy', function () {
    // Mock the embedding manager and driver
    $embeddingDriver = Mockery::mock(EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(true);
    $embeddingDriver->shouldReceive('embed')->with('VectorSearchTest')->andReturn([0.1, 0.2, 0.3, 0.4]);
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    // Create memory with very similar embedding
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This memory should be found by vector search',
        'embedding' => [0.1, 0.2, 0.3, 0.4] // Identical vector for perfect match
    ]);
    
    // Create memory with different embedding
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'This memory should not be found',
        'embedding' => [0.9, 0.8, 0.7, 0.6] // Very different vector
    ]);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'VectorSearchTest',
        limit: 10,
        threshold: 0.9, // High threshold
        useEmbedding: true
    );
    
    expect($results)->toHaveCount(1);
    expect($results->first()->thing_to_remember)->toContain('should be found by vector search');
});

test('it falls back to database search when no embeddings meet threshold', function () {
    // Mock the embedding manager and driver
    $embeddingDriver = Mockery::mock(EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(true);
    $embeddingDriver->shouldReceive('embed')->with('FallbackTest')->andReturn([0.1, 0.2, 0.3, 0.4]);
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    // Create memory with embedding that won't meet high threshold
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'FallbackTest memory should be found via text search',
        'embedding' => [0.9, 0.8, 0.7, 0.6] // Different vector
    ]);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'FallbackTest',
        limit: 10,
        threshold: 0.99, // Very high threshold that won't be met
        useEmbedding: true,
        fallbackToDatabase: true
    );
    
    // Should fallback and find the memory via text search
    expect($results)->toHaveCount(1);
    expect($results->first()->thing_to_remember)->toContain('FallbackTest');
});

test('it falls back to database search when embedding provider is not healthy', function () {
    // Mock the embedding manager and driver
    $embeddingDriver = Mockery::mock(EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(false);
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'UnhealthyProviderTest memory content',
    ]);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'UnhealthyProviderTest',
        limit: 10,
        useEmbedding: true
    );
    
    // Should fallback to database search
    expect($results)->toHaveCount(1);
    expect($results->first()->thing_to_remember)->toContain('UnhealthyProviderTest');
});

test('it throws exception when vector search fails and fallback is disabled', function () {
    // Mock the embedding manager and driver to throw exception
    $embeddingDriver = Mockery::mock(EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(true);
    $embeddingDriver->shouldReceive('embed')->andThrow(new Exception('Embedding service error'));
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    expect(fn() => $action->handle(
        userId: $this->user->id,
        query: 'ExceptionTest',
        limit: 10,
        useEmbedding: true,
        fallbackToDatabase: false
    ))->toThrow(Exception::class, 'Embedding service error');
});

test('it filters out memories without embeddings during vector search', function () {
    // Mock the embedding manager and driver
    $embeddingDriver = Mockery::mock(EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(true);
    $embeddingDriver->shouldReceive('embed')->with('EmbeddingFilterTest')->andReturn([0.1, 0.2, 0.3, 0.4]);
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    // Create one memory with embedding
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Memory with embedding should be found',
        'embedding' => [0.1, 0.2, 0.3, 0.4]
    ]);
    
    // Create one memory without embedding
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Memory without embedding should not be found',
        'embedding' => null
    ]);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'EmbeddingFilterTest',
        limit: 10,
        threshold: 0.5,
        useEmbedding: true,
        fallbackToDatabase: false
    );
    
    // Should only find the memory with embedding
    expect($results)->toHaveCount(1);
    expect($results->first()->thing_to_remember)->toContain('with embedding should be found');
});

test('it sorts results by similarity score in descending order', function () {
    // Mock the embedding manager and driver
    $embeddingDriver = Mockery::mock(EmbeddingDriverInterface::class);
    $embeddingDriver->shouldReceive('isHealthy')->andReturn(true);
    $embeddingDriver->shouldReceive('embed')->with('SortTest')->andReturn([1.0, 0.0, 0.0]);
    
    $embeddingManager = Mockery::mock(EmbeddingManager::class);
    $embeddingManager->shouldReceive('driver')->andReturn($embeddingDriver);
    
    // Create memories with different similarity scores
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'High similarity memory',
        'embedding' => [0.9, 0.1, 0.1] // High similarity to [1,0,0]
    ]);
    
    UserMemory::factory()->create([
        'user_id' => $this->user->id,
        'thing_to_remember' => 'Low similarity memory',
        'embedding' => [0.1, 0.9, 0.1] // Lower similarity to [1,0,0]
    ]);
    
    $action = new SearchMemoryAction($embeddingManager);
    
    $results = $action->handle(
        userId: $this->user->id,
        query: 'SortTest',
        limit: 10,
        threshold: 0.1,
        useEmbedding: true
    );
    
    expect($results)->toHaveCount(2);
    expect($results->first()->thing_to_remember)->toBe('High similarity memory');
    expect($results->items()[1]->thing_to_remember)->toBe('Low similarity memory');
});

afterEach(function () {
    Mockery::close();
});