<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\UserMemory;
use App\Services\EmbeddingManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

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
        if ($useEmbedding) {
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
     * Perform vector similarity search using cosine similarity.
     */
    protected function vectorSearch(int $userId, string $query, int $limit, float $threshold): LengthAwarePaginator
    {
        // Generate embedding for the query
        $queryEmbedding = $this->embeddingManager->driver()->embed($query);

        // Get all memories with embeddings for this user
        $memories = UserMemory::with('embeddingJob')
            ->where('user_id', $userId)
            ->whereHas('embeddingJob', fn ($query) => $query->whereNotNull('embedding'))
            ->get();

        // Calculate similarities and filter by threshold
        $similarities = $memories->map(function ($memory) use ($queryEmbedding, $threshold) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $memory->embeddingJob->embedding);
            
            // Only include memories that meet the threshold
            if ($similarity >= $threshold) {
                $memory->similarity = $similarity;
                return $memory;
            }
            
            return null;
        })->filter()->sortByDesc('similarity');

        // If no similar memories found, fall back to database search if needed
        if ($similarities->isEmpty()) {
            return $this->databaseSearch($userId, $query, $limit);
        }

        // Convert to paginated results
        return $this->paginateCollection($similarities, $limit);
    }

    /**
     * Calculate cosine similarity between two embedding vectors.
     *
     * @param  array  $vectorA
     * @param  array  $vectorB
     * @return float
     */
    protected function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        // Ensure both vectors have the same length
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        // Handle empty vectors
        if (empty($vectorA) || empty($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $a = (float) $vectorA[$i];
            $b = (float) $vectorB[$i];
            
            $dotProduct += $a * $b;
            $magnitudeA += $a * $a;
            $magnitudeB += $b * $b;
        }

        // Avoid division by zero
        $magnitude = sqrt($magnitudeA) * sqrt($magnitudeB);
        if ($magnitude == 0) {
            return 0.0;
        }

        return $dotProduct / $magnitude;
    }

    /**
     * Convert a collection to a paginated result.
     */
    protected function paginateCollection(Collection $collection, int $perPage): LengthAwarePaginator
    {
        $currentPage = Paginator::resolveCurrentPage();
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        return new LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    protected function databaseSearch(int $userId, string $query, int $limit): LengthAwarePaginator
    {
        // Split the query into individual words for better matching
        $words = array_filter(array_map('trim', explode(' ', $query)));
        
        return UserMemory::where('user_id', $userId)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    // Skip very short words to avoid too many false positives
                    if (strlen($word) < 3) {
                        continue;
                    }
                    
                    $q->orWhere(function ($subQuery) use ($word) {
                        $subQuery->where('thing_to_remember', 'LIKE', "%{$word}%")
                                ->orWhere('title', 'LIKE', "%{$word}%")
                                ->orWhere('project_name', 'LIKE', "%{$word}%")
                                ->orWhereJsonContains('tags', $word);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->paginate($limit);
    }
}