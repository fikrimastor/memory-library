<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\HealthCheckAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HealthCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Set the queue for this job
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(HealthCheckAction $action): void
    {
        try {
            $results = $action->handle();

            Log::info('Provider health check completed', $results);
        } catch (\Exception $e) {
            Log::error('Provider health check failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
