<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\EmbeddingJob;
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
            
            // Get recent memories
            $recentMemories = UserMemory::where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'created_at', 'document_type', 'project_name']);
                
            // Get memory statistics by document type
            $memoryByType = UserMemory::where('user_id', $userId)
                ->selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->get()
                ->keyBy('document_type')
                ->map(fn($item) => $item->count)
                ->toArray();
                
            // Get memory statistics by project
            $memoryByProject = UserMemory::where('user_id', $userId)
                ->whereNotNull('project_name')
                ->selectRaw('project_name, COUNT(*) as count')
                ->groupBy('project_name')
                ->get()
                ->keyBy('project_name')
                ->map(fn($item) => $item->count)
                ->toArray();

            // Get provider health status
            $providerHealth = ProviderHealth::all();
            
            // Get embedding job statistics
            $pendingJobs = EmbeddingJob::where('status', 'pending')->count();
            $processingJobs = EmbeddingJob::where('status', 'processing')->count();
            $completedJobs = EmbeddingJob::where('status', 'completed')->count();
            $failedJobs = EmbeddingJob::where('status', 'failed')->count();

            return [
                'success' => true,
                'memory_stats' => [
                    'total_count' => $memoryCount,
                    'recent_memories' => $recentMemories,
                    'by_document_type' => $memoryByType,
                    'by_project' => $memoryByProject,
                ],
                'provider_health' => $providerHealth,
                'embedding_jobs' => [
                    'pending' => $pendingJobs,
                    'processing' => $processingJobs,
                    'completed' => $completedJobs,
                    'failed' => $failedJobs,
                ],
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
