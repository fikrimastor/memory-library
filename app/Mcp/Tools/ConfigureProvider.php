<?php

namespace App\Mcp\Tools;

use App\Actions\HealthCheckAction;
use App\Models\ProviderHealth;
use App\Services\EmbeddingManager;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

class ConfigureProvider extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Configure or check the status of embedding providers.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            $params = $request->all();

            // If it's a configuration request
            if (isset($params['configure']) && $params['configure']) {
                // In a real implementation, this would update the configuration
                // For now, we'll just return the current configuration
                $config = config('embedding');

                return Response::json([
                    'success' => true,
                    'configuration' => $config,
                    'message' => 'Configuration retrieved successfully',
                ]);
            }

            // If it's a health check request
            if (isset($params['health_check']) && $params['health_check']) {
                $action = app(HealthCheckAction::class);
                $results = $action->handle();

                return Response::json([
                    'success' => true,
                    'health_status' => $results,
                    'message' => 'Health check completed successfully',
                ]);
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
                    
                    return Response::json([
                        'success' => true,
                        'provider_test' => [
                            'provider' => $providerName,
                            'healthy' => $isHealthy,
                            'test_embedding_generated' => $testEmbedding !== null,
                        ],
                        'message' => $isHealthy ? 'Provider test completed successfully' : 'Provider is not healthy',
                    ]);
                } catch (\Exception $e) {
                    return Response::json([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'message' => 'Failed to test provider',
                    ]);
                }
            }

            // Default action - return current configuration and health status
            $config = config('embedding');
            $providerHealth = ProviderHealth::all();

            return Response::json([
                'success' => true,
                'configuration' => $config,
                'provider_health' => $providerHealth,
                'message' => 'Configuration and health status retrieved successfully',
            ]);
        } catch (Throwable $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to configure provider',
            ]);
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
            'configure' => $schema->boolean()->description('Whether to configure the provider')->required(false),
            'health_check' => $schema->boolean()->description('Whether to perform a health check')->required(false),
            'test_provider' => $schema->boolean()->description('Whether to test a provider')->required(false),
            'provider_name' => $schema->string()->description('The name of the provider to test')->required(false),
        ];
    }
}
