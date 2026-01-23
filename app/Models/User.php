<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role_id',
        'reporting_to',
        'joining_date',
        'status',
        'biometric_id',
        'telegram_chat_id',
        'leave_balance',
        'last_accrued_month',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'joining_date' => 'date',
        'role_id' => 'integer',
        'leave_balance' => 'decimal:2',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'reporting_to');
    }

    public function manualRequests()
    {
        return $this->hasMany(ManualAttendanceRequest::class)->orderBy('created_at', 'desc');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Accessor for backward compatibility (reading $user->name)
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    // Helper Methods
    public function isAdmin()
    {
        return $this->role_id === 2;
    }

    public function isSupervisor()
    {
        return $this->role_id === 1;
    }

    public function isEmployee()
    {
        return !in_array($this->role_id, [1, 2]);
    }
}
