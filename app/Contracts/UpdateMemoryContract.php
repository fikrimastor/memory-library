<?php

namespace App\Contracts;

use App\Models\UserMemory;

interface UpdateMemoryContract
{
    public function handle(UserMemory $memory, array $validated): UserMemory;
}
