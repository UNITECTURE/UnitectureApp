<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\TaskComment;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /** Tasks page URL included in Telegram notifications (clickable link). */
    private const TASKS_APP_LINK = 'http://hrms.unitecture.co/tasks';

    /**
     * All available task statuses.
     * New tasks default to 'not_started'.
     */
    const STATUSES = [
        'not_started',
        'wip',
        'correction',
        'completed',
        'revision',
        'closed',
        'hold',
        'under_review',
        'awaiting_resources',
    ];

    /**
     * All available task stages.
     */
    const STAGES = [
        'overdue',
        'pending',
        'in_progress',
        'completed',
    ];

    /**
     * Display a listing of the tasks (Overview Dashboard).
     * Scope: 'assigned' = my tasks, 'team' = my team tasks (supervisor/admin only), 'all' = all tasks (super admin only).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $scope = $request->get('scope', 'assigned');

        // Ensure priorities and stages reflect the latest (fallback if scheduler hasn't run yet)
        Task::bulkSyncPrioritiesFromDeadlines();
        Task::bulkSyncOverdueStages();

        // Employees always see only their assigned tasks
        if ($user->isEmployee()) {
            $tasks = $user->tasks()
                ->with(['project', 'assignees', 'taggedUsers'])
                ->latest()
                ->get();
            $scope = 'assigned';
        } elseif ($user->isSuperAdmin() && ($scope === 'all' || $scope === 'assigned')) {
            // Super Admin can see all tasks in the firm
            if ($scope === 'all') {
                $tasks = Task::with(['project', 'assignees', 'taggedUsers'])
                    ->latest()
                    ->get();
            } else {
                // If 'assigned' scope is selected, show only their tasks
                $tasks = $user->tasks()
                    ->with(['project', 'assignees', 'taggedUsers'])
                    ->latest()
                    ->get();
            }
        } elseif ($scope === 'team' && ($user->isSupervisor() || $user->isAdmin())) {
            // Team tasks: tasks assigned to team members
            $teamIds = User::where('reporting_to', $user->id)
                ->orWhere('secondary_supervisor_id', $user->id)
                ->pluck('id');
            $tasks = Task::whereHas('assignees', function ($query) use ($teamIds) {
                $query->whereIn('users.id', $teamIds);
            })
                ->with(['project', 'assignees', 'taggedUsers'])
                ->latest()
                ->get();
        } else {
            // Assigned to me (admin/supervisor)
            $tasks = $user->tasks()
                ->with(['project', 'assignees', 'taggedUsers'])
                ->latest()
                ->get();
            $scope = 'assigned';
        }

        // Format assignees' and tagged users' profile images
        $tasks = $tasks->map(function ($task) {
            $task->assignees = $task->assignees->map(function ($assignee) {
                if ($assignee->profile_image) {
                    if (filter_var($assignee->profile_image, FILTER_VALIDATE_URL)) {
                        $assignee->profile_image_url = $assignee->profile_image;
                    } else {
                        $assignee->profile_image_url = asset('storage/' . $assignee->profile_image);
                    }
                } else {
                    $assignee->profile_image_url = null;
                }
                return $assignee;
            });
            $task->taggedUsers = $task->taggedUsers->map(function ($tagged) {
                if ($tagged->profile_image) {
                    if (filter_var($tagged->profile_image, FILTER_VALIDATE_URL)) {
                        $tagged->profile_image_url = $tagged->profile_image;
                    } else {
                        $tagged->profile_image_url = asset('storage/' . $tagged->profile_image);
                    }
                } else {
                    $tagged->profile_image_url = null;
                }
                return $tagged;
            });
            return $task;
        });

        $counts = [
            'all' => $tasks->count(),
            'pending' => $tasks->where('stage', 'pending')->count(),
            'in_progress' => $tasks->where('stage', 'in_progress')->count(),
            'completed' => $tasks->where('stage', 'completed')->count(),
            'overdue' => $tasks->where('stage', 'overdue')->count(),
        ];

        // Employees for filter dropdown (admin/supervisor only)
        $employees = collect();
        if ($user->isAdmin()) {
            $employees = User::select('id', 'full_name', 'email', 'profile_image')
                ->orderBy('full_name')
                ->get();
        } elseif ($user->isSupervisor()) {
            $employees = User::select('id', 'full_name', 'email', 'profile_image')
                ->where('id', $user->id)
                ->orWhere('reporting_to', $user->id)
                ->orWhere('secondary_supervisor_id', $user->id)
                ->orderBy('full_name')
                ->get();
        }
        $employees = $employees->map(function ($u) {
            $u->profile_image_url = $u->profile_image && filter_var($u->profile_image, FILTER_VALIDATE_URL)
                ? $u->profile_image
                : ($u->profile_image ? asset('storage/' . $u->profile_image) : null);
            return $u;
        });

        $statuses = self::STATUSES;
        $stages = self::STAGES;
        $showTeamToggle = $user->isSupervisor() || $user->isAdmin();
        $showAllToggle = $user->isSuperAdmin();
        return view('tasks.index', compact('tasks', 'statuses', 'stages', 'counts', 'employees', 'scope', 'showTeamToggle', 'showAllToggle'));
    }

    /**
     * Display tasks assigned to the current user (Vertical/Card View).
     */
    public function assigned()
    {
        // Ensure priorities and stages reflect the latest (fallback if scheduler hasn't run yet)
        Task::bulkSyncPrioritiesFromDeadlines();
        Task::bulkSyncOverdueStages();

        $user = Auth::user();

        // Get tasks assigned to the user
        $tasks = $user->tasks()
            ->with(['project', 'assignees', 'taggedUsers'])
            ->latest()
            ->get();

        // Format assignees' and tagged users' profile images
        $tasks = $tasks->map(function ($task) {
            $task->assignees = $task->assignees->map(function ($assignee) {
                if ($assignee->profile_image) {
                    if (filter_var($assignee->profile_image, FILTER_VALIDATE_URL)) {
                        $assignee->profile_image_url = $assignee->profile_image;
                    } else {
                        $assignee->profile_image_url = asset('storage/' . $assignee->profile_image);
                    }
                } else {
                    $assignee->profile_image_url = null;
                }
                return $assignee;
            });
            $task->taggedUsers = $task->taggedUsers->map(function ($tagged) {
                if ($tagged->profile_image) {
                    if (filter_var($tagged->profile_image, FILTER_VALIDATE_URL)) {
                        $tagged->profile_image_url = $tagged->profile_image;
                    } else {
                        $tagged->profile_image_url = asset('storage/' . $tagged->profile_image);
                    }
                } else {
                    $tagged->profile_image_url = null;
                }
                return $tagged;
            });
            return $task;
        });

        $statuses = self::STATUSES;
        $stages = self::STAGES;
        return view('tasks.assigned', compact('tasks', 'statuses', 'stages', 'user'));
    }

    /**
     * Display tasks for supervisor's team.
     */
    public function teamTasks()
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure priorities and stages reflect the latest (fallback if scheduler hasn't run yet)
        Task::bulkSyncPrioritiesFromDeadlines();
        Task::bulkSyncOverdueStages();

        $user = Auth::user();
        
        // Get team member IDs (primary + secondary subordinates)
        $teamIds = User::where('reporting_to', $user->id)
            ->orWhere('secondary_supervisor_id', $user->id)
            ->pluck('id');
        
        // Get tasks assigned to team members
        $tasks = Task::whereHas('assignees', function ($query) use ($teamIds) {
                $query->whereIn('users.id', $teamIds);
            })
            ->with(['project', 'assignees', 'taggedUsers'])
            ->latest()
            ->get();

        // Format assignees' and tagged users' profile images
        $tasks = $tasks->map(function ($task) {
            $task->assignees = $task->assignees->map(function ($assignee) {
                if ($assignee->profile_image) {
                    if (filter_var($assignee->profile_image, FILTER_VALIDATE_URL)) {
                        $assignee->profile_image_url = $assignee->profile_image;
                    } else {
                        $assignee->profile_image_url = asset('storage/' . $assignee->profile_image);
                    }
                } else {
                    $assignee->profile_image_url = null;
                }
                return $assignee;
            });
            $task->taggedUsers = $task->taggedUsers->map(function ($tagged) {
                if ($tagged->profile_image) {
                    if (filter_var($tagged->profile_image, FILTER_VALIDATE_URL)) {
                        $tagged->profile_image_url = $tagged->profile_image;
                    } else {
                        $tagged->profile_image_url = asset('storage/' . $tagged->profile_image);
                    }
                } else {
                    $tagged->profile_image_url = null;
                }
                return $tagged;
            });
            return $task;
        });

        // Employees for filter dropdown (team members the supervisor can assign to)
        $employees = User::select('id', 'full_name', 'email', 'profile_image')
            ->where(function ($query) use ($user) {
                $query->where('id', $user->id)
                    ->orWhere('reporting_to', $user->id)
                    ->orWhere('secondary_supervisor_id', $user->id);
            })
            ->orderBy('full_name')
            ->get();
        $employees = $employees->map(function ($u) {
            $u->profile_image_url = $u->profile_image && filter_var($u->profile_image, FILTER_VALIDATE_URL)
                ? $u->profile_image
                : ($u->profile_image ? asset('storage/' . $u->profile_image) : null);
            return $u;
        });

        $statuses = self::STATUSES;
        $stages = self::STAGES;
        return view('tasks.team', compact('tasks', 'statuses', 'stages', 'user', 'employees'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all projects created by supervisors/admins (visible to all supervisors)
        $projects = Project::whereHas('creator', function ($query) {
                $query->whereIn('role_id', [1, 2, 3]); // Supervisor, Admin, Super Admin
            })
            ->orderBy('name')
            ->get();

        $currentUser = Auth::user();
        if ($currentUser->isAdmin()) {
            // Admin and Super Admin can assign tasks to all users, including supervisors
            $users = User::orderBy('full_name')->get();
        } else {
            // Supervisor: themselves + primary subordinates + secondary subordinates
            $users = User::where('id', $currentUser->id)
                ->orWhere('reporting_to', $currentUser->id)
                ->orWhere('secondary_supervisor_id', $currentUser->id)
                ->orderBy('full_name')
                ->get();
        }

        return view('tasks.create', compact('projects', 'users'));
    }

    /**
     * Get employees for task assignment (API endpoint).
     */
    public function getEmployees()
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $currentUser = Auth::user();
        if ($currentUser->isAdmin()) {
            // Admin and Super Admin can assign tasks to all users, including supervisors
            $users = User::select('id', 'full_name', 'email', 'profile_image')
                ->orderBy('full_name')
                ->get();
        } else {
            // Supervisor: themselves + primary + secondary subordinates
            $users = User::select('id', 'full_name', 'email', 'profile_image')
                ->where('id', $currentUser->id)
                ->orWhere('reporting_to', $currentUser->id)
                ->orWhere('secondary_supervisor_id', $currentUser->id)
                ->orderBy('full_name')
                ->get();
        }

        // Format profile image URLs
        $users = $users->map(function ($user) {
            if ($user->profile_image) {
                // If it's already a full URL (Cloudinary or absolute URL), use it as is
                if (filter_var($user->profile_image, FILTER_VALIDATE_URL)) {
                    $user->profile_image_url = $user->profile_image;
                } else {
                    // If it's a relative path, construct the full URL
                    $user->profile_image_url = asset('storage/' . $user->profile_image);
                }
            } else {
                $user->profile_image_url = null;
            }
            return $user;
        });

        return response()->json($users);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->project_id) {
                        $project = Project::find($request->project_id);
                        if ($project && $value < $project->start_date) { // Valid comparison for standard Y-m-d dates
                            $fail('The task start date cannot be before the project start date (' . $project->start_date->format('Y-m-d') . ').');
                        }
                    }
                },
            ],
            // 'end_date' field in DB is datetime. We accept separate date and time inputs.
            'end_date_input' => 'nullable|date|after_or_equal:start_date',
            'end_time_input' => 'nullable|date_format:H:i',
            'priority' => 'required|in:high,medium,low,free',
            // Optional initial comment from the creator when creating the task
            'comments' => 'nullable|string|max:2000',
        ]);

        if (in_array(auth()->user()->role->name ?? '', ['admin', 'supervisor'])) {
            if ($request->has('status')) {
                $request->validate(['status' => 'in:' . implode(',', self::STATUSES)]);
            }
        }

        // Combine Date and Time for end_date
        $endDate = null;
        if ($request->filled('end_date_input')) {
            $endDate = $request->end_date_input;
            if ($request->filled('end_time_input') && $request->priority !== 'free') {
                $endDate .= ' ' . $request->end_time_input . ':00';
            } else {
                $endDate .= ' 23:59:59'; // Default to end of day if no time
            }
        }

        // Prepare data for saving
        $data = $request->except(['end_date_input', 'end_time_input', 'assignees', 'tagged', 'comments']);
        $data['end_date'] = $endDate;

        $task = new Task($data);
        $task->status = $request->has('status') ? $request->status : 'not_started';
        // Stage is set automatically by Task::syncStageFromStatusAndDueDate (in saving callback)
        $task->created_by = Auth::id();
        $task->save();

        // Persist initial comment (if provided) as a TaskComment
        $initialCommentText = trim((string) $request->input('comments', ''));
        if ($initialCommentText !== '') {
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'comment' => $initialCommentText,
            ]);
        }

        // Attach Assignees
        if ($request->has('assignees') && !empty($request->assignees)) {
            $task->assignees()->attach($request->assignees, ['type' => 'assignee']);
        }

        // Attach Tagged Users
        if ($request->has('tagged') && is_array($request->tagged) && !empty($request->tagged)) {
            $task->taggedUsers()->attach($request->tagged, ['type' => 'tagged']);

            // Notify tagged users via Telegram
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = new TelegramService('task');
                $creator = Auth::user();
                $project = $task->project()->first();

                $taggedUsers = User::whereIn('id', $request->tagged)
                    ->whereNotNull('telegram_chat_id')
                    ->get();

                // Also notify the task creator (supervisor/admin) if they have Telegram mapped
                $creatorRecipient = null;
                if ($creator && $creator->telegram_chat_id) {
                    $creatorRecipient = $creator;
                }

                foreach ($taggedUsers as $taggedUser) {
                    $lines = [];
                    $lines[] = '🔔 <b>You were tagged on a task</b>';
                    $lines[] = '';
                    $lines[] = '<b>Task:</b> ' . e(\Illuminate\Support\Str::limit($task->description, 80));

                    if ($project) {
                        $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                    }

                    if ($task->end_date) {
                        $due = \Carbon\Carbon::parse($task->end_date)->format('d M Y H:i');
                        $lines[] = '<b>Due:</b> ' . $due;
                    }

                    if ($creator) {
                        $lines[] = '<b>Created by:</b> ' . e($creator->name);
                    }

                    if ($task->priority) {
                        $lines[] = '<b>Priority:</b> ' . ucfirst($task->priority);
                    }

                    $lines[] = '';
                    $lines[] = 'View tasks: <a href="' . self::TASKS_APP_LINK . '">' . self::TASKS_APP_LINK . '</a>';

                    $message = implode("\n", $lines);

                    $telegram->sendMessage($taggedUser->telegram_chat_id, $message);
                }

                // Send a summary notification to the creator (supervisor/admin)
                if ($creatorRecipient) {
                    $lines = [];
                    $lines[] = '🔔 <b>Task created & people tagged</b>';
                    $lines[] = '';
                    $lines[] = '<b>Task:</b> ' . e(\Illuminate\Support\Str::limit($task->description, 80));

                    if ($project) {
                        $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                    }

                    if (!empty($request->tagged)) {
                        $taggedNames = $taggedUsers->pluck('name')->implode(', ');
                        $lines[] = '<b>Tagged:</b> ' . e($taggedNames);
                    }

                    if ($task->end_date) {
                        $due = \Carbon\Carbon::parse($task->end_date)->format('d M Y H:i');
                        $lines[] = '<b>Due:</b> ' . $due;
                    }

                    if ($task->priority) {
                        $lines[] = '<b>Priority:</b> ' . ucfirst($task->priority);
                    }

                    $lines[] = '';
                    $lines[] = 'View tasks: <a href="' . self::TASKS_APP_LINK . '">' . self::TASKS_APP_LINK . '</a>';

                    $message = implode("\n", $lines);

                    $telegram->sendMessage($creatorRecipient->telegram_chat_id, $message);
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram task tag notifications: ' . $e->getMessage());
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $user = Auth::user();
        if (!$this->canViewTask($user, $task)) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json([
            'task' => $task->load(['project', 'assignees', 'taggedUsers', 'creator'])
        ]);
    }

    /**
     * Update the task status.
     */
    public function updateStatus(Request $request, Task $task)
    {
        // Supervisors/Admins can use the full status enum.
        // Employees are restricted to: under_review, completed, wip, revision.
        $user = Auth::user();

        // Once a task is closed, its status cannot be changed anymore.
        if ($task->status === 'closed' && $request->input('status') !== 'closed') {
            return response()->json([
                'message' => 'Closed tasks cannot be re-opened or changed.',
                'status' => $task->status,
                'stage' => $task->stage,
            ], 403);
        }
        $allowedStatuses = self::STATUSES;
        if ($user->isEmployee()) {
            $allowedStatuses = ['under_review', 'completed', 'wip', 'revision'];
        }

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
        ]);

        $oldStatus = $task->status;
        $task->status = $validated['status'];
        // Stage is synced automatically by Task::syncStageFromStatusAndDueDate (in saving callback)
        $task->save();

        // Notify assignees and tagged users via Telegram about status change
        if ($oldStatus !== $task->status) {
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = new TelegramService('task');
                $actor = Auth::user();
                $project = $task->project()->first();

                $task->loadMissing(['assignees', 'taggedUsers', 'creator']);
                $recipientIds = $task->assignees->pluck('id')
                    ->merge($task->taggedUsers->pluck('id'))
                    ->push(optional($task->creator)->id)
                    ->filter()
                    ->unique()
                    ->all();

                if (!empty($recipientIds)) {
                    $recipients = User::whereIn('id', $recipientIds)
                        ->whereNotNull('telegram_chat_id')
                        ->get();

                    foreach ($recipients as $user) {
                        $lines = [];
                        $lines[] = '🔔 <b>Task status updated</b>';
                        $lines[] = '';
                        $lines[] = '<b>Task:</b> ' . e(\Illuminate\Support\Str::limit($task->description, 80));

                        if ($project) {
                            $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                        }

                        $lines[] = '<b>New Status:</b> ' . ucfirst(str_replace('_', ' ', $task->status));

                        if ($actor) {
                            $lines[] = '<b>Updated by:</b> ' . e($actor->name);
                        }

                        $lines[] = '';
                        $lines[] = 'View tasks: <a href="' . self::TASKS_APP_LINK . '">' . self::TASKS_APP_LINK . '</a>';

                        $message = implode("\n", $lines);

                        $telegram->sendMessage($user->telegram_chat_id, $message);
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram task status notifications: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $task->status,
            'stage' => $task->stage,
        ]);
    }

    /**
     * Update the task stage. Stages cannot be changed manually; they are set automatically
     * based on status and due date (pending, in_progress when wip, completed when closed, overdue when past due).
     */
    public function updateStage(Request $request, Task $task)
    {
        return response()->json([
            'message' => 'Stages cannot be changed manually. They are set automatically based on status and due date.',
        ], 403);
    }

    /**
     * Delete a task (and its relations).
     *
     * Supervisors can delete only tasks they created.
     * Admins can delete any task.
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $taskCreatedBy = (int) $task->created_by;

        // Only admins and supervisors can delete tasks
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            abort(403, 'Unauthorized to delete tasks.');
        }

        // For supervisors (not admins), check if they manage the task creator or created it themselves
        if (!$user->isAdmin() && $user->isSupervisor()) {
            // If supervisor created the task themselves, allow deletion
            if ($taskCreatedBy === $userId) {
                $task->delete();
                return request()->wantsJson() 
                    ? response()->json(['message' => 'Task deleted successfully.'])
                    : redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
            }

            // Otherwise check if the task creator is in this supervisor's team
            $taskCreator = $task->creator;
            if (!$taskCreator) {
                abort(404, 'Task creator not found.');
            }

            $creatorReportingTo = (int) ($taskCreator->reporting_to ?? 0);
            $creatorSecondaryId = (int) ($taskCreator->secondary_supervisor_id ?? 0);

            $isInTeam = $creatorReportingTo === $userId || $creatorSecondaryId === $userId;

            if (!$isInTeam) {
                abort(403, 'You can only delete tasks from your team.');
            }
        }

        $task->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully.']);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Update the task due date and time.
     * Only supervisors and admins are allowed to edit this.
     */
    public function updateDue(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user->isSupervisor() && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'end_date_input' => 'required|date',
            'end_time_input' => 'nullable|date_format:H:i',
        ]);

        // Ensure end date is not before task start date (if present)
        if ($task->start_date && $data['end_date_input'] < $task->start_date->toDateString()) {
            return response()->json([
                'message' => 'End date cannot be before task start date (' . $task->start_date->format('Y-m-d') . ').',
            ], 422);
        }

        // Combine date and time similar to store()
        $endDateTime = $data['end_date_input'];
        if ($task->priority === 'free') {
            // Free tasks always end at 23:59:59
            $endDateTime .= ' 23:59:59';
        } else {
            if (!empty($data['end_time_input'])) {
                $endDateTime .= ' ' . $data['end_time_input'] . ':00';
            } else {
                $endDateTime .= ' 23:59:59';
            }
        }

        $oldDueDate = $task->end_date;
        $task->end_date = $endDateTime;
        // Stage is synced automatically by Task::syncStageFromStatusAndDueDate (in saving callback)
        $task->save();

        // Notify assignees and tagged users via Telegram about due date change
        if ($oldDueDate != $task->end_date) {
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = new TelegramService('task');
                $actor = Auth::user();
                $project = $task->project()->first();

                $task->loadMissing(['assignees', 'taggedUsers', 'creator']);
                $recipientIds = $task->assignees->pluck('id')
                    ->merge($task->taggedUsers->pluck('id'))
                    ->push(optional($task->creator)->id)
                    ->filter()
                    ->unique()
                    ->all();

                if (!empty($recipientIds)) {
                    $recipients = User::whereIn('id', $recipientIds)
                        ->whereNotNull('telegram_chat_id')
                        ->get();

                    foreach ($recipients as $user) {
                        $lines = [];
                        $lines[] = '🔔 <b>Task due date updated</b>';
                        $lines[] = '';
                        $lines[] = '<b>Task:</b> ' . e(\Illuminate\Support\Str::limit($task->description, 80));

                        if ($project) {
                            $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                        }

                        if ($oldDueDate) {
                            $lines[] = '<b>Previous Due:</b> ' . \Carbon\Carbon::parse($oldDueDate)->format('d M Y H:i');
                        }
                        $lines[] = '<b>New Due:</b> ' . \Carbon\Carbon::parse($task->end_date)->format('d M Y H:i');

                        if ($actor) {
                            $lines[] = '<b>Updated by:</b> ' . e($actor->name);
                        }

                        $lines[] = '';
                        $lines[] = 'View tasks: <a href="' . self::TASKS_APP_LINK . '">' . self::TASKS_APP_LINK . '</a>';
                        $telegram->sendMessage($user->telegram_chat_id, implode("\n", $lines));
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram task due date notifications: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Due date updated successfully.',
            'end_date' => $task->end_date->toIso8601String(),
            'stage' => $task->stage,
        ]);
    }

    /**
     * Update assignees and tagged users on a task.
     * Only supervisors and admins are allowed.
     */
    public function updatePeople(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user->isSupervisor() && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        if (!$this->canViewTask($user, $task)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'tagged' => 'nullable|array',
            'tagged.*' => 'exists:users,id',
        ]);

        $assigneeIds = array_values(array_unique($request->input('assignees', [])));
        $taggedIds = array_values(array_unique($request->input('tagged', [])));

        $oldAssigneeIds = $task->assignees->pluck('id')->all();
        $oldTaggedIds = $task->taggedUsers->pluck('id')->all();

        $task->assignees()->sync(
            collect($assigneeIds)->mapWithKeys(fn ($id) => [$id => ['type' => 'assignee']])->all()
        );
        $task->taggedUsers()->sync(
            collect($taggedIds)->mapWithKeys(fn ($id) => [$id => ['type' => 'tagged']])->all()
        );

        // Notify newly added assignees and tagged users
        $newAssigneeIds = array_diff($assigneeIds, $oldAssigneeIds);
        $newTaggedIds = array_diff($taggedIds, $oldTaggedIds);
        $allNewUserIds = array_merge($newAssigneeIds, $newTaggedIds);

        if (!empty($allNewUserIds)) {
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = new TelegramService('task');
                $actor = Auth::user();
                $project = $task->project()->first();
                $newUsers = User::whereIn('id', $allNewUserIds)
                    ->whereNotNull('telegram_chat_id')
                    ->get();
                foreach ($newUsers as $user) {
                    $lines = [];
                    if (in_array($user->id, $newAssigneeIds)) {
                        $lines[] = '🔔 <b>You were assigned a task</b>';
                    } else {
                        $lines[] = '🔔 <b>You were tagged on a task</b>';
                    }
                    $lines[] = '';
                    $lines[] = '<b>Task:</b> ' . e(\Illuminate\Support\Str::limit($task->description, 80));
                    if ($project) {
                        $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                    }
                    if ($task->end_date) {
                        $lines[] = '<b>Due:</b> ' . \Carbon\Carbon::parse($task->end_date)->format('d M Y H:i');
                    }
                    if ($actor) {
                        $lines[] = '<b>By:</b> ' . e($actor->name);
                    }
                    $lines[] = '';
                    $lines[] = 'View tasks: <a href="' . self::TASKS_APP_LINK . '">' . self::TASKS_APP_LINK . '</a>';
                    $telegram->sendMessage($user->telegram_chat_id, implode("\n", $lines));
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram tag notifications: ' . $e->getMessage());
            }
        }

        $task->load(['assignees', 'taggedUsers']);
        $assignees = $task->assignees->map(function ($a) {
            $a->profile_image_url = $a->profile_image && !filter_var($a->profile_image, FILTER_VALIDATE_URL)
                ? asset('storage/' . $a->profile_image) : ($a->profile_image ?? null);
            return $a;
        });
        $taggedUsers = $task->taggedUsers->map(function ($t) {
            $t->profile_image_url = $t->profile_image && !filter_var($t->profile_image, FILTER_VALIDATE_URL)
                ? asset('storage/' . $t->profile_image) : ($t->profile_image ?? null);
            return $t;
        });

        return response()->json([
            'message' => 'People updated successfully.',
            'assignees' => $assignees,
            'tagged_users' => $taggedUsers,
        ]);
    }

    /**
     * List comments for a task.
     */
    public function comments(Task $task)
    {
        $user = Auth::user();
        if (!$this->canViewTask($user, $task)) {
            abort(403, 'Unauthorized action.');
        }

        $comments = $task->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(function (TaskComment $comment) {
                $commentUser = $comment->user;
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->toDateTimeString(),
                    'created_at_human' => $comment->created_at->format('M j, Y g:i A'),
                    'user' => [
                        'id' => $commentUser?->id,
                        'name' => $commentUser?->full_name ?? $commentUser?->name ?? 'Unknown',
                    ],
                ];
            });

        return response()->json($comments);
    }

    /**
     * Store a new comment on a task.
     */
    public function addComment(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$this->canViewTask($user, $task)) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        // Guard against accidental double-posts (e.g. event bound twice, double-click, flaky network retries)
        $text = trim((string) $data['comment']);
        $recentDuplicate = TaskComment::query()
            ->where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('comment', $text)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->latest('id')
            ->first();

        $comment = $recentDuplicate ?: TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $text,
        ]);

        $comment->load('user');

        // Notify assignees and tagged users about new comment via Telegram
        if (!$recentDuplicate) {
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = new TelegramService('task');
                $project = $task->project()->first();

                $task->loadMissing(['assignees', 'taggedUsers', 'creator']);
                $recipientIds = $task->assignees->pluck('id')
                    ->merge($task->taggedUsers->pluck('id'))
                    ->push(optional($task->creator)->id)
                    ->filter(fn($id) => $id != $user->id) // Don't notify the commenter
                    ->unique()
                    ->all();

                if (!empty($recipientIds)) {
                    $recipients = User::whereIn('id', $recipientIds)
                        ->whereNotNull('telegram_chat_id')
                        ->get();

                    foreach ($recipients as $recipient) {
                        $lines = [];
                        $lines[] = '💬 <b>New comment on task</b>';
                        $lines[] = '';
                        $lines[] = '<b>Task:</b> ' . e(\Illuminate\Support\Str::limit($task->description, 80));
                        if ($project) {
                            $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                        }
                        $lines[] = '<b>Comment by:</b> ' . e($user->full_name ?? $user->name);
                        $lines[] = '<b>Comment:</b> ' . e(\Illuminate\Support\Str::limit($comment->comment, 100));
                        $lines[] = '';
                        $lines[] = 'View tasks: <a href="' . self::TASKS_APP_LINK . '">' . self::TASKS_APP_LINK . '</a>';
                        $telegram->sendMessage($recipient->telegram_chat_id, implode("\n", $lines));
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram task comment notifications: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => $recentDuplicate ? 'Comment already added.' : 'Comment added successfully.',
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toDateTimeString(),
                'created_at_human' => $comment->created_at->format('M j, Y g:i A'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name,
                ],
            ],
        ]);
    }

    /**
     * Determine if the user can view/comment on the task.
     */
    private function canViewTask(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        $task->loadMissing(['assignees', 'taggedUsers']);

        if ($task->assignees->contains('id', $user->id)) {
            return true;
        }

        if ($task->taggedUsers->contains('id', $user->id)) {
            return true;
        }

        return false;
    }
}
