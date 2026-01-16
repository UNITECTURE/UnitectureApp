<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    // Table explicitly matched to user spec
    protected $table = 'attendance_logs';

    // Disable automatic timestamp management (created_at is handled by DB default)
    public $timestamps = false;

    protected $fillable = [
        'biometric_id',
        'punch_time',
        'device_id'
    ];
    
    // Explicitly define dates if needed, though punch_time is custom
    protected $casts = [
        'punch_time' => 'datetime',
        'created_at' => 'datetime',
    ];
}
