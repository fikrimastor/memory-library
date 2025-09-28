<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class MemoryLibraryServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Memory Library';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.2.1';

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
        \App\Mcp\Tools\BasicSearchMemory::class,
        \App\Mcp\Tools\FetchMemory::class,
        // \App\Mcp\Tools\SearchMemory::class, // Having errors when connects to Qwen
        \App\Mcp\Tools\AddToMemory::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        \App\Mcp\Resources\GetRecentMemory::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        \App\Mcp\Prompts\SummarizeRecentActivity::class,
    ];
}
