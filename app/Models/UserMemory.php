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

    protected $appends = [
        'share_url',
    ];

    public static function booted()
    {
        static::creating(function (UserMemory $memory) {
            $memory->share_token = Str::ulid();
        });

        static::created(function (UserMemory $memory) {
            // Log the creation with title and tags
            \Log::info("Memory created: Title - {$memory->title}, Tags - ".json_encode($memory->tags));

            // Dispatch a job to generate the embedding asynchronously
            GenerateEmbeddingJob::dispatch($memory);
        });

        static::updated(function (UserMemory $memory) {
            // Log the creation with title and tags
            \Log::info("Memory updated: Title - {$memory->title}, Tags - ".json_encode($memory->tags));

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

    public function getPublicUrl(): string
    {
        if (! $this->is_public) {
            return '';
        }

        return $this->share_url;
    }

    public function getSanitizedContent(): string
    {
        $content = $this->thing_to_remember ?? '';

        if (blank($content)) {
            return '';
        }

        return Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => '<br />',
            ],
            'unordered_list_markers' => ['•', '▪', '▫'],
        ]);
    }

    // Helper methods
    public function isPublic(): Attribute
    {
        return Attribute::get(fn () => $this->visibility === 'public');
    }

    public function isPrivate(): Attribute
    {
        return Attribute::get(fn () => $this->visibility === 'private');
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

    public function scopeShared($query)
    {
        return $query->where('visibility', 'public');
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

    /**
     * Accessor for a success message after creating a memory.
     */
    protected function shareUrl(): Attribute
    {
        return Attribute::get(fn () => url("/share/{$this->share_token}"));
    }
}
