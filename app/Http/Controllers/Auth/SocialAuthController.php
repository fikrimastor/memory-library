<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        try {
            return Socialite::driver('github')->redirect();
        } catch (Exception $e) {
            Log::error('GitHub OAuth redirect error: '.$e->getMessage());

            return redirect()->route('login')->withErrors([
                'social' => 'Unable to redirect to GitHub for authentication. Please try again.',
            ]);
        }
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (Exception $e) {
            Log::error('GitHub OAuth callback error: '.$e->getMessage());

            return redirect()->route('login')->withErrors([
                'social' => 'Unable to authenticate with GitHub. Please try again.',
            ]);
        }

        try {
            // Check if this GitHub account is already linked to a user
            $socialAccount = SocialAccount::where('provider', 'github')
                ->where('provider_id', $githubUser->getId())
                ->first();

            if ($socialAccount) {
                // Log in the existing user
                Auth::login($socialAccount->user);

                return redirect()->intended(route('dashboard', absolute: false));
            }

            // Check if the user is already logged in (linking accounts)
            if (Auth::check()) {
                $user = Auth::user();

                // Check if this GitHub account is already linked to another user
                if (SocialAccount::where('provider', 'github')
                    ->where('provider_id', $githubUser->getId())
                    ->exists()) {
                    return back()->withErrors([
                        'social' => 'This GitHub account is already linked to another user.',
                    ]);
                }

                // Link the GitHub account to the current user
                $user->socialAccounts()->create([
                    'provider' => 'github',
                    'provider_id' => $githubUser->getId(),
                    'provider_token' => $githubUser->token,
                    'provider_refresh_token' => $githubUser->refreshToken,
                    'provider_token_expires_at' => $githubUser->expiresIn ? now()->addSeconds($githubUser->expiresIn) : null,
                    'provider_data' => [
                        'nickname' => $githubUser->getNickname(),
                        'name' => $githubUser->getName(),
                        'email' => $githubUser->getEmail(),
                        'avatar' => $githubUser->getAvatar(),
                    ],
                ]);

                return back()->with('status', 'GitHub account linked successfully.');
            }

            // Check if a user with this email already exists
            $existingUser = User::where('email', $githubUser->getEmail())->first();

            if ($existingUser) {
                // Link the GitHub account to the existing user
                $existingUser->socialAccounts()->create([
                    'provider' => 'github',
                    'provider_id' => $githubUser->getId(),
                    'provider_token' => $githubUser->token,
                    'provider_refresh_token' => $githubUser->refreshToken,
                    'provider_token_expires_at' => $githubUser->expiresIn ? now()->addSeconds($githubUser->expiresIn) : null,
                    'provider_data' => [
                        'nickname' => $githubUser->getNickname(),
                        'name' => $githubUser->getName(),
                        'email' => $githubUser->getEmail(),
                        'avatar' => $githubUser->getAvatar(),
                    ],
                ]);

                Auth::login($existingUser);

                return redirect()->intended(route('dashboard', absolute: false));
            }

            // Create a new user
            $user = User::create([
                'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
                'password' => '', // No password needed for social login
            ]);

            // Create the social account link
            $user->socialAccounts()->create([
                'provider' => 'github',
                'provider_id' => $githubUser->getId(),
                'provider_token' => $githubUser->token,
                'provider_refresh_token' => $githubUser->refreshToken,
                'provider_token_expires_at' => $githubUser->expiresIn ? now()->addSeconds($githubUser->expiresIn) : null,
                'provider_data' => [
                    'nickname' => $githubUser->getNickname(),
                    'name' => $githubUser->getName(),
                    'email' => $githubUser->getEmail(),
                    'avatar' => $githubUser->getAvatar(),
                ],
            ]);

            Auth::login($user);

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (Exception $e) {
            Log::error('GitHub OAuth user creation/login error: '.$e->getMessage());

            return redirect()->route('login')->withErrors([
                'social' => 'An error occurred during authentication. Please try again.',
            ]);
        }
    }
}
