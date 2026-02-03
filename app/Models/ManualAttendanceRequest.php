<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualAttendanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'duration',
        'reason',
        'status',
        'approved_by',
        'rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
