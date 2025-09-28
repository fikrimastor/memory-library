<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class MemoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'thing_to_remember' => $this->thing_to_remember,
            'document_type' => $this->document_type,
            'project_name' => $this->project_name,
            'tags' => $this->tags ?? [],
            'visibility' => $this->visibility,
            'share_token' => $this->share_token,
            'share_url' => $this->share_url,
            'public_url' => $this->getPublicUrl(),
            'is_shared' => (bool) ($this->is_public ?? false),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
