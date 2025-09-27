<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\EmbeddingJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupFailedEmbeddingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find failed embedding jobs that have exceeded max attempts
            $failedJobs = EmbeddingJob::where('status', 'failed')
                ->where('attempts', '>=', 3)
                ->get();

            $count = $failedJobs->count();

            if ($count > 0) {
                foreach ($failedJobs as $job) {
                    Log::info('Cleaning up failed embedding job', [
                        'job_id' => $job->id,
                        'memory_id' => $job->memory_id,
                        'provider' => $job->provider,
                        'attempts' => $job->attempts,
                    ]);

                    // Here you might want to notify the user or take other actions
                    // For now, we'll just delete the job record
                    $job->delete();
                }

                Log::info("Cleaned up {$count} failed embedding jobs");
            }
        } catch (\Exception $e) {
            Log::error('Failed to cleanup embedding jobs', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
