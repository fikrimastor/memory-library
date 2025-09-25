<?php

namespace App\Http\Controllers;

use App\Actions\AddToMemoryAction;
use App\Http\Requests\UpdateMemoryRequest;
use App\Models\UserMemory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class MemoryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = $request->input('search');
        $project = $request->input('project');

        $memories = UserMemory::query()
            ->select(['id', 'title', 'thing_to_remember', 'document_type', 'project_name', 'tags', 'created_at', 'updated_at', 'visibility', 'share_token', 'shared_at'])
            ->where('user_id', $user->id)
            ->when($query, function ($q, $query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('thing_to_remember', 'like', "%{$query}%")
                    ->orWhere('project_name', 'like', "%{$query}%");
            })
            ->when($project, function ($q, $project) {
                $q->where('project_name', $project);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $projects = UserMemory::where('user_id', $user->id)
            ->select('project_name')
            ->distinct()
            ->pluck('project_name');

        return Inertia::render('memories/Index', [
            'memories' => $memories,
            'projects' => $projects,
            'search' => $query,
            'project' => $project,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('memories/Create');
    }

    public function store(Request $request, AddToMemoryAction $addToMemoryAction): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'thing_to_remember' => 'required|string',
            'document_type' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
        ]);

        $memory = $addToMemoryAction->handle(
            userId: Auth::id(),
            content: $validated['thing_to_remember'],
            metadata: ['title' => $validated['title']],
            tags: $validated['tags'] ?? [],
            projectName: $validated['project_name'] ?? null,
            documentType: $validated['document_type'] ?? 'Memory',
        );

        return Redirect::route('memories.index')
            ->with('success', $memory->successMessageCreated);
    }

    public function show(UserMemory $memory): Response
    {
        $this->authorize('view', $memory);

        return Inertia::render('memories/Show', [
            'memory' => $memory,
        ]);
    }

    public function edit(UserMemory $memory): Response
    {
        $this->authorize('update', $memory);

        return Inertia::render('memories/Edit', [
            'memory' => $memory,
        ]);
    }

    public function update(UpdateMemoryRequest $request, UserMemory $memory): RedirectResponse
    {
        $this->authorize('update', $memory);

        $memory->update($request->validated());

        return Redirect::route('memories.show', $memory)
            ->with('success', 'Memory updated successfully.');
    }

    public function destroy(UserMemory $memory): RedirectResponse
    {
        $this->authorize('delete', $memory);

        $memory->delete();

        return Redirect::route('memories.index')
            ->with('success', 'Memory deleted successfully.');
    }

    /**
     * Make a memory public.
     */
    public function makePublic(Request $request, UserMemory $memory)
    {
        $this->authorize('update', $memory);

        $memory->makePublic();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Memory is now public.',
                'visibility' => $memory->visibility,
                'share_token' => $memory->share_token,
                'public_url' => $memory->getPublicUrl(),
                'is_shared' => $memory->isShared(),
            ]);
        }

        return Redirect::back()->with('success', 'Memory is now public.');
    }

    /**
     * Make a memory unlisted.
     */
    public function makeUnlisted(Request $request, UserMemory $memory)
    {
        $this->authorize('update', $memory);

        $memory->makeUnlisted();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Memory is now unlisted.',
                'visibility' => $memory->visibility,
                'share_token' => $memory->share_token,
                'public_url' => $memory->getPublicUrl(),
                'is_shared' => $memory->isShared(),
            ]);
        }

        return Redirect::back()->with('success', 'Memory is now unlisted.');
    }

    /**
     * Make a memory private.
     */
    public function makePrivate(Request $request, UserMemory $memory)
    {
        $this->authorize('update', $memory);

        $memory->makePrivate();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Memory is now private.',
                'visibility' => $memory->visibility,
                'share_token' => null,
                'public_url' => '',
                'is_shared' => $memory->isShared(),
            ]);
        }

        return Redirect::back()->with('success', 'Memory is now private.');
    }

    /**
     * Get sharing information for a memory.
     */
    public function sharingInfo(UserMemory $memory): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $memory);

        return response()->json([
            'visibility' => $memory->visibility,
            'share_token' => $memory->share_token,
            'public_url' => $memory->getPublicUrl(),
            'is_shared' => $memory->isShared(),
        ]);
    }
}
