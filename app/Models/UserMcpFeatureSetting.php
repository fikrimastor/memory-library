<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMcpFeatureSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'mcp_feature_id',
        'is_active',
        'custom_config',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'custom_config' => 'array',
        ];
    }

    /**
     * Get the user that owns the setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feature that this setting belongs to.
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(McpFeature::class, 'mcp_feature_id');
    }

    /**
     * Scope to filter active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter inactive settings.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
