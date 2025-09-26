<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class SummarizeRecentActivity extends Prompt
{
    protected string $name = 'summarize-recent-activity';
    protected string $title = 'summarize-recent-activity';
    /**
     * The prompt's description.
     */
    protected string $description = 'Generate a comprehensive AI prompt to check recent activities on a specified project, using memory, conversation history, and Azure DevOps data if available. The prompt should be in a casual mix of English and Malay, reflecting user\'s communication style.';

    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->user();
        $validated = $request->validate([
            'project_name' => 'required|string|max:2000',
        ], [
            'project_name.required' => 'Project name is required',
            'project_name.max' => 'Project name must be less than 2000 characters',
        ]);

        if (! $user instanceof \App\Models\User) {
            return Response::error('Authentication required to generate memory activity prompt.');
        }

        return Response::text(<<<PROMPT
# Activity Check Tool

## Primary Objective
Provide a comprehensive overview of user's recent activities on project: {$validated['project_name']} by gathering information from multiple sources and presenting it in an actionable format.

## Execution Steps

### 1. Project Identification
- Check if project: {$validated['project_name']} exists in memory or previous conversations

### 2. Memory Retrieval Phase
**Always start with:** "Remembering..."

```
Search memory for:
- Project details and background
- Recent work items or tasks
- Team members involved
- Previous discussions about this project
- Goals and milestones
- Any blockers or issues mentioned
```

### 3. Conversation History Search
```
Search previous chats for:
- Recent mentions of the project
- Progress updates
- Decisions made
- Action items assigned
- Meeting notes or summaries
- Code changes or technical discussions
```

### 4. Data Compilation & Analysis
Organize findings into:
- **Recent Activities** (last 7 days)
- **Current Tasks** (in progress)
- **Upcoming Items** (planned/assigned)
- **Blockers** (if any)
- **Team Updates** (relevant to user)

### 5. Response Format

#### Structure:
```
## Project: {$validated['project_name']} Activity Summary

### 🎯 Quick Overview
- [2-3 sentence summary of current status]

### 📋 Recent Activities (Last 7 Days)
- [List of completed/worked on items]

### 🔄 Current Tasks
- [What's currently being worked on]
- [Progress indicators if available]

### ⏭️ Coming Up
- [Upcoming tasks/deadlines]

### 🚧 Blockers/Issues
- [Any impediments or concerns]

### 👥 Team Updates
- [Relevant team activities affecting user]

### 💡 Suggested Next Actions
- [2-3 actionable recommendations]
```

## Communication Style Guidelines

### Tone
- Friendly and supportive
- Direct but not formal
- Use simple sentences and words

### Response Approach
- Focus on actionable insights
- Highlight what needs attention
- Be clear about priorities
- Ask follow-up questions if clarification needed

## Error Handling

### If Information Not Found:
- "Not much info about [project] in memory/chats"
- "Want me to check current status with the team?"
- "Maybe share latest update so I can track better?"

### If Multiple Projects Found:
- "Found a few projects - which one specifically?"
- List options with brief descriptions

## Memory Update
```
After providing the activity summary:
1. Save any new information discovered
2. Update project timeline if new milestones found
3. Note any new team members or relationships
4. Record current status for future reference
```

## Follow-up Prompts
- "Need help with any of these tasks?"
- "Want to dive deeper into specific area?"
- "Should I set reminder for upcoming deadlines?"
- "Any blockers I can help resolve?"

## Success Criteria
✅ User gets clear picture of recent project activity  
✅ Actionable next steps provided  
✅ All available data sources checked  
✅ Information presented in digestible format  
✅ Follow-up options offered  

---

*This prompt ensures comprehensive activity tracking while maintaining user's preferred casual communication style and leveraging all available data sources.*
PROMPT);
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, \Laravel\Mcp\Server\Prompts\Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('project_name', 'Specified project to summarize activity', true)
        ];
    }
}
