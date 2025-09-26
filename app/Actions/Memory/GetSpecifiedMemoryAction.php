<?php

namespace App\Actions\Memory;

use App\Models\User;
use App\Services\EmbeddingManager;

final class GetSpecifiedMemoryAction
{
    public function handle(User $user, string $shareToken): ?array
    {
        // Logic to retrieve the most recent memory for the user
        $memory = $user->memories()->firstWhere('share_token', $shareToken);

        if (!$memory) {
            return null; // No memory found
        }

        // This is a placeholder implementation
        return [
            'id' => $memory->share_token,
            'title' => $memory->title,
            'memory' => $memory->thing_to_remember,
            'tags' => $memory->tags,
            'document_type' => $memory->document_type,
            'project_name' => $memory->project_name,
            'is_public' => $memory->is_public,
            'url' => $memory->getPublicUrl(),
            'created_at' => $memory->created_at->diffForHumans(),
        ];
    }
}