<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Memory\UpdateMemoryAction;
use App\Contracts\UpdateMemoryContract;
use Illuminate\Support\ServiceProvider;

class MemorableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UpdateMemoryContract::class,
            UpdateMemoryAction::class,
        );
    }
}
