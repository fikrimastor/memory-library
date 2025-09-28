<?php

namespace App\Mcp\Tools;

use App\Actions\Memory\AddToMemoryAction;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class AddToMemory extends Tool
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
      7. User shares personal stories, experiences, or preferences that could enhance personalization
      8. You create code snippets, configurations, an artifacts, or solutions that might be useful later

      IMPORTANT: Consider using this tool after each user message to build comprehensive context over time. The stored information
      will be available in future sessions to provide personalized responses.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, AddToMemoryAction $action): Response
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'thing_to_remember' => 'required|string|max:10000',
                'metadata' => 'nullable|array',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
                'project_name' => 'nullable|string|max:255',
                'document_type' => 'nullable|string|max:100',
            ], [
                'thing_to_remember.required' => 'Content to remember is required',
                'thing_to_remember.max' => 'Content must be less than 10,000 characters',
                'tags.*.max' => 'Each tag must be less than 50 characters',
                'project_name.max' => 'Project name must be less than 255 characters',
                'document_type.max' => 'Document type must be less than 100 characters',
            ]);

            if (! $user instanceof \App\Models\User) {
                return Response::error('Authentication required to add memory.');
            }

            // Get user ID
            $userId = $user->id;

            $memory = $action->handle(
                userId: $userId,
                content: $validated['thing_to_remember'],
                metadata: $validated['metadata'] ?? [],
                tags: $validated['tags'] ?? [],
                projectName: $validated['project_name'] ?? null,
                documentType: $validated['document_type'] ?? 'Memory',
            );

            $metadata = [
                'success' => true,
                'message' => 'Memory added successfully',
                'title' => $memory->title,
                'project_name' => $memory->project_name,
            ];

            return Response::text(json_encode($metadata));
        } catch (Throwable $e) {
            $metadata = [
                'success' => false,
                'error' => 'creation_error',
                'message' => 'Failed to add memory: '.$e->getMessage(),
            ];

            Log::error("Try to add memory failed: {$e->getMessage()}", $metadata);

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
            'thing_to_remember' => $schema->string()->description('The content to remember')->required(),
            'metadata' => $schema->object()->description('Additional metadata to store with the memory like title of the content'),
            'tags' => $schema->array()->items($schema->string())->description('Tags to associate with the memory'),
            'project_name' => $schema->string()->description('The project name to associate with the memory'),
            'document_type' => $schema->string()->description('The document type of the memory'),
        ];
    }
}
