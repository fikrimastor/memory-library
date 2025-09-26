<?php

declare(strict_types=1);

namespace App\Actions\Embedding;

use App\Models\ProviderHealth;
use App\Services\EmbeddingManager;
use Illuminate\Support\Facades\DB;

final class HealthCheckAction
{
    public function __construct(
        protected EmbeddingManager $embeddingManager
    ) {}

    /**
     * Perform a health check on all configured embedding providers.
     */
    public function handle(): array
    {
        $results = [];
        $config = config('embedding.providers');

        foreach ($config as $name => $providerConfig) {
            $results[$name] = $this->checkProvider($name);
        }

        return $results;
    }

    /**
     * Check the health of a specific provider.
     */
    protected function checkProvider(string $providerName): array
    {
        try {
            $driver = $this->embeddingManager->driver($providerName);
            $isHealthy = $driver->isHealthy();
            $responseTime = 0; // In a real implementation, we would measure this

            // Update the health record
            $this->updateHealthRecord($providerName, $isHealthy, $responseTime);

            return [
                'provider' => $providerName,
                'healthy' => $isHealthy,
                'response_time_ms' => $responseTime,
                'error' => null,
            ];
        } catch (\Exception $e) {
            // Update the health record with error
            $this->updateHealthRecord($providerName, false, 0, $e->getMessage());

            return [
                'provider' => $providerName,
                'healthy' => false,
                'response_time_ms' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update the health record for a provider.
     */
    protected function updateHealthRecord(
        string $providerName,
        bool $isHealthy,
        int $responseTime,
        ?string $errorMessage = null
    ): void {
        DB::transaction(function () use ($providerName, $isHealthy, $responseTime, $errorMessage) {
            $health = ProviderHealth::firstOrNew(['provider' => $providerName]);

            $health->is_healthy = $isHealthy;
            $health->last_check = now();
            $health->response_time_ms = $responseTime;

            if ($isHealthy) {
                $health->success_count = $health->success_count + 1;
                $health->last_error = null;
            } else {
                $health->error_count = $health->error_count + 1;
                $health->last_error = $errorMessage;
            }

            $health->save();
        });
    }
}
