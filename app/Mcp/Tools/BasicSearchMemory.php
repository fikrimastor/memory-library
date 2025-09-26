<?php

namespace App\Mcp\Tools;

use App\Actions\Memory\SearchMemoryAction;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class BasicSearchMemory extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'search';

    /**
     * The tool's description.
     */
    protected string $description = 'This tool search for documents using semantic search.
      1. This tool searches through the vector store to find semantically relevant matches.
      2. Returns a list of search results with basic information. Use the fetch tool to get complete document content.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, SearchMemoryAction $action): Response
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'query' => 'required|string|max:2000',
            ], [
                'query' => 'Query must be less than 2000 characters',
            ]);

            if (! $user instanceof \App\Models\User) {
                return Response::error('Authentication required to search memory.');
            }

            // Get user ID from params or Auth
            $userId = $user->id;
            $query = $validated['query'] ?? '';

            // Validate required parameters
            if (empty($query)) {
                return Response::error(json_encode([
                    'success' => false,
                    'error' => 'validation_error',
                    'message' => 'query is required',
                ]));
            }

            if (! $userId) {
                return Response::error(json_encode([
                    'success' => false,
                    'error' => 'authentication_error',
                    'message' => 'user_id is required when not authenticated',
                ]));
            }

            $searchResults = $action->handle(
                userId: $userId,
                query: $query
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
            'query' => $schema->string()->description('Search query string. Natural language queries work best for semantic search.')->required(),
        ];
    }
}
