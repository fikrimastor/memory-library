<?php

namespace App\Mcp\Tools;

use App\Actions\Memory\GetSpecifiedMemoryAction;
use App\Mcp\Concerns\ChecksFeatureStatus;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class FetchMemory extends Tool
{
    use ChecksFeatureStatus;

    /**
     * The tool's name.
     */
    protected string $name = 'fetch';

    /**
     * The tool's description.
     */
    protected string $description = 'The user\'s specified memory stored. 
      Use this tool when:
      1. You need full context about the user\'s specific memory or past interactions
      2. You need to verify if specific information about the user stored in memory';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, GetSpecifiedMemoryAction $action): Response
    {
        $user = $request->user();
        $validated = $request->validate([
            'id' => 'required|string|exists:user_memories,share_token',
        ], [
            'id.required' => 'Memory ID is required',
            'id.exists' => 'Memory with the given ID does not exist',
        ]);

        if (! $user instanceof \App\Models\User) {
            return Response::error('Authentication required to fetch memory.');
        }

        // Get user ID from params or Auth
        $recentMemory = $action->handle($user, $validated['id']);

        if (! $recentMemory) {
            return Response::error(json_encode([
                'success' => false,
                'error' => 'not_found',
                'message' => 'No memory found for the given ID.',
            ]));
        }

        $documentType = str($recentMemory['document_type'])->headline()->value();
        $data = [
            'id' => $validated['id'],
            'title' => $recentMemory['title'] ?? "({$documentType}-{$recentMemory['project_name']}-{$recentMemory['created_at']})",
            'text' => $recentMemory['memory'],
            'url' => $recentMemory['is_public'] ? $recentMemory['url'] : '',
            'metadata' => [
                'tags' => $recentMemory['tags'],
                'document_type' => $recentMemory['document_type'],
                'project_name' => $recentMemory['project_name'],
                'created_at' => $recentMemory['created_at'],
            ],
        ];

        return Response::text(json_encode($data));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->description('The unique share token for the memory item')->required(),
        ];
    }
}
