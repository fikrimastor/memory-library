<?php

namespace App\Actions\Memory;

use App\Contracts\UpdateMemoryContract;
use App\Models\UserMemory;

class UpdateMemoryAction implements UpdateMemoryContract
{
    public function handle(UserMemory $memory, array $validated): UserMemory
    {
        $memory->update($validated);

        return $memory->fresh();
    }
}
