<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group([
    'middleware' => 'auth',
    'prefix' => 'settings',
], function () {
    Route::redirect('/', '/settings/profile');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    Route::get('/api-tokens', [ApiTokenController::class, 'index'])
        ->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])
        ->name('api-tokens.store');
    Route::delete('/api-tokens/{token}', [ApiTokenController::class, 'destroy'])
        ->name('api-tokens.destroy');
});
