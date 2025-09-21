<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\UserMemory;
use App\Services\EmbeddingManager;
use RuntimeException;

final class GenerateEmbeddingAction
{
    public function __construct(
        protected EmbeddingManager $embeddingManager
    ) {}

    /**
     * Generate an embedding for a memory.
     *
     * @param  UserMemory  $memory  The memory to generate an embedding for
     * @param  string|null  $provider  The provider to use (null for default)
     * @return array The generated embedding
     *
     * @throws RuntimeException
     */
    public function handle(UserMemory $memory, ?string $provider = null): array
    {
        try {
            // Get the embedding driver
            $driver = $provider ? $this->embeddingManager->driver($provider) : $this->embeddingManager->driver();

            // Generate the embedding
            $embedding = $driver->embed($memory->thing_to_remember);

            // Update the memory with the embedding
            $embeddingJob = $memory->embeddingJob;
            $embeddingJob?->update(['embedding' => $embedding]);

            return $embedding;
        } catch (\Exception $e) {
            // Log the error and rethrow
            throw new RuntimeException('Failed to generate embedding: '.$e->getMessage(), 0, $e);
        }
    }
}
