<?php

namespace App\Mcp\Tools;

use App\Actions\Memory\SearchMemoryAction;
use App\Mcp\Concerns\ChecksFeatureStatus;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class SearchMemory extends Tool
{
    use ChecksFeatureStatus;

    /**
     * The tool's name.
     */
    protected string $name = 'advanced-search';

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
    public function handle(Request $request, SearchMemoryAction $action): Response
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'query' => 'required|string|max:1000',
                'limit' => 'nullable|integer|min:1|max:10',
                'threshold' => 'nullable|numeric|min:0|max:1',
                'use_embedding' => 'nullable|boolean',
                'fallback_to_database' => 'nullable|boolean',
                'use_hybrid_search' => 'nullable|boolean',
                'vector_weight' => 'nullable|numeric|min:0|max:1',
                'text_weight' => 'nullable|numeric|min:0|max:1',
            ], [
                'query.required' => 'Search query is required',
                'query.max' => 'Search query must be less than 1,000 characters',
                'limit.min' => 'Limit must be at least 1',
                'limit.max' => 'Limit cannot exceed 100',
                'threshold.min' => 'Threshold must be between 0 and 1',
                'threshold.max' => 'Threshold must be between 0 and 1',
                'vector_weight.min' => 'Vector weight must be between 0 and 1',
                'vector_weight.max' => 'Vector weight must be between 0 and 1',
                'text_weight.min' => 'Text weight must be between 0 and 1',
                'text_weight.max' => 'Text weight must be between 0 and 1',
            ]);

            if (! $user instanceof \App\Models\User) {
                return Response::error('Authentication required to search memory.');
            }

            // Get user ID
            $userId = $user->id;

            $searchResults = $action->handle(
                userId: $userId,
                query: $validated['query'],
                limit: $validated['limit'] ?? 10,
                threshold: $validated['threshold'] ?? 0.7,
                useEmbedding: $validated['use_embedding'] ?? true,
                fallbackToDatabase: $validated['fallback_to_database'] ?? true,
                useHybridSearch: $validated['use_hybrid_search'] ?? config('embedding.hybrid_search.enabled', false),
                vectorWeight: $validated['vector_weight'] ?? config('embedding.hybrid_search.vector_weight', 0.7),
                textWeight: $validated['text_weight'] ?? config('embedding.hybrid_search.text_weight', 0.3)
            );

            return Response::text(json_encode($searchResults));
        } catch (Throwable $e) {
            $metadata = [
                'success' => false,
                'error' => 'search_error',
                'message' => 'Failed to search memory: '.$e->getMessage(),
            ];

            Log::error("Search memory failed: {$e->getMessage()}", $metadata);

            return Response::error(json_encode($metadata));
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
            'use_embedding' => $schema->boolean()->description('Whether to enable vector search (defaults to true)'),
            'fallback_to_database' => $schema->boolean()->description('Fallback to SQL search when embeddings miss (defaults to true)'),
            'use_hybrid_search' => $schema->boolean()->description('Enable hybrid search mode using embeddings and text search'),
            'vector_weight' => $schema->number()->description('Weight for vector search results in hybrid mode (0.0-1.0)'),
            'text_weight' => $schema->number()->description('Weight for text search results in hybrid mode (0.0-1.0)'),
        ];
    }
}
