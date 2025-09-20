<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\HealthCheckAction;
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

            // Default action - return current configuration
            $config = config('embedding');

            return [
                'success' => true,
                'configuration' => $config,
                'message' => 'Configuration retrieved successfully',
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
