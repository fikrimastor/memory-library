<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | These settings configure the MCP (Model Context Protocol) server for
    | third-party connectors like Claude Desktop, Claude Web, etc.
    |
    */

    'server' => [
        'name' => env('MCP_SERVER_NAME', 'Memory Library'),
        'version' => env('MCP_SERVER_VERSION', '1.2.1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Configure OAuth settings for MCP authentication. Third-party connectors
    | use OAuth to obtain access tokens for authenticated requests.
    |
    */

    'oauth' => [
        // Token expiration in minutes (default: 60 minutes = 1 hour)
        'token_expiration' => env('MCP_TOKEN_EXPIRATION', 60),

        // Refresh token expiration in days (default: 30 days)
        'refresh_token_expiration' => env('MCP_REFRESH_TOKEN_EXPIRATION', 30),

        // Scopes available for MCP clients
        'scopes' => [
            'memory:read' => 'Read access to memories',
            'memory:write' => 'Write access to memories',
            'memory:search' => 'Search memories',
        ],

        // Default scopes granted to MCP clients
        'default_scopes' => ['memory:read', 'memory:write', 'memory:search'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for MCP endpoints to prevent abuse.
    |
    */

    'rate_limiting' => [
        'enabled' => env('MCP_RATE_LIMITING_ENABLED', true),
        'max_requests' => env('MCP_RATE_LIMIT_MAX', 60),
        'decay_minutes' => env('MCP_RATE_LIMIT_DECAY', 1),
    ],

];
