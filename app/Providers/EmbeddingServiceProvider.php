<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\EmbeddingDriverInterface;
use App\Services\EmbeddingManager;
use Illuminate\Support\ServiceProvider;

class EmbeddingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/embedding.php', 'embedding'
        );

        $this->app->singleton(EmbeddingManager::class, function ($app) {
            return new EmbeddingManager($app);
        });

        // Register the default driver
        $this->app->singleton(EmbeddingDriverInterface::class, function ($app) {
            return $app[EmbeddingManager::class]->driver();
        });
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
}