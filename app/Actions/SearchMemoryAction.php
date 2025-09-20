<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\UserMemory;
use App\Services\EmbeddingManager;
use Illuminate\Pagination\LengthAwarePaginator;

final class SearchMemoryAction
{
    public function __construct(
        protected EmbeddingManager $embeddingManager
    ) {}

    /**
     * Search memories using hybrid search (vector similarity + database search).
     *
     * @param  int  $userId  The ID of the user
     * @param  string  $query  The search query
     * @param  int  $limit  Number of results to return
     * @param  float  $threshold  Similarity threshold for vector search
     * @param  bool  $useEmbedding  Whether to use embedding-based search
     * @param  bool  $fallbackToDatabase  Whether to fallback to database search
     */
    public function handle(
        int $userId,
        string $query,
        int $limit = 10,
        float $threshold = 0.7,
        bool $useEmbedding = true,
        bool $fallbackToDatabase = true
    ): LengthAwarePaginator {
        // If we're using embedding and have a working provider, perform vector search
        if ($useEmbedding && $this->embeddingManager->driver()->isHealthy()) {
            try {
                return $this->vectorSearch($userId, $query, $limit, $threshold);
            } catch (\Exception $e) {
                // If vector search fails and fallback is enabled, use database search
                if ($fallbackToDatabase) {
                    return $this->databaseSearch($userId, $query, $limit);
                }

                throw $e;
            }
        }

        // Fallback to database search
        return $this->databaseSearch($userId, $query, $limit);
    }

    /**
     * Perform vector similarity search.
     */
    protected function vectorSearch(int $userId, string $query, int $limit, float $threshold): LengthAwarePaginator
    {
        // Generate embedding for the query
        $queryEmbedding = $this->embeddingManager->driver()->embed($query);

        // For now, we'll implement a simple database search as a placeholder
        // In a real implementation, this would use vector database functions
        return $this->databaseSearch($userId, $query, $limit);
    }

    /**
     * Perform database-based search.
     */
    protected function databaseSearch(int $userId, string $query, int $limit): LengthAwarePaginator
    {
        return UserMemory::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('thing_to_remember', 'LIKE', "%{$query}%")
                    ->orWhere('title', 'LIKE', "%{$query}%")
                    ->orWhere('project_name', 'LIKE', "%{$query}%");
            })
            ->paginate($limit);
    }
}
