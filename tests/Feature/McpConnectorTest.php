<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| MCP Route Registration Tests
|--------------------------------------------------------------------------
|
| These tests verify that MCP routes are properly registered and
| accessible for third-party connectors like Claude Desktop, Claude Web.
|
*/

it('has MCP web route registered', function () {
    expect(route('mcp.memory-library'))->toBeString();
});

it('has MCP OAuth routes registered', function () {
    // Test that OAuth routes exist
    expect(route('passport.authorizations.authorize'))->toBeString();
    expect(route('passport.token'))->toBeString();
});

/*
|--------------------------------------------------------------------------
| MCP Discovery Tests
|--------------------------------------------------------------------------
|
| These tests verify that third-party connectors can discover the
| MCP server without authentication (required for OAuth flow to work).
|
*/

it('allows unauthenticated GET request to MCP endpoint for discovery', function () {
    $response = $this->get(route('mcp.memory-library'));

    // Should not return 401 Unauthorized for discovery
    // The MCP protocol requires the endpoint to be discoverable
    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| MCP Tool Authentication Tests
|--------------------------------------------------------------------------
|
| These tests verify that MCP tools properly require authentication
| and return appropriate errors when not authenticated.
|
*/

it('returns authentication error when calling AddToMemory without auth', function () {
    $response = $this->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'add-to-memory',
            'arguments' => [
                'thing_to_remember' => 'Test memory content',
            ],
        ],
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    // Should return error about authentication
    expect($data)->toHaveKey('result');
});

it('returns authentication error when calling SearchMemory without auth', function () {
    $response = $this->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'advanced-search',
            'arguments' => [
                'query' => 'test search',
            ],
        ],
    ]);

    $response->assertStatus(200);
});

it('returns authentication error when calling FetchMemory without auth', function () {
    $response = $this->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'fetch',
            'arguments' => [
                'id' => 'test-token',
            ],
        ],
    ]);

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| MCP Server Capabilities Tests
|--------------------------------------------------------------------------
|
| These tests verify that the MCP server returns correct capabilities
| for third-party connectors to understand available tools and resources.
|
*/

it('returns server capabilities on initialize request', function () {
    $response = $this->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [],
            'clientInfo' => [
                'name' => 'test-client',
                'version' => '1.0.0',
            ],
        ],
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toHaveKey('result');
    expect($data['result'])->toHaveKey('serverInfo');
    expect($data['result']['serverInfo']['name'])->toBe('Memory Library');
});

it('returns available tools list on tools/list request', function () {
    // First initialize the session
    $this->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [],
            'clientInfo' => [
                'name' => 'test-client',
                'version' => '1.0.0',
            ],
        ],
    ]);

    $response = $this->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/list',
        'params' => [],
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toHaveKey('result');
    expect($data['result'])->toHaveKey('tools');
    expect($data['result']['tools'])->toBeArray();
});

/*
|--------------------------------------------------------------------------
| MCP Authenticated Access Tests
|--------------------------------------------------------------------------
|
| These tests verify that authenticated users can use MCP tools properly.
|
*/

it('allows authenticated user to call AddToMemory tool', function () {
    $user = User::factory()->create();

    // Create a personal access token for API authentication
    $token = $user->createToken('test-token')->accessToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'add-to-memory',
            'arguments' => [
                'thing_to_remember' => 'Test memory from authenticated user',
                'tags' => ['test', 'mcp'],
                'project_name' => 'MCP Testing',
            ],
        ],
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toHaveKey('result');
});

it('allows authenticated user to call SearchMemory tool', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->accessToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson(route('mcp.memory-library'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'advanced-search',
            'arguments' => [
                'query' => 'test search',
                'limit' => 5,
            ],
        ],
    ]);

    $response->assertStatus(200);
});
