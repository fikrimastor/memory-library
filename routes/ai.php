<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', \App\Mcp\Servers\MemoryLibraryServer::class)->name('mcp.memory-library')->middleware(['auth', 'verified']);
