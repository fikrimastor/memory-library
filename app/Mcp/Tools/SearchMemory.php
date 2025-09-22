<?php

namespace App\Mcp\Tools;

use App\Actions\SearchMemoryAction;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class SearchMemory extends Tool
{
    public function __construct(
        protected SearchMemoryAction $action
    ) {}

    /**
     * The tool's description.
     */
    protected string $description = 'This tool searches the user\'s persistent memory layer for relevant information, preferences, and past context.
      It uses semantic matching to find connections between your query and stored memories, even when exact keywords don\'t match.
      Use this tool when:
      1. User explicitly asking ("Remembering...")
      2. You need historical context about the user\'s preferences or past interactions
      3. The user refers to something they previously mentioned or asked you to remember
      4. You need to verify if specific information about the user exists in memory';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            $params = $request->all();

            // Get user ID from params or Auth
            $userId = Auth::id();

            // Validate required parameters
            $query = $params['query'] ?? '';
            if (empty($query)) {
                return Response::json([
                    'success' => false,
                    'error' => 'validation_error',
                    'message' => 'query is required',
                ]);
            }

            if (! $userId) {
                return Response::json([
                    'success' => false,
                    'error' => 'authentication_error',
                    'message' => 'user_id is required when not authenticated',
                ]);
            }

            $limit = $params['limit'] ?? 10;
            $threshold = $params['threshold'] ?? 0.7;
            $useEmbedding = $params['use_embedding'] ?? true;
            $fallbackToDatabase = $params['fallback_to_database'] ?? true;

            $useHybridSearch = $params['use_hybrid_search'] ?? config('embedding.hybrid_search.enabled', false);
            $vectorWeight = $params['vector_weight'] ?? config('embedding.hybrid_search.vector_weight', 0.7);
            $textWeight = $params['text_weight'] ?? config('embedding.hybrid_search.text_weight', 0.3);

            $results = $this->action->handle(
                userId: $userId,
                query: $query,
                limit: $limit,
                threshold: $threshold,
                useEmbedding: $useEmbedding,
                fallbackToDatabase: $fallbackToDatabase,
                useHybridSearch: $useHybridSearch,
                vectorWeight: $vectorWeight,
                textWeight: $textWeight
            );

            // Determine the actual search method used
            $searchMethod = 'database';
            if ($useHybridSearch) {
                $searchMethod = 'hybrid';
            } elseif ($useEmbedding) {
                $searchMethod = 'vector';
                // In a future implementation, we could determine if fallback was used
            }

            // Override if use_embedding is explicitly false
            if ($useEmbedding === false) {
                $searchMethod = 'database';
            }

            $totalResults = $results->total();

            // Format results as array
            $formattedResults = [];
            foreach ($results as $result) {
                $formattedResult = [
                    'title' => $result->title,
                    'thing_to_remember' => $result->thing_to_remember,
                    'tags' => $result->tags ?? [],
                    'document_type' => $result->document_type,
                    'project_name' => $result->project_name,
                    'created_at' => $result->created_at->toISOString(),
                ];

                // Add search scores if available
                if (isset($result->hybrid_score)) {
                    $formattedResult['hybrid_score'] = round($result->hybrid_score, 3);
                    $formattedResult['vector_score'] = round($result->vector_score, 3);
                    $formattedResult['text_score'] = round($result->text_score, 3);
                } elseif (isset($result->similarity)) {
                    $formattedResult['similarity'] = round($result->similarity, 3);
                }

                $formattedResults[] = $formattedResult;
            }

            return Response::json([
                'total' => $totalResults,
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'threshold' => $threshold,
                'search_method' => $searchMethod,
                'results' => $formattedResults,
            ]);
        } catch (Throwable $e) {
            return Response::json([
                'success' => false,
                'error' => 'search_error',
                'message' => 'Failed to search memory: '.$e->getMessage(),
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
            'limit' => $schema->integer()->description('Maximum number of results to return'),
            'threshold' => $schema->number()->description('Similarity threshold for vector search'),
            'vector_weight' => $schema->number()->description('Weight for vector search results in hybrid mode (0.0-1.0)'),
            'text_weight' => $schema->number()->description('Weight for text search results in hybrid mode (0.0-1.0)'),
        ];
    }
}
