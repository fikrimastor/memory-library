<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::group(['middleware' => 'auth'], function () {
    // Dashboard route
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // Memory management routes
    Route::resource('memories', App\Http\Controllers\MemoryController::class);

    // Authenticated sharing routes
    Route::group([
        'prefix' => 'memories',
        'as' => 'memories.',
    ], function () {
        Route::post('/{memory}/share/public', [App\Http\Controllers\MemoryController::class, 'makePublic'])->name('share.public');
        Route::post('/{memory}/share/private', [App\Http\Controllers\MemoryController::class, 'makePrivate'])->name('share.private');
        Route::get('/{memory}/sharing-info', [App\Http\Controllers\MemoryController::class, 'sharingInfo'])->name('sharing.info');
    });
});

// Public sharing routes (no authentication required)
Route::group([
    'prefix' => 'share',
    'as' => 'memories.public.',
    'middleware' => ['block.bots', 'throttle:public-memory']
], function () {
    Route::get('/{memory:share_token}', [App\Http\Controllers\PublicMemoryController::class, 'show'])->name('show');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
