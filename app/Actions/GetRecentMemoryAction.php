<?php

namespace App\Actions;

use App\Models\User;
use App\Services\EmbeddingManager;

final class GetRecentMemoryAction
{
    public function __construct(
        protected EmbeddingManager $embeddingManager
    ) {}

    public function handle(User $user): ?array
    {
        // Logic to retrieve the most recent memory for the user
        $memory = $user->memories()->latest()->first();

        if (!$memory) {
            return null; // No memory found
        }

        // This is a placeholder implementation
        return [
            'title' => $memory->title,
            'memory' => $memory->thing_to_remember,
            'tags' => $memory->tags->toArray(),
            'document_type' => $memory->document_type,
            'project_name' => $memory->project_name,
            'created_at' => $memory->created_at->diffForHumans(),
        ];
    }
}