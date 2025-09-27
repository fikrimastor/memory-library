<?php

use App\Models\User;

// Test that routes exist
it('has GitHub OAuth routes', function () {
    // Test that the routes exist without actually calling them
    expect(route('github.login'))->toBeString();
    expect(route('github.callback'))->toBeString();
});

// Test the unlink functionality directly on the model
it('allows user to unlink GitHub account', function () {
    $user = User::factory()->create();

    // First link a GitHub account
    $socialAccount = $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_id' => '12345',
        'provider_token' => 'token123',
        'provider_data' => [
            'nickname' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'https://example.com/avatar.png',
        ],
    ]);

    expect($user->socialAccounts)->toHaveCount(1);

    // Now unlink it directly
    $user->socialAccounts()->where('provider', 'github')->delete();

    expect($user->fresh()->socialAccounts)->toHaveCount(0);
});
