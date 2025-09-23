<?php

namespace App\Models;

use App\Jobs\GenerateEmbeddingJob;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserMemory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'thing_to_remember',
        'title',
        'document_type',
        'project_name',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
    ];

    public static function booted()
    {
        static::created(function (UserMemory $memory) {
            // Log the creation with title and tags
            \Log::info("Memory created: Title - {$memory->title}, Tags - " . json_encode($memory->tags));

            // Dispatch a job to generate the embedding asynchronously
            GenerateEmbeddingJob::dispatch($memory);
        });
    }

    /**
     * Get the user that owns the memory.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that owns the memory.
     */
    public function embeddingJob(): HasOne
    {
        return $this->hasOne(EmbeddingJob::class, 'memory_id', 'id');
    }

    protected function successMessageCreated(): Attribute
    {
        return Attribute::get(fn () => "Memory added successfully. Memory: {$this->title}. " . json_encode($this->tags));
    }
}
