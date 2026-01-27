<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'time_estimate',
        'priority',
        'category_tags',
        'status',
        'stage',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'datetime',
    ];

    /**
     * Get the project that holds the task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The users that belong to the task (Assignees).
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->wherePivot('type', 'assignee')
            ->withTimestamps();
    }

    /**
     * The users that are tagged in the task.
     */
    public function taggedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->wherePivot('type', 'tagged')
            ->withTimestamps();
    }

    /**
     * Comments associated with this task.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }
}
