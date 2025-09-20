<?php

namespace App\Mcp\Tools;

use App\Models\EmbeddingJob;
use App\Models\ProviderHealth;
use App\Models\UserMemory;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class MemoryStatus extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Get status information about user\'s memory library and provider health.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            $userId = Auth::id();

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

            return Response::text("Status retrieved successfully. Total memories: {$memoryCount}, Pending jobs: {$pendingJobs}, Processing jobs: {$processingJobs}, Completed jobs: {$completedJobs}, Failed jobs: {$failedJobs}");
        } catch (Throwable $e) {
            return Response::text('Failed to retrieve status: ' . $e->getMessage());
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
