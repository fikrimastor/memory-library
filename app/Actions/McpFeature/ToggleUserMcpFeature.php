<?php

namespace App\Actions\McpFeature;

use App\Models\McpFeature;
use App\Models\User;
use App\Models\UserMcpFeatureSetting;

class ToggleUserMcpFeature
{
    /**
     * Toggle an MCP feature for a user.
     *
     * @param  User  $user  The user to toggle the feature for
     * @param  int  $featureId  The MCP feature ID
     * @param  bool|null  $isActive  Force a specific state, or null to toggle
     * @return UserMcpFeatureSetting The updated or created setting
     */
    public function handle(User $user, int $featureId, ?bool $isActive = null): UserMcpFeatureSetting
    {
        $feature = McpFeature::findOrFail($featureId);

        // Find or create the setting
        $setting = UserMcpFeatureSetting::firstOrNew([
            'user_id' => $user->id,
            'mcp_feature_id' => $featureId,
        ]);

        // If no state is specified, toggle the current state
        if ($isActive === null) {
            // If setting doesn't exist yet, toggle from the default
            if (! $setting->exists) {
                $isActive = ! $feature->is_active_by_default;
            } else {
                $isActive = ! $setting->is_active;
            }
        }

        $setting->is_active = $isActive;
        $setting->save();

        return $setting;
    }
}
