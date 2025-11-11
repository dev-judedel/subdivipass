<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'pass_id',
        'user_id',
        'gate_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'logged_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'logged_at' => 'datetime',
    ];

    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }
}
