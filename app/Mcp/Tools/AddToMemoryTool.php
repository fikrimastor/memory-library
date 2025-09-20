<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\AddToMemoryAction;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AddToMemoryTool
{
    /**
     * Add content to user's memory library with optional embedding generation.
     *
     * @param  array  $params  Tool parameters
     * @return array Response
     */
    public function handle(array $params): array
    {
        try {
            $action = app(AddToMemoryAction::class);

            $memory = $action->handle(
                userId: $params['user_id'] ?? Auth::id(),
                content: $params['content'] ?? $params['thingToRemember'] ?? '',
                metadata: $params['metadata'] ?? [],
                tags: $params['tags'] ?? [],
                projectName: $params['project_name'] ?? $params['projectName'] ?? null,
                documentType: $params['document_type'] ?? $params['documentType'] ?? 'Memory',
                generateEmbedding: $params['generate_embedding'] ?? $params['generateEmbedding'] ?? true
            );

            return [
                'success' => true,
                'memory_id' => $memory->id,
                'embedding_queued' => true,
                'message' => 'Memory added successfully',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to add memory',
            ];
        }
    }
}
