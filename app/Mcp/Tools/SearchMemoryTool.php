<?php

namespace App\Mcp\Tools;

use App\Actions\SearchMemoryAction;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class SearchMemoryTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Search the user\'s persistent memory layer for relevant information, preferences, and past context.
      It uses semantic matching to find connections between your query and stored memories, even when exact keywords don\'t match.
      Use this tool when:
      1. You need historical context about the user\'s preferences or past interactions
      2. The user refers to something they previously mentioned or asked you to remember
      3. You need to verify if specific information about the user exists in memory';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            $params = $request->all();

            // Validate required parameters
            $query = $params['query'] ?? '';
            if (empty($query)) {
                return Response::json([
                    'success' => false,
                    'error' => 'Query is required',
                    'message' => 'Failed to search memory: query is required',
                ]);
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

            return Response::json([
                'success' => true,
                'results' => $results->items(),
                'total' => $results->total(),
                'search_method' => $searchMethod,
                'query' => $query,
                'limit' => $limit,
                'threshold' => $threshold,
                'message' => 'Search completed successfully',
            ]);
        } catch (Throwable $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to search memory',
            ]);
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The search query')->required(),
            'user_id' => $schema->integer()->description('The user ID to search for memories')->required(false),
            'limit' => $schema->integer()->description('Maximum number of results to return')->required(false),
            'threshold' => $schema->number()->description('Similarity threshold for vector search')->required(false),
            'use_embedding' => $schema->boolean()->description('Whether to use embedding search')->required(false),
            'useEmbedding' => $schema->boolean()->description('Whether to use embedding search (alternative parameter name)')->required(false),
            'fallback_to_database' => $schema->boolean()->description('Whether to fallback to database search')->required(false),
            'fallbackToDatabase' => $schema->boolean()->description('Whether to fallback to database search (alternative parameter name)')->required(false),
        ];
    }
}
