<?php

namespace App\Actions\Memory;

use App\Models\UserMemory;
use Illuminate\Support\Facades\DB;

class UpdateShareableMemoryStatus
{
    private UserMemory $userMemory;

    /**
     * Update the shareable status of a memory.
     *
     * @param  UserMemory  $memory
     * @param  string  $status  The new status ('public', 'unlisted', 'private')
     * @param  array  $options  Additional options for sharing
     * @return bool  True if the status was updated, false otherwise
     * @throws \Throwable
     */
    public function handle(UserMemory $memory, string $status, array $options = []): bool
    {
        $this->userMemory = $memory;

        return DB::transaction(function () use ($memory, $status, $options) {
            return match ($status) {
                'public' => $this->makePublic($options),
                'unlisted' => $this->makeUnlisted($options),
                'private' => $this->makePrivate(),
                default => throw new \InvalidArgumentException("Invalid status: $status"),
            } !== null;
        });
    }

    private function makePublic(array $options = []): self
    {
        $this->userMemory->update([
            'share_token' => $this->share_token ?? $this->userMemory->generateShareToken(),
            'visibility' => 'public',
            'shared_at' => now(),
            'share_options' => $options,
        ]);

        return $this;
    }

    private function makeUnlisted(array $options = []): self
    {
        $this->userMemory->update([
            'share_token' => $this->share_token ?? $this->userMemory->generateShareToken(),
            'visibility' => 'unlisted',
            'shared_at' => now(),
            'share_options' => $options,
        ]);

        return $this;
    }

    private function makePrivate(): self
    {
        $this->userMemory->update([
            'visibility' => 'private',
        ]);

        return $this;
    }
}