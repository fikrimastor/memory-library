<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Memory\AddToMemoryAction;
use App\Http\Requests\Api\V1\StoreMemoryRequest;
use App\Http\Requests\Api\V1\UpdateMemoryRequest;
use App\Http\Resources\Api\V1\MemoryResource;
use App\Models\UserMemory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemoryController extends BaseApiController
{
    /**
     * Display a paginated list of the authenticated user's memories.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserMemory::class);

        $perPage = (int) min($request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $project = $request->query('project');

        $memories = $request->user()
            ->memories()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('thing_to_remember', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%");
                });
            })
            ->when($project, fn ($query, $projectName) => $query->where('project_name', $projectName))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return $this->resourceCollection(
            MemoryResource::collection($memories),
            message: 'Memories retrieved successfully.'
        );
    }

    /**
     * Store a newly created memory.
     */
    public function store(StoreMemoryRequest $request, AddToMemoryAction $addToMemoryAction): JsonResponse
    {
        $this->authorize('create', UserMemory::class);

        $validated = $request->validated();

        $memory = $addToMemoryAction->handle(
            userId: $request->user()->id,
            content: $validated['thing_to_remember'],
            metadata: ['title' => $validated['title']],
            tags: $validated['tags'] ?? [],
            projectName: $validated['project_name'] ?? null,
            documentType: $validated['document_type'] ?? 'Memory'
        );

        return $this->success(
            data: MemoryResource::make($memory)->toArray($request),
            message: 'Memory created successfully.',
            status: 201
        );
    }

    /**
     * Display the specified memory.
     */
    public function show(Request $request, UserMemory $memory): JsonResponse
    {
        $this->authorize('view', $memory);

        return $this->success(
            data: MemoryResource::make($memory)->toArray($request),
            message: 'Memory retrieved successfully.'
        );
    }

    /**
     * Update the specified memory in storage.
     */
    public function update(UpdateMemoryRequest $request, UserMemory $memory): JsonResponse
    {
        $this->authorize('update', $memory);

        $memory->update($request->validated());

        $memory->refresh();

        return $this->success(
            data: MemoryResource::make($memory)->toArray($request),
            message: 'Memory updated successfully.'
        );
    }

    /**
     * Remove the specified memory from storage.
     */
    public function destroy(UserMemory $memory): JsonResponse
    {
        $this->authorize('delete', $memory);

        $memory->delete();

        return response()->json(null, 204);
    }
}
