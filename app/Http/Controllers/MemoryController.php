<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemoryRequest;
use App\Models\UserMemory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $memories = UserMemory::query()
            ->where('user_id', auth()->id())
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('thing_to_remember', 'like', "%{$search}%")
                        ->orWhere('document_type', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('memories/Index', [
            'memories' => $memories,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('memories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpdateMemoryRequest $request): RedirectResponse
    {
        UserMemory::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('memories.index')
            ->with('success', 'Memory created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UserMemory $memory): Response
    {
        $this->authorize('view', $memory);

        return Inertia::render('memories/Show', [
            'memory' => $memory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserMemory $memory): Response
    {
        $this->authorize('update', $memory);

        return Inertia::render('memories/Edit', [
            'memory' => $memory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemoryRequest $request, UserMemory $memory): RedirectResponse
    {
        $this->authorize('update', $memory);

        $memory->update($request->validated());

        return redirect()->route('memories.index')
            ->with('success', 'Memory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserMemory $memory): RedirectResponse
    {
        $this->authorize('delete', $memory);

        $memory->delete();

        return redirect()->route('memories.index')
            ->with('success', 'Memory deleted successfully.');
    }
}
