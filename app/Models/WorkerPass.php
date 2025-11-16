<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WorkerPass extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'pass_id',
        'worker_name',
        'worker_contact',
        'worker_email',
        'worker_position',
        'worker_id_number',
        'photo_path',
        'qr_code_path',
        'qr_signature',
        'is_admitted',
        'last_scan_at',
        'last_scan_gate_id',
        'last_scan_guard_id',
        'total_scans',
        'status',
        'notes',
    ];

    protected $casts = [
        'is_admitted' => 'boolean',
        'last_scan_at' => 'datetime',
        'total_scans' => 'integer',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['worker_name', 'status', 'is_admitted'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the parent pass.
     */
    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }

    /**
     * Get the last scan gate.
     */
    public function lastScanGate()
    {
        return $this->belongsTo(Gate::class, 'last_scan_gate_id');
    }

    /**
     * Get the last scan guard.
     */
    public function lastScanGuard()
    {
        return $this->belongsTo(User::class, 'last_scan_guard_id');
    }

    /**
     * Check if worker is currently admitted.
     */
    public function isAdmitted(): bool
    {
        return $this->is_admitted && $this->status === 'active';
    }

    /**
     * Check if worker pass is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->pass->isActive();
    }

    /**
     * Admit the worker (mark as scanned in).
     */
    public function admit(Gate $gate, User $guard): void
    {
        $this->update([
            'is_admitted' => true,
            'last_scan_at' => now(),
            'last_scan_gate_id' => $gate->id,
            'last_scan_guard_id' => $guard->id,
            'total_scans' => $this->total_scans + 1,
        ]);
    }

    /**
     * Exit the worker (mark as left).
     */
    public function exit(): void
    {
        $this->update(['is_admitted' => false]);
    }

    /**
     * Get the worker's photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    /**
     * Get the worker's QR code URL.
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : null;
    }

    /**
     * Reset all admissions for this pass (new day).
     */
    public static function resetDailyAdmissions(int $passId): void
    {
        static::where('pass_id', $passId)->update(['is_admitted' => false]);
    }
}
