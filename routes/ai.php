<?php

use App\Mcp\Servers\MemoryLibraryServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP (Model Context Protocol) Routes
|--------------------------------------------------------------------------
|
| These routes handle MCP server registration and OAuth authentication
| for third-party connectors like Claude Desktop, Claude Web, etc.
|
| The OAuth routes provide the authentication flow endpoints.
| The web endpoint allows server discovery and tool execution.
|
| Authentication is handled at the tool level, allowing:
| 1. Unauthenticated discovery of server capabilities
| 2. OAuth flow for obtaining access tokens
| 3. Authenticated tool execution with proper user context
|
*/

// Register OAuth routes for third-party connector authentication
Mcp::oauthRoutes();

// Register MCP web server endpoint
// Note: Authentication is handled at the tool level, not at the route level.
// This allows third-party connectors to:
// 1. Discover the MCP server capabilities without authentication
// 2. Initiate OAuth flow to obtain access tokens
// 3. Execute tools with proper authentication via Bearer tokens
Mcp::web('/mcp', MemoryLibraryServer::class)
    ->name('mcp.memory-library');
