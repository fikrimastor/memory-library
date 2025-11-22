<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class McpFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            // Tools
            [
                'type' => 'tool',
                'name' => 'add_to_memory',
                'title' => 'Add to Memory',
                'description' => 'This tool stores important user information in a persistent memory layer.',
                'class_name' => \App\Mcp\Tools\AddToMemory::class,
                'is_system' => true,
                'is_active_by_default' => true,
            ],
            [
                'type' => 'tool',
                'name' => 'fetch_memory',
                'title' => 'Fetch Memory',
                'description' => 'Fetch a specific memory by its ID.',
                'class_name' => \App\Mcp\Tools\FetchMemory::class,
                'is_system' => true,
                'is_active_by_default' => true,
            ],
            [
                'type' => 'tool',
                'name' => 'search_memory',
                'title' => 'Search Memory (Advanced)',
                'description' => 'Search the user\'s persistent memory layer using semantic matching.',
                'class_name' => \App\Mcp\Tools\SearchMemory::class,
                'is_system' => true,
                'is_active_by_default' => true,
            ],
            [
                'type' => 'tool',
                'name' => 'basic_search_memory',
                'title' => 'Search Memory (Basic)',
                'description' => 'Basic text search through memories.',
                'class_name' => \App\Mcp\Tools\BasicSearchMemory::class,
                'is_system' => true,
                'is_active_by_default' => false, // Commented out in MemoryLibraryServer
            ],

            // Resources
            [
                'type' => 'resource',
                'name' => 'recent_memory',
                'title' => 'Recent Memory',
                'description' => 'The user\'s most recently added memory.',
                'class_name' => \App\Mcp\Resources\GetRecentMemory::class,
                'is_system' => true,
                'is_active_by_default' => true,
            ],

            // Prompts
            [
                'type' => 'prompt',
                'name' => 'summarize_recent_activity',
                'title' => 'Summarize Recent Activity',
                'description' => 'Generate a comprehensive AI prompt to check recent activities on a specified project.',
                'class_name' => \App\Mcp\Prompts\SummarizeRecentActivity::class,
                'is_system' => true,
                'is_active_by_default' => true,
            ],
        ];

        foreach ($features as $feature) {
            \App\Models\McpFeature::updateOrCreate(
                [
                    'type' => $feature['type'],
                    'name' => $feature['name'],
                ],
                $feature
            );
        }

        $this->command->info('MCP features seeded successfully!');
    }
}
