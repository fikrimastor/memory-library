<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

// Register the memory library server as a local MCP server
Mcp::local('memory-library', \App\Mcp\Servers\MemoryLibraryServer::class);

// Also register it as a web server for HTTP access
Mcp::web('/mcp', \App\Mcp\Servers\MemoryLibraryServer::class)
    ->name('mcp.memory-library')
    ->middleware(['auth:api']);
