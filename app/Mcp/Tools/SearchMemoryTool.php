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
            $action = app(SearchMemoryAction::class);

            $results = $action->handle(
                userId: $params['user_id'] ?? Auth::id(),
                query: $params['query'] ?? '',
                limit: $params['limit'] ?? 10,
                threshold: $params['threshold'] ?? 0.7,
                useEmbedding: $params['use_embedding'] ?? $params['useEmbedding'] ?? true,
                fallbackToDatabase: $params['fallback_to_database'] ?? $params['fallbackToDatabase'] ?? true
            );

            return [
                'success' => true,
                'results' => $results->items(),
                'total' => $results->total(),
                'search_method' => 'hybrid',
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
