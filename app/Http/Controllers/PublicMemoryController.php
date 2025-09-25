<?php

namespace App\Http\Controllers;

use App\Models\UserMemory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class PublicMemoryController extends Controller
{
    /**
     * Display a single shared memory.
     */
    public function show(UserMemory $memory): \Inertia\Response
    {
        // Verify the memory is shared (public or unlisted)
        if (! $memory->isShared()) {
            abort(404, 'Memory not found');
        }

        // Additional rate limiting for public endpoints
        $key = 'show_memory:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 100)) { // 100 requests per minute
            abort(429, 'Too many requests');
        }
        RateLimiter::hit($key, 60); // 60 seconds window

        // Sanitize content for public display
        $memory->sanitized_content = $memory->getSanitizedContent();

        return Inertia::render('Public/Memory/Show', [
            'memory' => $memory,
        ]);
    }

    /**
     * List all public memories.
     */
    public function index(Request $request): \Inertia\Response
    {
        // Additional rate limiting for public endpoints
        $key = 'index_memories:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 100)) { // 100 requests per minute
            abort(429, 'Too many requests');
        }
        RateLimiter::hit($key, 60); // 60 seconds window

        $query = $request->input('query');

        $memories = UserMemory::public()
            ->when($query, function ($q, $query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('thing_to_remember', 'like', "%{$query}%")
                    ->orWhere('project_name', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Public/Memory/Index', [
            'memories' => $memories,
            'query' => $query,
        ]);
    }

    /**
     * Search public memories.
     */
    public function search(Request $request): \Inertia\Response
    {
        // Additional rate limiting for public endpoints
        $key = 'search_memories:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 100)) { // 100 requests per minute
            abort(429, 'Too many requests');
        }
        RateLimiter::hit($key, 60); // 60 seconds window

        $query = $request->input('q');

        $memories = UserMemory::public()
            ->when($query, function ($q, $query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('thing_to_remember', 'like', "%{$query}%")
                    ->orWhere('project_name', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Public/Memory/Search', [
            'memories' => $memories,
            'query' => $query,
        ]);
    }
}
