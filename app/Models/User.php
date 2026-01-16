<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'joining_date' => 'date',
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

    // Accessor for backward compatibility (reading $user->name)
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    public function manualRequests()
    {
        return $this->hasMany(ManualAttendanceRequest::class)->orderBy('created_at', 'desc');
    }
}
