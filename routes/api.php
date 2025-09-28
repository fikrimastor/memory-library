<?php

use App\Http\Controllers\Api\V1\MemoryController as ApiV1MemoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware(['auth:api'])->group(function () {
        Route::get('memories', [ApiV1MemoryController::class, 'index'])
            ->middleware('throttle:api.memories')
            ->name('memories.index');

        Route::post('memories', [ApiV1MemoryController::class, 'store'])
            ->middleware('throttle:api.memories.create')
            ->name('memories.store');

        Route::get('memories/{memory}', [ApiV1MemoryController::class, 'show'])
            ->middleware('throttle:api.memories')
            ->name('memories.show');

        Route::match(['put', 'patch'], 'memories/{memory}', [ApiV1MemoryController::class, 'update'])
            ->middleware('throttle:api.memories')
            ->name('memories.update');

        Route::delete('memories/{memory}', [ApiV1MemoryController::class, 'destroy'])
            ->middleware('throttle:api.memories')
            ->name('memories.destroy');
    });
});
