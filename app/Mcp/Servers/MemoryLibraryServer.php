<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class MemoryLibraryServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Memory Library Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = 'A personal memory management system that allows you to store, search, and retrieve memories with semantic search capabilities. Use this to remember important information, track project details, and build a searchable knowledge base.';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        \App\Mcp\Tools\AddToMemory::class,
        \App\Mcp\Tools\SearchMemory::class,
        \App\Mcp\Tools\MemoryStatus::class,
        \App\Mcp\Tools\ConfigureProvider::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
