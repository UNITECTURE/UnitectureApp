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
        'secondary_supervisor_id',
        'joining_date',
        'status',
        'biometric_id',
        'telegram_chat_id',
        'leave_balance',
        'last_accrued_month',
        'profile_image',
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

    /** Primary supervisor (assigned during onboarding). */
    public function primarySupervisor()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    /** Optional secondary supervisor (assigned by admin). */
    public function secondarySupervisor()
    {
        return $this->belongsTo(User::class, 'secondary_supervisor_id');
    }

    /** Employees who report directly to this user (primary). */
    public function subordinates()
    {
        return $this->hasMany(User::class, 'reporting_to');
    }

    /** Employees who have this user as secondary supervisor. */
    public function secondarySubordinates()
    {
        return $this->hasMany(User::class, 'secondary_supervisor_id');
    }

    /** All employees this user can assign tasks to (primary + secondary team). */
    public function assignableEmployees()
    {
        return User::where('reporting_to', $this->id)->orWhere('secondary_supervisor_id', $this->id);
    }

    public function manualRequests()
    {
        return $this->hasMany(ManualAttendanceRequest::class)->orderBy('created_at', 'desc');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * The tasks assigned to the user.
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->wherePivot('type', 'assignee')
            ->withTimestamps();
    }

    // Accessor for backward compatibility (reading $user->name)
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    // Keep legacy name column in sync when full_name changes
    public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
        $this->attributes['name'] = $value;
    }

    // Helper Methods
    public function isSuperAdmin()
    {
        return (int) $this->role_id === 3;
    }

    public function isAdmin()
    {
        // Admin or Super Admin
        return in_array((int) $this->role_id, [2, 3]);
    }

    public function isSupervisor()
    {
        return (int) $this->role_id === 1;
    }

    public function isEmployee()
    {
        // Not Supervisor, Admin, or Super Admin
        return !in_array((int) $this->role_id, [1, 2, 3]);
    }
}
