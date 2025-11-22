<?php

namespace App\Actions\McpFeature;

use App\Models\McpFeature;
use App\Models\User;
use Illuminate\Support\Collection;

class GetActiveUserMcpFeatures
{
    /**
     * Get active MCP features for a user by type.
     *
     * This merges system defaults with user preferences.
     * If a user has no setting for a feature, the default is used.
     *
     * @param  User  $user  The user to get features for
     * @param  string  $type  The feature type ('tool', 'resource', or 'prompt')
     * @return Collection Collection of active McpFeature models
     */
    public function handle(User $user, string $type): Collection
    {
        // Get all features of this type
        $allFeatures = McpFeature::where('type', $type)->get();

        // Filter to only active features for this user
        return $allFeatures->filter(function (McpFeature $feature) use ($user) {
            return $feature->isActiveForUser($user);
        });
    }

    /**
     * Get all active features for a user (all types).
     */
    public function handleAll(User $user): Collection
    {
        return McpFeature::all()->filter(function (McpFeature $feature) use ($user) {
            return $feature->isActiveForUser($user);
        })->groupBy('type');
    }
}
