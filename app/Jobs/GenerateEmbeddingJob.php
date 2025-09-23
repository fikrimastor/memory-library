<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GenerateEmbeddingAction;
use App\Models\EmbeddingJob;
use App\Models\UserMemory;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  UserMemory  $memory  The memory to generate an embedding for
     * @param  string|null  $provider  The provider to use (null for default)
     * @return void
     */
    public function __construct(protected UserMemory $memory, protected ?string $provider = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GenerateEmbeddingAction $action): void
    {
        // Create or update the embedding job record
        $jobRecord = EmbeddingJob::firstOrNew([
            'memory_id' => $this->memory->id,
            'provider' => $this->provider ?? config('embedding.default'),
        ]);

        try {
            DB::transaction(function () use ($action, $jobRecord) {
                $jobRecord->status = 'processing';
                $jobRecord->attempts = $jobRecord->attempts + 1;
                $jobRecord->save();

                // Generate the embedding
                $action->handle($this->memory, $this->provider);

                // Mark as completed
                $jobRecord->status = 'completed';
                $jobRecord->save();

                Log::info('Embedding generated successfully', [
                    'memory_id' => $this->memory->id,
                    'provider' => $this->provider ?? config('embedding.default'),
                ]);
            });
        } catch (\Throwable $e) {
            $jobRecord->status = 'failed';
            $jobRecord->error_message = $e->getMessage();
            $jobRecord->save();

            Log::error('Failed to generate embedding', [
                'memory_id' => $this->memory->id,
                'provider' => $this->provider ?? config('embedding.default'),
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception so the job can be retried
            throw $e;
        }
    }

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [3, 60, 180, 1800];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addHours(5);
    }
}
