<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'description',
        'start_date',
        'end_date',
        'priority',
        'category_tags',
        'status',
        'stage',
        'created_by',
    ];

    /** Default attribute values. New tasks are "not_started" by default. */
    protected $attributes = [
        'status' => 'not_started',
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

    protected static function booted(): void
    {
        static::saving(function (self $task) {
            $task->syncPriorityFromDeadline();
            $task->syncStageFromStatusAndDueDate();
        });

        static::addGlobalScope('active_project_tasks', function ($builder) {
            $builder->whereHas('project', function ($query) {
                $query->where('is_parked', false);
            });
        });
    }

    public function syncStageFromStatusAndDueDate(?Carbon $now = null): void
    {
        $now = $now ?? now();

        if (in_array($this->status, ['closed', 'completed'], true)) {
            $this->stage = 'completed';
            return;
        }

        if ($this->end_date) {
            $due = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);
            if ($due->isPast()) {
                $this->stage = 'overdue';
                return;
            }
        }

        // Only "not_started" and "correction" should remain in the "pending" stage.
        if (in_array($this->status, ['not_started', 'correction'], true)) {
            $this->stage = 'pending';
            return;
        }

        // Any other active status (wip, under_review, revision, hold, etc.)
        // should be considered "in_progress" while it is not closed/overdue.
        $this->stage = 'in_progress';
    }

    /**
     * Bulk sync stage to 'overdue' for tasks past their due date.
     * Completed tasks are left unchanged.
     */
    public static function bulkSyncOverdueStages(?Carbon $now = null): int
    {
        $now = $now ?? now();

        return (int) DB::table('tasks')
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now->toDateTimeString())
            ->where('status', '!=', 'closed')
            ->where(function ($q) {
                $q->whereNull('stage')->orWhere('stage', '!=', 'overdue');
            })
            ->update(['stage' => 'overdue']);
    }

    /**
     * Calculate the priority based on time remaining to end_date.
     *
     * Rules (from provided sheet):
     * - Overdue: Any -> high
     * - <= 24 hours: low/medium -> high (high stays high)
     * - <= 48 hours: low -> medium (medium/high stay as-is)
     * - > 48 hours: no change
     *
     * Note: "free" is treated as a special value and is only escalated when overdue.
     */
    public function calculatePriorityFromDeadline(?Carbon $now = null): string
    {
        $now = $now ?? now();

        $current = (string) ($this->getRawOriginal('priority') ?? $this->attributes['priority'] ?? $this->priority ?? 'medium');
        $current = strtolower(trim($current));

        if (!$this->end_date) {
            return $current;
        }

        $due = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);

        // Overdue always escalates to high (sheet: Any -> High)
        if ($due->isPast()) {
            return 'high';
        }

        // Preserve "free" unless overdue.
        if ($current === 'free') {
            return 'free';
        }

        $hoursRemaining = $now->diffInHours($due, false);

        if ($hoursRemaining <= 24) {
            return $current === 'high' ? 'high' : 'high';
        }

        if ($hoursRemaining <= 48) {
            return $current === 'low' ? 'medium' : $current;
        }

        return $current;
    }

    /**
     * Mutate this Task instance's priority to match the deadline rules.
     * Does not save by itself.
     */
    public function syncPriorityFromDeadline(?Carbon $now = null): void
    {
        $newPriority = $this->calculatePriorityFromDeadline($now);
        if (!empty($newPriority) && $this->priority !== $newPriority) {
            $this->priority = $newPriority;
        }
    }

    /**
     * Bulk sync priorities in the DB using a single SQL update.
     *
     * @param  \Carbon\Carbon|null  $now
     * @param  iterable<int>|null   $taskIds Optional scope by task IDs
     * @return int affected rows
     */
    public static function bulkSyncPrioritiesFromDeadlines(?Carbon $now = null, ?iterable $taskIds = null): int
    {
        $now = $now ?? now();
        $t24 = $now->copy()->addHours(24);
        $t48 = $now->copy()->addHours(48);

        $query = DB::table('tasks')
            ->whereNotNull('end_date')
            ->where(function ($q) {
                // Avoid touching completed tasks to preserve history
                $q->whereNull('stage')->orWhere('stage', '!=', 'completed');
            });

        if ($taskIds !== null) {
            $ids = collect($taskIds)->filter()->values()->all();
            if (empty($ids)) {
                return 0;
            }
            $query->whereIn('id', $ids);
        }

        // MySQL-friendly CASE update.
        return (int) $query->update([
            'priority' => DB::raw(
                "CASE
                    WHEN end_date < '{$now->toDateTimeString()}' AND priority <> 'high' THEN 'high'
                    WHEN end_date <= '{$t24->toDateTimeString()}' AND priority IN ('low','medium') THEN 'high'
                    WHEN end_date <= '{$t48->toDateTimeString()}' AND priority = 'low' THEN 'medium'
                    ELSE priority
                END"
            ),
        ]);
    }

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
