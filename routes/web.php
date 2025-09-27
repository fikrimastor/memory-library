<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('memories', App\Http\Controllers\MemoryController::class)
    ->middleware(['auth', 'verified']);

// Public sharing routes (no authentication required)
Route::prefix('share')->name('memories.public.')->group(function () {
    Route::get('/{memory:share_token}', [App\Http\Controllers\PublicMemoryController::class, 'show'])
        ->middleware(['throttle:public-memory'])
        ->name('show');
});

// Authenticated sharing routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/memories/{memory}/share/public', [App\Http\Controllers\MemoryController::class, 'makePublic'])->name('memories.share.public');
    Route::post('/memories/{memory}/share/private', [App\Http\Controllers\MemoryController::class, 'makePrivate'])->name('memories.share.private');
    Route::get('/memories/{memory}/sharing-info', [App\Http\Controllers\MemoryController::class, 'sharingInfo'])->name('memories.sharing.info');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
