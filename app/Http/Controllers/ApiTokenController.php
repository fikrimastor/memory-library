<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiTokenStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Passport\Token;

class ApiTokenController extends Controller
{
    /**
     * Display a listing of the user's API tokens.
     */
    public function index(Request $request): Response
    {
        $tokens = $request->user()
            ->tokens()
            ->where('revoked', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Token $token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'scopes' => $token->scopes,
                    'created_at' => $token->created_at,
                    'last_used_at' => $token->last_used_at,
                ];
            });

        return Inertia::render('settings/ApiTokens', [
            'tokens' => $tokens,
        ]);
    }

    /**
     * Store a newly created API token.
     */
    public function store(ApiTokenStoreRequest $request): RedirectResponse|JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $scopes = $validated['scopes'] ?? [];

            $token = $request->user()->createToken(
                name: $validated['name'],
                scopes: $scopes
            );

            DB::commit();

            // Return the token value only on creation (it won't be shown again)
            if ($request->expectsJson()) {
                return response()->json([
                    'token' => $token->accessToken,
                    'id' => $token->token->id,
                    'name' => $token->token->name,
                    'scopes' => $token->token->scopes,
                    'created_at' => $token->token->created_at,
                ], 201);
            }

            return to_route('api-tokens.index')->with([
                'success' => 'API token created successfully.',
                'token' => $token->accessToken,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to create API token.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return to_route('api-tokens.index')->withErrors([
                'error' => 'Failed to create API token. Please try again.',
            ]);
        }
    }

    /**
     * Revoke the specified API token.
     */
    public function destroy(Request $request, string $tokenId): RedirectResponse|JsonResponse
    {
        try {
            $user = $request->user();

            // Find the token and ensure it belongs to the authenticated user
            $token = $user->tokens()->where('id', $tokenId)->first();

            if (! $token) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Token not found or access denied.',
                    ], 404);
                }

                return to_route('api-tokens.index')->withErrors([
                    'error' => 'Token not found or access denied.',
                ]);
            }

            // Revoke the token
            $token->revoke();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'API token revoked successfully.',
                ], 200);
            }

            return to_route('api-tokens.index')->with([
                'success' => 'API token revoked successfully.',
            ]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to revoke API token.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return to_route('api-tokens.index')->withErrors([
                'error' => 'Failed to revoke API token. Please try again.',
            ]);
        }
    }
}
