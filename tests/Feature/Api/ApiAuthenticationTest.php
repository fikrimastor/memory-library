<?php

use App\Models\User;

describe('API Authentication with Tokens', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        // Create Passport personal access client for testing
        if (! \Laravel\Passport\Client::where('personal_access_client', true)->exists()) {
            \Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'Test Personal Access Client',
                '--no-interaction' => true,
            ]);
        }
    });

    describe('User Endpoint Authentication', function () {
        test('authenticated user can access user endpoint with valid token', function () {
            $token = $this->user->createToken('API Access Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();
            $response->assertJson([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]);
        });

        test('unauthenticated request returns 401', function () {
            $response = $this
                ->withHeaders(['Accept' => 'application/json'])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });

        test('invalid token returns 401', function () {
            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer invalid-token-here',
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });

        test('malformed authorization header returns 401', function () {
            $response = $this
                ->withHeaders([
                    'Authorization' => 'InvalidBearer token-here',
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });

        test('missing bearer prefix returns 401', function () {
            $token = $this->user->createToken('Test Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => $token->accessToken, // Missing 'Bearer '
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });

        test('revoked token cannot access api', function () {
            $token = $this->user->createToken('Revoked Token');
            $token->token->revoke();

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });

        test('expired token cannot access api', function () {
            $token = $this->user->createToken('Expired Token');

            // Manually expire the token by setting expires_at in the past
            $token->token->update(['expires_at' => now()->subDay()]);

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });
    });

    describe('Token Scopes and Permissions', function () {
        test('token with read scope can access user endpoint', function () {
            $token = $this->user->createToken('Read Token', ['read']);

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();
        });

        test('token without any scopes can access user endpoint', function () {
            $token = $this->user->createToken('No Scope Token', []);

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();
        });

        test('user data matches authenticated user', function () {
            $anotherUser = User::factory()->create();
            $token = $this->user->createToken('User Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();
            $response->assertJson([
                'id' => $this->user->id,
                'email' => $this->user->email,
            ]);

            // Ensure it doesn't return another user's data
            $response->assertJsonMissing([
                'id' => $anotherUser->id,
                'email' => $anotherUser->email,
            ]);
        });
    });

    describe('API Token Security', function () {
        test('different users tokens cannot access each others data', function () {
            $otherUser = User::factory()->create();
            $otherToken = $otherUser->createToken('Other User Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$otherToken->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();
            $response->assertJson([
                'id' => $otherUser->id,
                'email' => $otherUser->email,
            ]);

            // Should not return the first user's data
            $response->assertJsonMissing([
                'id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        });

        test('token usage updates last_used_at timestamp', function () {
            $token = $this->user->createToken('Usage Token');

            expect($token->token->last_used_at)->toBeNull();

            $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $token->token->refresh();
            expect($token->token->last_used_at)->not->toBeNull();
            expect($token->token->last_used_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        });

        test('multiple requests update last_used_at timestamp', function () {
            $token = $this->user->createToken('Multiple Usage Token');

            // First request
            $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $token->token->refresh();
            $firstUsage = $token->token->last_used_at;

            $this->travel(5)->minutes();

            // Second request
            $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $token->token->refresh();
            $secondUsage = $token->token->last_used_at;

            expect($secondUsage)->toBeGreaterThan($firstUsage);
        });
    });

    describe('API Response Format', function () {
        test('api returns json content type', function () {
            $token = $this->user->createToken('JSON Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertHeader('Content-Type', 'application/json');
        });

        test('api response contains expected user fields', function () {
            $token = $this->user->createToken('Fields Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertJsonStructure([
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ]);
        });

        test('api response does not contain sensitive fields', function () {
            $token = $this->user->createToken('Security Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertJsonMissing(['password']);
            $response->assertJsonMissing(['remember_token']);

            $responseData = $response->json();
            expect($responseData)->not->toHaveKey('password');
            expect($responseData)->not->toHaveKey('remember_token');
        });
    });

    describe('API Error Handling', function () {
        test('missing accept header still returns json for api routes', function () {
            $token = $this->user->createToken('No Accept Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                ])
                ->get('/api/user');

            $response->assertOk();
            $response->assertHeader('Content-Type', 'application/json');
        });

        test('unauthorized responses return proper json structure', function () {
            $response = $this
                ->withHeaders(['Accept' => 'application/json'])
                ->getJson('/api/user');

            $response->assertUnauthorized();
            $response->assertJsonStructure(['message']);
        });

        test('case sensitive bearer token handling', function () {
            $token = $this->user->createToken('Case Token');

            // Test lowercase 'bearer'
            $response = $this
                ->withHeaders([
                    'Authorization' => 'bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized(); // Should fail with lowercase

            // Test uppercase 'BEARER'
            $response = $this
                ->withHeaders([
                    'Authorization' => 'BEARER '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized(); // Should fail with uppercase

            // Test correct case 'Bearer'
            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk(); // Should work with correct case
        });
    });

    describe('Token Lifecycle Integration', function () {
        test('newly created token works immediately', function () {
            $token = $this->user->createToken('Immediate Token');

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();
        });

        test('token stops working after revocation', function () {
            $token = $this->user->createToken('Revocation Token');

            // First verify token works
            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();

            // Revoke the token
            $token->token->revoke();

            // Verify token no longer works
            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertUnauthorized();
        });

        test('token with custom scopes maintains access', function () {
            $customScopes = ['read-profile', 'write-data', 'admin'];
            $token = $this->user->createToken('Custom Scope Token', $customScopes);

            $response = $this
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token->accessToken,
                    'Accept' => 'application/json',
                ])
                ->getJson('/api/user');

            $response->assertOk();

            // Verify the token has the correct scopes
            expect($token->token->scopes)->toBe($customScopes);
        });
    });
});
