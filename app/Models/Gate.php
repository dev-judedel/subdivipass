<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Gate extends Model
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
        'code',
        'location',
        'coordinates',
        'type',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'coordinates' => 'array',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'type', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the subdivision that owns the gate.
     */
    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class);
    }

    /**
     * Get the pass scans for this gate.
     */
    public function passScans()
    {
        return $this->hasMany(PassScan::class);
    }

    /**
     * Get the users (guards) assigned to this gate.
     */
    public function guards()
    {
        return $this->belongsToMany(User::class, 'gate_user')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'guard');
            });
    }

    /**
     * Check if gate is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if gate allows entry.
     */
    public function allowsEntry(): bool
    {
        return in_array($this->type, ['entry', 'both']);
    }

    /**
     * Check if gate allows exit.
     */
    public function allowsExit(): bool
    {
        return in_array($this->type, ['exit', 'both']);
    }
}
