<?php

use App\Http\Middleware\BlockBots;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'block.bots' => BlockBots::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $throwable, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($throwable instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed.',
                    'errors' => $throwable->errors(),
                ], 422);
            }

            if ($throwable instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $throwable->getMessage() ?: 'Unauthenticated.',
                ], 401);
            }

            if ($throwable instanceof AuthorizationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $throwable->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }

            if ($throwable instanceof ModelNotFoundException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found.',
                ], 404);
            }

            $status = $throwable instanceof HttpExceptionInterface
                ? $throwable->getStatusCode()
                : 500;

            $message = $status === 500
                ? 'An unexpected error occurred.'
                : $throwable->getMessage();

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], $status);
        });
    })->create();
