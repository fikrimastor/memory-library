<?php

namespace App\Mcp\Tools;

use App\Actions\AddToMemoryAction;
use App\Models\EmbeddingJob;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class AddToMemoryTool extends Tool
{
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

            // Validate required parameters
            $content = $params['content'] ?? $params['thingToRemember'] ?? null;
            if (empty($content)) {
                return Response::json([
                    'success' => false,
                    'error' => 'Content is required',
                    'message' => 'Failed to add memory: content is required',
                ]);
            }

            $action = app(AddToMemoryAction::class);
            
            $userId = $params['user_id'] ?? Auth::id();
            $generateEmbedding = $params['generate_embedding'] ?? $params['generateEmbedding'] ?? true;

            $memory = $action->handle(
                userId: $userId,
                content: $content,
                metadata: $params['metadata'] ?? [],
                tags: $params['tags'] ?? [],
                projectName: $params['project_name'] ?? $params['projectName'] ?? null,
                documentType: $params['document_type'] ?? $params['documentType'] ?? 'Memory',
                generateEmbedding: $generateEmbedding
            );

            // Check if embedding job was requested
            $embeddingQueued = $generateEmbedding;
            $embeddingJobId = null;
            
            // If embedding was requested, try to find the job record
            if ($generateEmbedding) {
                $embeddingJob = EmbeddingJob::where('memory_id', $memory->id)->first();
                $embeddingJobId = $embeddingJob?->id;
            }

            return Response::json([
                'success' => true,
                'memory_id' => $memory->id,
                'embedding_queued' => $embeddingQueued,
                'embedding_job_id' => $embeddingJobId,
                'message' => 'Memory added successfully',
            ]);
        } catch (Throwable $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to add memory',
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
            'content' => $schema->string()->description('The content to remember')->required(false),
            'thingToRemember' => $schema->string()->description('The content to remember (alternative parameter name)')->required(false),
            'user_id' => $schema->integer()->description('The user ID to associate with the memory')->required(false),
            'generate_embedding' => $schema->boolean()->description('Whether to generate an embedding for the memory')->required(false),
            'generateEmbedding' => $schema->boolean()->description('Whether to generate an embedding for the memory (alternative parameter name)')->required(false),
            'metadata' => $schema->object()->description('Additional metadata to store with the memory')->required(false),
            'tags' => $schema->array()->items($schema->string())->description('Tags to associate with the memory')->required(false),
            'project_name' => $schema->string()->description('The project name to associate with the memory')->required(false),
            'projectName' => $schema->string()->description('The project name to associate with the memory (alternative parameter name)')->required(false),
            'document_type' => $schema->string()->description('The document type of the memory')->required(false),
            'documentType' => $schema->string()->description('The document type of the memory (alternative parameter name)')->required(false),
        ];
    }
}
