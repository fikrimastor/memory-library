<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\SearchMemoryAction;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SearchMemoryTool
{
    /**
     * Search user's memory library with hybrid search capabilities.
     *
     * @param  array  $params  Tool parameters
     * @return array Response
     */
    public function handle(array $params): array
    {
        try {
            // Validate required parameters
            $query = $params['query'] ?? '';
            if (empty($query)) {
                return [
                    'success' => false,
                    'error' => 'Query is required',
                    'message' => 'Failed to search memory: query is required',
                ];
            }

            $action = app(SearchMemoryAction::class);

            $userId = $params['user_id'] ?? Auth::id();
            $limit = $params['limit'] ?? 10;
            $threshold = $params['threshold'] ?? 0.7;
            $useEmbedding = $params['use_embedding'] ?? $params['useEmbedding'] ?? true;
            $fallbackToDatabase = $params['fallback_to_database'] ?? $params['fallbackToDatabase'] ?? true;

            $results = $action->handle(
                userId: $userId,
                query: $query,
                limit: $limit,
                threshold: $threshold,
                useEmbedding: $useEmbedding,
                fallbackToDatabase: $fallbackToDatabase
            );

            // Determine the actual search method used
            $searchMethod = 'database';
            if ($useEmbedding) {
                $searchMethod = 'vector';
                // In a future implementation, we could determine if fallback was used
            }

            return [
                'success' => true,
                'results' => $results->items(),
                'total' => $results->total(),
                'search_method' => $searchMethod,
                'query' => $query,
                'limit' => $limit,
                'threshold' => $threshold,
                'message' => 'Search completed successfully',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to search memory',
            ];
        }
    }
}
