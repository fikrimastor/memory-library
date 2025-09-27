<?php

declare(strict_types=1);

namespace App\Actions\Memory;

use App\DTO\MemorySearchResultDTO;
use App\Models\UserMemory;
use App\Services\EmbeddingManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        bool $fallbackToDatabase = true,
        bool $useHybridSearch = false,
        float $vectorWeight = 0.7,
        float $textWeight = 0.3
    ): array {
        // Determine the actual search method used
        $searchMethod = 'database';
        if ($useHybridSearch) {
            $searchMethod = 'hybrid';
        } elseif ($useEmbedding) {
            $searchMethod = 'vector';
        }

        // Override if use_embedding is explicitly false
        if ($useEmbedding === false) {
            $searchMethod = 'database';
        }

        // If hybrid search is enabled, combine both vector and text search
        if ($useHybridSearch) {
            try {
                $results = $this->hybridSearch($userId, $query, $limit, $threshold, $vectorWeight, $textWeight);
                return $this->buildResponse($results, $query, $limit, $threshold, $searchMethod);
            } catch (\Exception $e) {
                // If hybrid search fails and fallback is enabled, use database search
                if ($fallbackToDatabase) {
                    Log::warning('Hybrid search failed, falling back to database search', [
                        'query' => $query,
                        'error' => $e->getMessage()
                    ]);
                    $results = $this->databaseSearch($userId, $query, $limit);
                    return $this->buildResponse($results, $query, $limit, $threshold, 'database');
                }

                throw $e;
            }
        }

        // If we're using embedding and have a working provider, perform vector search
        if ($useEmbedding) {
            try {
                $results = $this->vectorSearch($userId, $query, $limit, $threshold);
                return $this->buildResponse($results, $query, $limit, $threshold, $searchMethod);
            } catch (\Exception $e) {
                // If vector search fails and fallback is enabled, use database search
                if ($fallbackToDatabase) {
                    $results = $this->databaseSearch($userId, $query, $limit);
                    return $this->buildResponse($results, $query, $limit, $threshold, 'database');
                }

                throw $e;
            }
        }

        // Fallback to database search
        $results = $this->databaseSearch($userId, $query, $limit);
        return $this->buildResponse($results, $query, $limit, $threshold, $searchMethod);
    }

    /**
     * Perform hybrid search combining vector similarity and text matching.
     */
    protected function hybridSearch(
        int $userId,
        string $query,
        int $limit,
        float $threshold,
        float $vectorWeight,
        float $textWeight
    ): LengthAwarePaginator {
        // Run both searches in parallel
        $vectorResults = collect();
        $textResults = collect();

        // Get vector search results
        try {
            $queryEmbedding = $this->embeddingManager->driver()->embed($query);

            $memories = UserMemory::with(['embeddingJob'])
                ->where('user_id', $userId)
                ->whereHas('embeddingJob', fn ($q) =>
                    $q->whereNotNull('embedding')->where('status', 'completed')
                )
                ->get();

            foreach ($memories as $memory) {
                if (!$memory->embeddingJob || !$memory->embeddingJob->embedding) {
                    continue;
                }

                $similarity = $this->cosineSimilarity($queryEmbedding, $memory->embeddingJob->embedding);
                Log::debug("while cosineSimilarity {$query}", compact('similarity', 'threshold'));

                if ($similarity >= $threshold) {
                    $memory->vector_score = $similarity;
                    $vectorResults->put($memory->id, $memory);
                }
            }
        } catch (\Exception $e) {
            Log::info('Vector search failed in hybrid mode, using text search only', [
                'error' => $e->getMessage()
            ]);
        }

        // Get text search results
        $words = array_filter(array_map('trim', explode(' ', $query)));
        $textMemories = UserMemory::where('user_id', $userId)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    if (strlen($word) < 3) continue;

                    $q->orWhere(function ($subQuery) use ($word) {
                        $subQuery->where('thing_to_remember', 'LIKE', "%{$word}%")
                                ->orWhere('title', 'LIKE', "%{$word}%")
                                ->orWhere('project_name', 'LIKE', "%{$word}%")
                                ->orWhereJsonContains('tags', $word);
                    });
                }
            })
            ->get();

        // Calculate text relevance scores
        foreach ($textMemories as $memory) {
            $textScore = $this->calculateTextRelevanceScore($memory, $words);
            $threshold = config('embedding.hybrid_search.text_weight');

            if ($textScore > $threshold) {
                Log::debug("while calculateTextRelevanceScore {$query}", compact('textScore', 'threshold'));
                $memory->text_score = $textScore;
                $textResults->put($memory->id, $memory);
            }
        }

        // Combine results with weighted scoring
        $combinedResults = collect();

        // Get all unique memory IDs
        $allIds = $vectorResults->keys()->merge($textResults->keys())->unique();

        foreach ($allIds as $memoryId) {
            $vectorResult = $vectorResults->get($memoryId);
            $textResult = $textResults->get($memoryId);

            // Use the memory from either result set
            $memory = $vectorResult ?? $textResult;

            // Calculate hybrid score
            $vectorScore = $vectorResult?->vector_score ?? 0;
            $textScore = $textResult?->text_score ?? 0;

            $hybridScore = ($vectorScore * $vectorWeight) + ($textScore * $textWeight);

            $memory->hybrid_score = $hybridScore;
            $memory->vector_score = $vectorScore;
            $memory->text_score = $textScore;

            $combinedResults->put($memoryId, $memory);
        }

        // Sort by hybrid score and paginate
        $sortedResults = $combinedResults->sortByDesc('hybrid_score')->values();

        Log::debug("use hybrid search {$combinedResults->count()}");

        return $this->paginateCollection($sortedResults, $limit);
    }

    /**
     * Calculate text relevance score based on keyword matches.
     */
    protected function calculateTextRelevanceScore(UserMemory $memory, array $words): float
    {
        $score = 0.0;
        $maxScore = count($words);

        if ($maxScore === 0) return 0.0;

        $weights = config('embedding.hybrid_search.text_relevance_weights', [
            'title' => 3.0,
            'tags' => 2.5,
            'project_name' => 2.0,
            'content' => 1.0,
        ]);

        foreach ($words as $word) {
            if (strlen($word) < 3) continue;

            $word = strtolower($word);

            // Title matches are weighted higher
            if (str_contains(strtolower($memory->title ?? ''), $word)) {
                $score += $weights['title'];
            }

            // Project name matches
            if (str_contains(strtolower($memory->project_name ?? ''), $word)) {
                $score += $weights['project_name'];
            }

            // Tag matches (exact match in JSON array)
            if (in_array($word, array_map('strtolower', $memory->tags ?? []))) {
                $score += $weights['tags'];
            }

            // Content matches
            if (str_contains(strtolower($memory->thing_to_remember ?? ''), $word)) {
                $score += $weights['content'];
            }
        }

        // Normalize score (0.0 to 1.0)
        return min($score / ($maxScore * $weights['title']), 1.0);
    }

    /**
     * Perform vector similarity search using cosine similarity.
     */
    protected function vectorSearch(int $userId, string $query, int $limit, float $threshold): LengthAwarePaginator
    {
        // Generate embedding for the query with timeout protection
        try {
            $queryEmbedding = $this->embeddingManager->driver()->embed($query);
        } catch (\Exception $e) {
            Log::warning('Failed to generate query embedding, falling back to database search', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            // Fall back to database search immediately
            return $this->databaseSearch($userId, $query, $limit);
        }

        Log::debug("use embedding {$query}", $queryEmbedding);

        // Get all memories with embeddings for this user
        $memories = UserMemory::with(['embeddingJob'])
            ->where('user_id', $userId)
            ->whereHas('embeddingJob', fn ($query) => $query->whereNotNull('embedding')
                ->where('status', 'completed'))
            ->get();

        Log::debug("load memories {$query}", $memories->only('title')->toArray());

        // Calculate similarities and filter by threshold
        $similarities = $memories->map(function ($memory) use ($queryEmbedding, $threshold, $query) {
            if (!$memory->embeddingJob || !$memory->embeddingJob->embedding) {
                return null;
            }

            $similarity = $this->cosineSimilarity($queryEmbedding, $memory->embeddingJob->embedding);

            Log::debug("Similarity: {$query}", [
                'memory_id' => $memory->id,
                'similarity' => $similarity,
                'threshold' => $threshold
            ]);
            
            // Only include memories that meet the threshold
            if ($similarity >= $threshold) {
                Log::debug('enter similarity '.$similarity);
                $memory->similarity = $similarity;
                return $memory;
            }
            
            return null;
        })->filter()->sortByDesc('similarity');

        Log::debug("Similarity result query: {$query}", $similarities->toArray());

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
        Log::debug("use database search: {$query}");
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

    /**
     * Build response array from paginated results
     */
    protected function buildResponse(
        LengthAwarePaginator $results,
        string $query,
        int $limit,
        float $threshold,
        string $searchMethod
    ): array {
        $dtoResults = [];
        foreach ($results->items() as $memory) {
            // Extract scores safely from dynamic properties
            $similarity = $this->getScoreFromMemory($memory, 'similarity');
            $hybrid_score = $this->getScoreFromMemory($memory, 'hybrid_score');
            $vector_score = $this->getScoreFromMemory($memory, 'vector_score');
            $text_score = $this->getScoreFromMemory($memory, 'text_score');

            $dto = MemorySearchResultDTO::fromUserMemory(
                memory: $memory,
                similarity: $similarity,
                hybrid_score: $hybrid_score,
                vector_score: $vector_score,
                text_score: $text_score
            );

            $dtoResults[] = $dto->toArray();
        }

        return [
            'metadata' => [
                'total' => $results->total(),
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'threshold' => $threshold,
                'search_method' => $searchMethod,
            ],
            'results' => $dtoResults,
        ];
    }

    /**
     * Safely extract score from memory object if it exists as a dynamic property
     */
    protected function getScoreFromMemory(object $memory, string $scoreType): ?float
    {
        if (!property_exists($memory, $scoreType)) {
            return null;
        }

        $value = $memory->$scoreType;
        return is_numeric($value) ? (float) $value : null;
    }
}