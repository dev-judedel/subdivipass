<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardIssueReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'guard_id',
        'pass_id',
        'gate_id',
        'issue_type',
        'severity',
        'description',
        'status',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }

    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }
}
