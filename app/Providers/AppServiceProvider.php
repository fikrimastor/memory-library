<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::authorizationView(function ($parameters) {
            return view('mcp.authorize', $parameters);
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('public-memory', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        RateLimiter::for('api.memories', function (Request $request) {
            $identifier = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(60)->by($identifier);
        });

        RateLimiter::for('api.memories.create', function (Request $request) {
            $identifier = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(20)->by($identifier);
        });

        RateLimiter::for('api.memories.bulk', function (Request $request) {
            $identifier = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(10)->by($identifier);
        });

        RateLimiter::for('api.memories.search', function (Request $request) {
            $identifier = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)->by($identifier);
        });
    }
}
