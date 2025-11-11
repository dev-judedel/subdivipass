<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Pass extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subdivision_id',
        'pass_type_id',
        'requester_id',
        'approver_id',
        'uuid',
        'pass_number',
        'qr_code_path',
        'qr_signature',
        'pin',
        'visitor_name',
        'visitor_contact',
        'visitor_email',
        'visitor_company',
        'vehicle_plate',
        'vehicle_model',
        'purpose',
        'destination',
        'notes',
        'metadata',
        'valid_from',
        'valid_to',
        'status',
        'approved_at',
        'rejected_at',
        'revoked_at',
        'rejection_reason',
        'revocation_reason',
        'scan_count',
        'last_scanned_at',
        'last_scanned_gate_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_scanned_at' => 'datetime',
        'scan_count' => 'integer',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'visitor_name', 'valid_from', 'valid_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pass) {
            if (empty($pass->uuid)) {
                $pass->uuid = (string) Str::uuid();
            }

            if (empty($pass->pin)) {
                $pass->pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            }

            if (empty($pass->pass_number)) {
                $pass->pass_number = static::generatePassNumber($pass->subdivision_id);
            }
        });
    }

    /**
     * Generate a unique pass number.
     */
    protected static function generatePassNumber($subdivisionId): string
    {
        $subdivision = Subdivision::find($subdivisionId);
        $code = $subdivision->code ?? 'PASS';
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())
            ->where('subdivision_id', $subdivisionId)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $code, $date, $count);
    }

    /**
     * Get the subdivision that owns the pass.
     */
    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class);
    }

    /**
     * Get the pass type.
     */
    public function type()
    {
        return $this->belongsTo(PassType::class, 'pass_type_id');
    }

    /**
     * Get the requester (user who created the pass).
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the approver (user who approved the pass).
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the pass scans.
     */
    public function scans()
    {
        return $this->hasMany(PassScan::class);
    }

    /**
     * Get the pass logs.
     */
    public function logs()
    {
        return $this->hasMany(PassLog::class);
    }

    /**
     * Check if pass is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->valid_from <= now()
            && $this->valid_to >= now();
    }

    /**
     * Check if pass is valid (time-wise).
     */
    public function isValid(): bool
    {
        return $this->valid_from <= now() && $this->valid_to >= now();
    }

    /**
     * Check if pass is expired.
     */
    public function isExpired(): bool
    {
        return $this->valid_to < now();
    }

    /**
     * Check if pass requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->type->requiresApproval();
    }

    /**
     * Check if pass is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'active';
    }

    public function isBlacklisted(): bool
    {
        return (bool) ($this->metadata['blacklisted'] ?? false);
    }

    /**
     * Check if pass is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if pass is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if pass is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Approve the pass.
     */
    public function approve(User $approver): bool
    {
        $this->update([
            'status' => 'approved',
            'approver_id' => $approver->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the pass.
     */
    public function reject(User $approver, string $reason = null): bool
    {
        $this->update([
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Revoke the pass.
     */
    public function revoke(string $reason = null): bool
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Activate the pass (after approval).
     */
    public function activate(): bool
    {
        if ($this->isApproved() && $this->isValid()) {
            $this->update(['status' => 'active']);
            return true;
        }

        return false;
    }

    /**
     * Expire the pass.
     */
    public function expire(): bool
    {
        $this->update(['status' => 'expired']);
        return true;
    }

    /**
     * Record a scan.
     */
    public function recordScan(Gate $gate, User $guard): void
    {
        $this->increment('scan_count');
        $this->update([
            'last_scanned_at' => now(),
            'last_scanned_gate_id' => $gate->id,
        ]);
    }
}
