<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'pass_id',
        'gate_id',
        'guard_id',
        'method',
        'input_code',
        'status',
        'message',
        'meta',
        'ip_address',
        'was_offline',
    ];

    protected $casts = [
        'meta' => 'array',
        'was_offline' => 'boolean',
    ];

    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }

    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }
}
