<?php

namespace App\Mcp\Concerns;

use App\Models\McpFeature;
use Laravel\Mcp\Request;

trait ChecksFeatureStatus
{
    /**
     * Determine if this feature should be registered for the given request.
     *
     * Checks the user's feature settings to see if this tool/resource/prompt is active.
     */
    public function shouldRegister(Request $request): bool
    {
        $user = $request->user();

        if (! $user instanceof \App\Models\User) {
            return false;
        }

        // Determine the feature type based on the class
        $type = match (true) {
            $this instanceof \Laravel\Mcp\Server\Tool => 'tool',
            $this instanceof \Laravel\Mcp\Server\Resource => 'resource',
            $this instanceof \Laravel\Mcp\Server\Prompt => 'prompt',
            default => null,
        };

        if (! $type) {
            // Unknown type, allow by default
            return true;
        }

        // Check if this feature is active for the user
        $feature = McpFeature::where('type', $type)
            ->where('class_name', static::class)
            ->first();

        if (! $feature) {
            // If not in database, allow by default (backward compatibility)
            return true;
        }

        return $feature->isActiveForUser($user);
    }
}
