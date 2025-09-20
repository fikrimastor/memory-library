<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/memory', \App\Mcp\Servers\MemoryLibraryServer::class)->middleware(['auth', 'verified']);
