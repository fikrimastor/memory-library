<?php

use App\Mcp\Servers\MemoryLibraryServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

// Also register it as a web server for HTTP access
Mcp::web('/mcp', MemoryLibraryServer::class)
    ->name('mcp.memory-library')
    ->middleware('auth:api');
