<?php

namespace App\Mcp\Tools;

use App\Actions\AddToMemoryAction;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class AddToMemory extends Tool
{
    public function __construct(
        protected AddToMemoryAction $action
    ) {}

    /**
     * The tool's description.
     */
    protected string $description = 'This tool stores important user information in a persistent memory layer. Use it when:
      1. User explicitly asks to remember something ("remember this...")
      2. You detect significant user preferences, traits, or patterns worth preserving
      3. Technical details, examples, or emotional responses emerge that would be valuable in future interactions
      4. User explicitly asks to remember events, journal, documents, or project details
      5. You generate significant documentation that would be valuable in future interactions
      6. Product Requirements Documents, Technical Specs, Best Practise documentation are created, IMPORTANT: remember the full content of these documents, don\'t just store a summary

      Consider using this tool after each user message to build comprehensive context over time. The stored information
      will be available in future sessions to provide personalized responses.';

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
            $content = $params['thing_to_remember'] ?? '';
            if (empty($content)) {
                return Response::json([
                    'success' => false,
                    'error' => 'validation_error',
                    'message' => 'thing_to_remember is required',
                ]);
            }

            if (! $userId) {
                return Response::json([
                    'success' => false,
                    'error' => 'authentication_error',
                    'message' => 'user_id is required when not authenticated',
                ]);
            }

            $generateEmbedding = $params['generate_embedding'] ?? true;

            $memory = $this->action->handle(
                userId: $userId,
                content: $content,
                metadata: $params['metadata'] ?? [],
                tags: $params['tags'] ?? [],
                projectName: $params['project_name'] ?? null,
                documentType: $params['document_type'] ?? 'Memory',
                generateEmbedding: $generateEmbedding
            );

            return Response::json([
                'success' => true,
                'message' => 'Memory added successfully',
                'title' => $memory->title,
                'project_name' => $memory->project_name,
                'embedding_queued' => $generateEmbedding,
            ]);
        } catch (Throwable $e) {
            Log::error("Try to add memory failed: {$e->getMessage()}");

            return Response::json([
                'success' => false,
                'error' => 'creation_error',
                'message' => 'Failed to add memory: '.$e->getMessage(),
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
            'thing_to_remember' => $schema->string()->description('The content to remember')->required(),
            'metadata' => $schema->object()->description('Additional metadata to store with the memory like title of the content')->required(),
            'tags' => $schema->array()->items($schema->string())->description('Tags to associate with the memory')->required(),
            'project_name' => $schema->string()->description('The project name to associate with the memory')->required(),
            'document_type' => $schema->string()->description('The document type of the memory')->required(),
            'generate_embedding' => $schema->boolean()->description('Whether to generate an embedding for this memory'),
        ];
    }
}
