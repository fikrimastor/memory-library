<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\EmbeddingDriverInterface;
use App\Services\EmbeddingManager;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class EmbeddingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/embedding.php', 'embedding'
        );

        $this->app->singleton(EmbeddingManager::class, fn ($app) => new EmbeddingManager($app));

        // Register the default driver
        $this->app->singleton(EmbeddingDriverInterface::class, fn ($app) => $app[EmbeddingManager::class]->driver());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/embedding.php' => config_path('embedding.php'),
        ], 'config');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            EmbeddingManager::class,
            EmbeddingDriverInterface::class,
        ];
    }
}
