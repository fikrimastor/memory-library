<?php

use App\Models\User;

describe('API Token Management Browser Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('Page Navigation and Layout', function () {
        test('user can navigate to api tokens page', function () {
            $page = visit('/login')
                ->fill('email', $this->user->email)
                ->fill('password', 'password')
                ->click('Log in')
                ->assertSee('Dashboard');

            $page->visit('/settings/profile')
                ->assertSee('Profile Information')
                ->visit('/settings/api-tokens')
                ->assertSee('API Tokens');
        });

        test('api tokens page renders correctly', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->assertSee('API Tokens')
                ->assertSee('Create Token')
                ->assertSee('Create a new API token for accessing your account programmatically.')
                ->assertNoJavascriptErrors();
        });

        test('empty state is displayed when no tokens exist', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->assertSee('No API tokens found')
                ->assertSee('Create your first API token to get started')
                ->assertSee('API tokens allow third-party applications to access your account');
        });
    });

    describe('Token Creation Flow', function () {
        test('user can create a new api token through the ui', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->assertSee('Create New API Token')
                ->fill('[data-testid="token-name"]', 'My Test Token')
                ->click('Create Token')
                ->assertSee('API token created successfully')
                ->assertSee('My Test Token');
        });

        test('token creation shows generated token once', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->fill('[data-testid="token-name"]', 'Display Token')
                ->click('Create Token')
                ->assertSee('Token Created Successfully')
                ->assertSee('Please copy your new API token')
                ->assertSee('For security reasons, this token will only be shown once');

            // Close the dialog and verify token is not shown again
            $page->click('Close')
                ->assertDontSee('Please copy your new API token');
        });

        test('user can copy generated token to clipboard', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->fill('[data-testid="token-name"]', 'Copy Token')
                ->click('Create Token')
                ->click('Copy to Clipboard')
                ->assertSee('Token copied to clipboard!');
        });

        test('token creation form validation works', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->click('Create Token') // Submit without name
                ->assertSee('The token name is required.');

            // Test with too long name
            $page->fill('[data-testid="token-name"]', str_repeat('a', 256))
                ->click('Create Token')
                ->assertSee('The token name may not be greater than 255 characters.');
        });

        test('user can cancel token creation', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->assertSee('Create New API Token')
                ->click('Cancel')
                ->assertDontSee('Create New API Token');
        });
    });

    describe('Token Display and Management', function () {
        test('existing tokens are displayed correctly', function () {
            $this->actingAs($this->user);

            // Create some tokens first
            $this->user->createToken('First Token');
            $this->user->createToken('Second Token');

            $page = visit('/settings/api-tokens')
                ->assertSee('First Token')
                ->assertSee('Second Token')
                ->assertSee('Active') // Token status badge
                ->assertDontSee('No API tokens found');
        });

        test('token information is displayed', function () {
            $this->actingAs($this->user);

            $token = $this->user->createToken('Detailed Token');

            $page = visit('/settings/api-tokens')
                ->assertSee('Detailed Token')
                ->assertSee('Active')
                ->assertSee('Created')
                ->assertSee('Never used'); // Last used status
        });

        test('token creation date is formatted correctly', function () {
            $this->actingAs($this->user);

            $this->user->createToken('Date Token');

            $page = visit('/settings/api-tokens')
                ->assertSee('Date Token')
                ->assertSee(date('M j, Y')); // Current date format
        });
    });

    describe('Token Revocation Flow', function () {
        test('user can revoke a token', function () {
            $this->actingAs($this->user);

            $this->user->createToken('Revoke Token');

            $page = visit('/settings/api-tokens')
                ->assertSee('Revoke Token')
                ->click('[data-testid="revoke-button"]')
                ->assertSee('Revoke API Token')
                ->assertSee('Are you sure you want to revoke this token?')
                ->click('Revoke Token')
                ->assertSee('API token revoked successfully')
                ->assertDontSee('Revoke Token');
        });

        test('token revocation shows confirmation dialog', function () {
            $this->actingAs($this->user);

            $this->user->createToken('Confirm Revoke');

            $page = visit('/settings/api-tokens')
                ->click('[data-testid="revoke-button"]')
                ->assertSee('Revoke API Token')
                ->assertSee('This action cannot be undone')
                ->assertSee('Confirm Revoke'); // Token name in dialog
        });

        test('user can cancel token revocation', function () {
            $this->actingAs($this->user);

            $this->user->createToken('Cancel Revoke');

            $page = visit('/settings/api-tokens')
                ->click('[data-testid="revoke-button"]')
                ->assertSee('Revoke API Token')
                ->click('Cancel')
                ->assertDontSee('Revoke API Token')
                ->assertSee('Cancel Revoke'); // Token still exists
        });
    });

    describe('Error Handling', function () {
        test('network errors during token creation are handled gracefully', function () {
            $this->actingAs($this->user);

            // Mock a network failure scenario
            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->fill('[data-testid="token-name"]', 'Network Error Token');

            // Simulate network failure by intercepting the request
            $page->interceptRequests()
                ->failRequest('**/api-tokens', 500)
                ->click('Create Token')
                ->assertSee('Failed to create API token'); // Error message
        });

        test('handles server errors during token revocation', function () {
            $this->actingAs($this->user);

            $token = $this->user->createToken('Error Revoke');

            $page = visit('/settings/api-tokens')
                ->interceptRequests()
                ->failRequest('**/api-tokens/**', 500)
                ->click('[data-testid="revoke-button"]')
                ->click('Revoke Token')
                ->assertSee('Failed to revoke API token');
        });
    });

    describe('Accessibility and UX', function () {
        test('page is accessible with screen reader', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->assertAccessible()
                ->assertHasHeading('API Tokens')
                ->assertHasButton('Create Token');
        });

        test('form inputs have proper labels and aria attributes', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->assertElementExists('label[for="token-name"]')
                ->assertElementExists('input[aria-describedby]');
        });

        test('buttons have proper loading states', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->click('Create Token')
                ->fill('[data-testid="token-name"]', 'Loading Test')
                ->click('Create Token')
                ->assertSee('Creating...') // Loading state
                ->waitFor('[data-testid="success-message"]'); // Wait for completion
        });

        test('keyboard navigation works correctly', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->press('Tab') // Navigate to Create Token button
                ->press('Enter') // Open dialog
                ->assertSee('Create New API Token')
                ->press('Escape') // Close dialog
                ->assertDontSee('Create New API Token');
        });
    });

    describe('Dark Mode Support', function () {
        test('page renders correctly in dark mode', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->switchToDarkMode()
                ->assertNoJavascriptErrors()
                ->assertElementExists('[class*="dark"]'); // Verify dark mode classes
        });

        test('dialogs render correctly in dark mode', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->switchToDarkMode()
                ->click('Create Token')
                ->assertSee('Create New API Token')
                ->assertNoJavascriptErrors();
        });
    });

    describe('Responsive Design', function () {
        test('page is responsive on mobile devices', function () {
            $this->actingAs($this->user);

            $page = visit('/settings/api-tokens')
                ->resize(375, 667) // iPhone SE size
                ->assertNoJavascriptErrors()
                ->assertElementExists('button') // Create button still accessible
                ->click('Create Token')
                ->assertSee('Create New API Token');
        });

        test('token list adapts to smaller screens', function () {
            $this->actingAs($this->user);

            $this->user->createToken('Mobile Token');

            $page = visit('/settings/api-tokens')
                ->resize(375, 667)
                ->assertSee('Mobile Token')
                ->assertNoJavascriptErrors();
        });
    });

    describe('Multiple Tokens Management', function () {
        test('user can manage multiple tokens', function () {
            $this->actingAs($this->user);

            // Create multiple tokens
            $this->user->createToken('Token 1');
            $this->user->createToken('Token 2');
            $this->user->createToken('Token 3');

            $page = visit('/settings/api-tokens')
                ->assertSee('Token 1')
                ->assertSee('Token 2')
                ->assertSee('Token 3');

            // Revoke one token
            $page->within('[data-token="Token 2"]', function ($token) {
                $token->click('[data-testid="revoke-button"]');
            })
                ->click('Revoke Token')
                ->assertSee('API token revoked successfully')
                ->assertSee('Token 1')
                ->assertDontSee('Token 2')
                ->assertSee('Token 3');
        });

        test('tokens are ordered by creation date', function () {
            $this->actingAs($this->user);

            $firstToken = $this->user->createToken('First Token');
            $this->travel(1)->hour();
            $secondToken = $this->user->createToken('Second Token');

            $page = visit('/settings/api-tokens');

            // Check order - newest first
            $page->assertSeeInOrder(['Second Token', 'First Token']);
        });
    });
});
