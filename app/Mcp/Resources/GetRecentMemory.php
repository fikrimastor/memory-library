<?php

namespace App\Mcp\Resources;

use App\Actions\GetRecentMemoryAction;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class GetRecentMemory extends Resource
{
    /**
     * The resource's description.
     */
    protected string $description = 'The user\'s most recently added memory. 
      Use this tool when:
      1. You need latest memory about the user\'s preferences or past interactions
      2. You need to verify if specific information about the user stored in memory';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request, GetRecentMemoryAction $action): Response
    {
        $user = $request->user();
        $recentMemory = $action->handle($user);

        if (!$recentMemory) {
            $text = "⚠️ **No Recent Memory Found.**\n\nYou haven't added any memories yet. Use the 'Add to Memory' tool to store important information.";

            return Response::text($text);
        }

        $output = "📖 **Your Recently Added Memory**\n\n";

        if (!empty($recentMemory['title'])) {
            $output .= "**{$recentMemory['title']}**\n";
        }

        // $output .= "URL: {$result['link']['url']}\n"; TODO: Add URL if applicable

        $output .= 'Tags: '.implode(', ', $recentMemory['tags'])."\n";
        $output .= 'Document Type: '.str($recentMemory['document_type'])->headline()->value()."\n";
        $output .= 'Project Name: '.$recentMemory['project_name']."\n";
        $output .= 'Created On: '.$recentMemory['created_at']."\n";
        $output .= "Memory Added:\n\n {$recentMemory['memory']}\n";

        return Response::text($output);
    }
}
