<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\AddToMemoryAction;
use App\Models\EmbeddingJob;
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
            // Validate required parameters
            $content = $params['content'] ?? $params['thingToRemember'] ?? null;
            if (empty($content)) {
                return [
                    'success' => false,
                    'error' => 'Content is required',
                    'message' => 'Failed to add memory: content is required',
                ];
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

            return [
                'success' => true,
                'memory_id' => $memory->id,
                'embedding_queued' => $embeddingQueued,
                'embedding_job_id' => $embeddingJobId,
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
