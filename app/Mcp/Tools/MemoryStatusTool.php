<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\ProviderHealth;
use App\Models\UserMemory;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MemoryStatusTool
{
    /**
     * Get status information about user's memory library and provider health.
     *
     * @param  array  $params  Tool parameters
     * @return array Response
     */
    public function handle(array $params): array
    {
        try {
            $userId = $params['user_id'] ?? Auth::id();

            // Get memory statistics
            $memoryCount = UserMemory::where('user_id', $userId)->count();
            $recentMemories = UserMemory::where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'created_at']);

            // Get provider health status
            $providerHealth = ProviderHealth::all();

            return [
                'success' => true,
                'memory_stats' => [
                    'total_count' => $memoryCount,
                    'recent_memories' => $recentMemories,
                ],
                'provider_health' => $providerHealth,
                'message' => 'Status retrieved successfully',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve status',
            ];
        }
    }
}
