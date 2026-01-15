<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Memory\UpdateMemoryAction;
use App\Contracts\EmbeddingDriverInterface;
use App\Contracts\UpdateMemoryContract;
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

        // Additional Contract
        $this->app->singleton(UpdateMemoryContract::class, UpdateMemoryAction::class);
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
