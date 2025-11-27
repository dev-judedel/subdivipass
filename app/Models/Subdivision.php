<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subdivision extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'settings',
        'logo_path',
        'status',
        'notes',
        'curfew_enabled',
        'curfew_start',
        'curfew_end',
        'curfew_exemptions',
        'curfew_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'curfew_enabled' => 'boolean',
        'curfew_exemptions' => 'array',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'status', 'settings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the gates for the subdivision.
     */
    public function gates()
    {
        return $this->hasMany(Gate::class);
    }

    /**
     * Get the pass types for the subdivision.
     */
    public function passTypes()
    {
        return $this->hasMany(PassType::class);
    }

    /**
     * Get the passes for the subdivision.
     */
    public function passes()
    {
        return $this->hasMany(Pass::class);
    }

    /**
     * Get the users assigned to this subdivision.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'subdivision_user');
    }

    /**
     * Check if subdivision is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
