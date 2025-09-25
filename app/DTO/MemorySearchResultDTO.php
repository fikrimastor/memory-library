<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\UserMemory;
use Carbon\Carbon;

final readonly class MemorySearchResultDTO
{
    public function __construct(
        public int $id,
        public ?string $title,
        public string $thing_to_remember,
        public array $tags,
        public ?string $document_type,
        public ?string $project_name,
        public Carbon $created_at,
        public ?float $similarity = null,
        public ?float $hybrid_score = null,
        public ?float $vector_score = null,
        public ?float $text_score = null,
    ) {}

    public static function fromUserMemory(
        UserMemory $memory,
        ?float $similarity = null,
        ?float $hybrid_score = null,
        ?float $vector_score = null,
        ?float $text_score = null
    ): self {
        return new self(
            id: $memory->id,
            title: $memory->title,
            thing_to_remember: $memory->thing_to_remember,
            tags: $memory->tags ?? [],
            document_type: $memory->document_type,
            project_name: $memory->project_name,
            created_at: $memory->created_at,
            similarity: $similarity,
            hybrid_score: $hybrid_score,
            vector_score: $vector_score,
            text_score: $text_score,
        );
    }

    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'title' => $this->title ?? '',
            'thing_to_remember' => $this->thing_to_remember,
            'tags' => $this->tags,
            'document_type' => $this->document_type ?? '',
            'project_name' => $this->project_name ?? '',
            'created_at' => $this->created_at->toISOString(),
        ];

        // Add scores only if they exist
        if ($this->similarity !== null) {
            $result['similarity'] = $this->roundScore($this->similarity);
        }
        if ($this->hybrid_score !== null) {
            $result['hybrid_score'] = $this->roundScore($this->hybrid_score);
        }
        if ($this->vector_score !== null) {
            $result['vector_score'] = $this->roundScore($this->vector_score);
        }
        if ($this->text_score !== null) {
            $result['text_score'] = $this->roundScore($this->text_score);
        }

        return $result;
    }

    /**
     * Custom robust rounding function that avoids static analyzer issues
     * Uses mathematical operations instead of PHP's round() function
     *
     * @param float $value The value to round
     * @param int $precision Number of decimal places (default: 3)
     * @return float Rounded value
     */
    private function roundScore(float $value, int $precision = 3): float
    {
        $multiplier = pow(10, $precision);
        return floor($value * $multiplier + 0.5) / $multiplier;
    }
}
