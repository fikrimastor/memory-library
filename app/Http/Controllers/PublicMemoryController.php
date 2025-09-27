<?php

namespace App\Http\Controllers;

use App\Models\UserMemory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicMemoryController extends Controller
{
    /**
     * Display a single shared memory.
     */
    public function show(UserMemory $memory): \Inertia\Response
    {
        // Verify the memory is shared (public or unlisted)
        abort_if(! $memory->is_public,404, 'Memory not found');

        $memory->load('user');

        // Sanitize content for public display
        $memory->sanitized_content = $memory->getSanitizedContent();

        return Inertia::render('Public/Memory/Show', [
            'memory' => $memory,
            'userName' => $memory->user->name,
        ]);
    }
}
