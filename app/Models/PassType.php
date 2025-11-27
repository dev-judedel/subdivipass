<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PassType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subdivision_id',
        'name',
        'slug',
        'description',
        'config',
        'default_validity_hours',
        'max_validity_hours',
        'requires_approval',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'default_validity_hours' => 'integer',
        'max_validity_hours' => 'integer',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'requires_approval', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the subdivision that owns the pass type.
     */
    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class);
    }

    /**
     * Get the passes of this type.
     */
    public function passes()
    {
        return $this->hasMany(Pass::class, 'pass_type_id');
    }

    /**
     * Check if pass type is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if pass type requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval === true;
    }

    /**
     * Get required fields for this pass type.
     */
    public function getRequiredFields(): array
    {
        return $this->config['required_fields'] ?? [];
    }

    /**
     * Get optional fields for this pass type.
     */
    public function getOptionalFields(): array
    {
        return $this->config['optional_fields'] ?? [];
    }

    /**
     * Get validation rules for this pass type.
     */
    public function getValidationRules(): array
    {
        return $this->config['validation_rules'] ?? [];
    }
}
