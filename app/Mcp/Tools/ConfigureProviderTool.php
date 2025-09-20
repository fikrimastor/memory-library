<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\HealthCheckAction;
use App\Models\ProviderHealth;
use App\Services\EmbeddingManager;
use Throwable;

class ConfigureProviderTool
{
    /**
     * Configure or check the status of embedding providers.
     *
     * @param  array  $params  Tool parameters
     * @return array Response
     */
    public function handle(array $params): array
    {
        try {
            // If it's a configuration request
            if (isset($params['configure']) && $params['configure']) {
                // In a real implementation, this would update the configuration
                // For now, we'll just return the current configuration
                $config = config('embedding');

                return [
                    'success' => true,
                    'configuration' => $config,
                    'message' => 'Configuration retrieved successfully',
                ];
            }

            // If it's a health check request
            if (isset($params['health_check']) && $params['health_check']) {
                $action = app(HealthCheckAction::class);
                $results = $action->handle();

                return [
                    'success' => true,
                    'health_status' => $results,
                    'message' => 'Health check completed successfully',
                ];
            }
            
            // If it's a provider test request
            if (isset($params['test_provider']) && $params['test_provider']) {
                $providerName = $params['provider_name'] ?? config('embedding.default');
                $manager = app(EmbeddingManager::class);
                
                try {
                    $driver = $manager->driver($providerName);
                    $isHealthy = $driver->isHealthy();
                    $testEmbedding = null;
                    $testError = null;
                    
                    if ($isHealthy) {
                        // Try to generate a test embedding
                        $testEmbedding = $driver->embed('test');
                    }
                    
                    return [
                        'success' => true,
                        'provider_test' => [
                            'provider' => $providerName,
                            'healthy' => $isHealthy,
                            'test_embedding_generated' => $testEmbedding !== null,
                        ],
                        'message' => $isHealthy ? 'Provider test completed successfully' : 'Provider is not healthy',
                    ];
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'message' => 'Failed to test provider',
                    ];
                }
            }

            // Default action - return current configuration and health status
            $config = config('embedding');
            $providerHealth = ProviderHealth::all();

            return [
                'success' => true,
                'configuration' => $config,
                'provider_health' => $providerHealth,
                'message' => 'Configuration and health status retrieved successfully',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to configure provider',
            ];
        }
    }
}
