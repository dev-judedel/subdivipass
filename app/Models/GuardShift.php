<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'guard_id',
        'gate_id',
        'status',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
