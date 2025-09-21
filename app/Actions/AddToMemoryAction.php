<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\UserMemory;
use App\Services\EmbeddingManager;
use Illuminate\Support\Facades\DB;

final class AddToMemoryAction
{
    public function __construct(
        protected EmbeddingManager $embeddingManager
    ) {}

    /**
     * Add a new memory to the user's memory library.
     *
     * @param  int  $userId  The ID of the user
     * @param  string  $content  The content to remember
     * @param  array  $metadata  Additional metadata
     * @param  array  $tags  Tags for the memory
     * @param  string|null  $projectName  The project name
     * @param  string  $documentType  The document type
     * @param  bool  $generateEmbedding  Whether to generate an embedding
     * @throws \Throwable
     */
    public function handle(
        int $userId,
        string $content,
        array $metadata = [],
        array $tags = [],
        ?string $projectName = null,
        string $documentType = 'Memory',
        bool $generateEmbedding = true
    ): UserMemory {
        return DB::transaction(function () use (
            $userId,
            $content,
            $metadata,
            $tags,
            $projectName,
            $documentType,
            $generateEmbedding
        ) {
            // Create the memory record
            $memory = UserMemory::create([
                'user_id' => $userId,
                'thing_to_remember' => $content,
                'title' => $metadata['title'] ?? null,
                'document_type' => $documentType,
                'project_name' => $projectName,
                'tags' => $tags,
            ]);

            // Generate embedding if requested
            if ($generateEmbedding) {
                // Dispatch a job to generate the embedding asynchronously
                GenerateEmbeddingJob::dispatch($memory);
            }

            return $memory;
        });
    }
}
