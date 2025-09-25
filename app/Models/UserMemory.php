<?php

namespace App\Models;

use App\Jobs\GenerateEmbeddingJob;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

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
        'share_token',
        'visibility',
        'shared_at',
        'share_options',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
        'share_options' => 'array',
        'shared_at' => 'datetime',
    ];

    public static function booted()
    {
        static::created(function (UserMemory $memory) {
            // Log the creation with title and tags
            \Log::info("Memory created: Title - {$memory->title}, Tags - ".json_encode($memory->tags));

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
     * Get the embedding job for the memory.
     */
    public function embeddingJob(): HasOne
    {
        return $this->hasOne(EmbeddingJob::class, 'memory_id', 'id');
    }

    // Sharing functionality methods
    public function generateShareToken(): string
    {
        return Str::ulid();
    }

    public function makePublic(array $options = []): self
    {
        $this->update([
            'share_token' => $this->share_token ?? $this->generateShareToken(),
            'visibility' => 'public',
            'shared_at' => now(),
            'share_options' => $options,
        ]);

        return $this;
    }

    public function makeUnlisted(array $options = []): self
    {
        $this->update([
            'share_token' => $this->share_token ?? $this->generateShareToken(),
            'visibility' => 'unlisted',
            'shared_at' => now(),
            'share_options' => $options,
        ]);

        return $this;
    }

    public function makePrivate(): self
    {
        $this->update([
            'visibility' => 'private',
        ]);

        return $this;
    }

    public function getPublicUrl(): string
    {
        if (! $this->isShared()) {
            return '';
        }

        return url("/share/{$this->share_token}");
    }

    public function getSanitizedContent(): string
    {
        return strip_tags($this->thing_to_remember);
    }

    // Helper methods
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isUnlisted(): bool
    {
        return $this->visibility === 'unlisted';
    }

    public function isShared(): bool
    {
        return in_array($this->visibility, ['public', 'unlisted']);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeUnlisted($query)
    {
        return $query->where('visibility', 'unlisted');
    }

    public function scopeShared($query)
    {
        return $query->whereIn('visibility', ['public', 'unlisted']);
    }

    // Dynamic route key binding
    public function getRouteKeyName()
    {
        // Use share_token for public routes, id for authenticated routes
        if (request()->routeIs(['memories.public.*', 'share.*'])) {
            return 'share_token';
        }

        return 'id';
    }

    /**
     * Accessor for a success message after creating a memory.
     */
    protected function successMessageCreated(): Attribute
    {
        return Attribute::get(fn () => "Memory added successfully. Memory: {$this->title}. ".json_encode($this->tags));
    }

    /**
     * Mutator to ensure document_type is always stored as a slug.
     */
    protected function documentType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => Str::slug($value, '-'),
        );
    }

    /**
     * Mutator to ensure document_type is always stored as a slug.
     */
    protected function projectName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => Str::slug($value, '-'),
        );
    }
}
