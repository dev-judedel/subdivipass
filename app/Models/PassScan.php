<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassScan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'pass_id',
        'gate_id',
        'guard_id',
        'scan_type',
        'scan_method',
        'result',
        'result_message',
        'scan_data',
        'device_id',
        'ip_address',
        'user_agent',
        'location',
        'was_offline',
        'scanned_at',
        'synced_at',
    ];

    protected $casts = [
        'scan_data' => 'array',
        'location' => 'array',
        'was_offline' => 'boolean',
        'scanned_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }
}
