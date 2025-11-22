<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McpFeature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'title',
        'description',
        'class_name',
        'handler_code',
        'schema_definition',
        'arguments_definition',
        'is_system',
        'is_active_by_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema_definition' => 'array',
            'arguments_definition' => 'array',
            'is_system' => 'boolean',
            'is_active_by_default' => 'boolean',
        ];
    }

    /**
     * Get the user settings for this feature.
     */
    public function userSettings(): HasMany
    {
        return $this->hasMany(UserMcpFeatureSetting::class);
    }

    /**
     * Get the users who have this feature active.
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_mcp_feature_settings')
            ->wherePivot('is_active', true);
    }

    /**
     * Scope to filter tools.
     */
    public function scopeTools($query)
    {
        return $query->where('type', 'tool');
    }

    /**
     * Scope to filter resources.
     */
    public function scopeResources($query)
    {
        return $query->where('type', 'resource');
    }

    /**
     * Scope to filter prompts.
     */
    public function scopePrompts($query)
    {
        return $query->where('type', 'prompt');
    }

    /**
     * Scope to filter system features.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to filter custom features.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Check if this feature is active for a given user.
     */
    public function isActiveForUser(User $user): bool
    {
        $setting = $this->userSettings()
            ->where('user_id', $user->id)
            ->first();

        // If no setting exists, use the default
        if (! $setting) {
            return $this->is_active_by_default;
        }

        return $setting->is_active;
    }

    /**
     * Get an instance of the handler class if it exists.
     */
    public function getHandlerInstance(): ?object
    {
        if (! $this->class_name || ! class_exists($this->class_name)) {
            return null;
        }

        return app($this->class_name);
    }
}
