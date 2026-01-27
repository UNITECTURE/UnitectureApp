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
    /**
     * All available task statuses.
     */
    const STATUSES = [
        'wip',
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
     */
    public function index()
    {
        // For now, show all tasks if admin/supervisor, or assigned tasks if employee
        $user = Auth::user();

        if ($user->isEmployee()) {
            $tasks = $user->tasks()
                ->with(['project', 'assignees'])
                ->latest()
                ->get();
        } else {
            // Supervisors and Admins see all tasks
            $tasks = Task::with(['project', 'assignees', 'creator'])
                ->latest()
                ->get();
        }

        // Format assignees' profile images
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
            return $task;
        });

        $counts = [
            'all' => $tasks->count(),
            'pending' => $tasks->where('stage', 'pending')->count(),
            'in_progress' => $tasks->where('stage', 'in_progress')->count(),
            'completed' => $tasks->where('stage', 'completed')->count(),
            'overdue' => $tasks->where('stage', 'overdue')->count(),
        ];

        $statuses = self::STATUSES;
        $stages = self::STAGES;
        return view('tasks.index', compact('tasks', 'statuses', 'stages', 'counts'));
    }

    /**
     * Display tasks assigned to the current user (Vertical/Card View).
     */
    public function assigned()
    {
        $user = Auth::user();

        // Get tasks assigned to the user
        $tasks = $user->tasks()
            ->with(['project', 'assignees'])
            ->latest()
            ->get();

        // Format assignees' profile images
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

        $user = Auth::user();
        
        // Get team member IDs (subordinates)
        $teamIds = User::where('reporting_to', $user->id)->pluck('id');
        
        // Get tasks assigned to team members
        $tasks = Task::whereHas('assignees', function ($query) use ($teamIds) {
                $query->whereIn('users.id', $teamIds);
            })
            ->with(['project', 'assignees'])
            ->latest()
            ->get();

        // Format assignees' profile images
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
            return $task;
        });

        $statuses = self::STATUSES;
        $stages = self::STAGES;
        return view('tasks.team', compact('tasks', 'statuses', 'stages', 'user'));
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
            $users = User::orderBy('full_name')->get();
        } else {
            // Supervisor: Show themselves + their subordinates
            $users = User::where('id', $currentUser->id)
                ->orWhere('reporting_to', $currentUser->id)
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
            $users = User::select('id', 'full_name', 'email', 'profile_image')
                ->orderBy('full_name')
                ->get();
        } else {
            // Supervisor: Show themselves + their subordinates
            $users = User::select('id', 'full_name', 'email', 'profile_image')
                ->where('id', $currentUser->id)
                ->orWhere('reporting_to', $currentUser->id)
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
            'time_estimate' => 'nullable|string|max:50',
            'priority' => 'required|in:high,medium,low,free',
        ]);

        if (in_array(auth()->user()->role->name ?? '', ['admin', 'supervisor'])) {
            if ($request->has('status')) {
                $request->validate(['status' => 'in:' . implode(',', self::STATUSES)]);
            }
            if ($request->has('stage')) {
                $request->validate(['stage' => 'in:' . implode(',', self::STAGES)]);
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
        $data = $request->except(['end_date_input', 'end_time_input', 'assignees', 'tagged']);
        $data['end_date'] = $endDate;
        // Use description as title if title is not provided
        $data['title'] = $request->input('title', substr($request->description, 0, 255));

        $task = new Task($data);
        if ($request->has('status')) {
            $task->status = $request->status;
        }
        if ($request->has('stage')) {
            $task->stage = $request->stage;
        } else {
            // Set default stage based on end_date
            if ($endDate) {
                $endDateTime = \Carbon\Carbon::parse($endDate);
                if ($endDateTime->isPast()) {
                    $task->stage = 'overdue';
                } else {
                    $task->stage = 'pending';
                }
            } else {
                $task->stage = 'pending';
            }
        }
        $task->created_by = Auth::id();
        $task->save();

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
                $telegram = app(TelegramService::class);
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
                    $lines[] = '<b>Task:</b> ' . e($task->title);

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
                    $lines[] = 'Open the Unitecture app to view full details.';

                    $message = implode("\n", $lines);

                    $telegram->sendMessage($taggedUser->telegram_chat_id, $message);
                }

                // Send a summary notification to the creator (supervisor/admin)
                if ($creatorRecipient) {
                    $lines = [];
                    $lines[] = '🔔 <b>Task created & people tagged</b>';
                    $lines[] = '';
                    $lines[] = '<b>Task:</b> ' . e($task->title);

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
                    $lines[] = 'Open the Unitecture app to view full details.';

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
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::STATUSES),
        ]);

        $oldStatus = $task->status;
        $task->status = $validated['status'];
        $task->save();

        // Notify assignees and tagged users via Telegram about status change
        if ($oldStatus !== $task->status) {
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = app(TelegramService::class);
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
                        $lines[] = '<b>Task:</b> ' . e($task->title);

                        if ($project) {
                            $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                        }

                        $lines[] = '<b>New Status:</b> ' . ucfirst(str_replace('_', ' ', $task->status));

                        if ($actor) {
                            $lines[] = '<b>Updated by:</b> ' . e($actor->name);
                        }

                        $lines[] = '';
                        $lines[] = 'Open the Unitecture app to view full details.';

                        $message = implode("\n", $lines);

                        $telegram->sendMessage($user->telegram_chat_id, $message);
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram task status notifications: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Status updated successfully', 'status' => $task->status]);
    }

    /**
     * Update the task stage.
     */
    public function updateStage(Request $request, Task $task)
    {
        $validated = $request->validate([
            'stage' => 'required|string|in:' . implode(',', self::STAGES),
        ]);

        $oldStage = $task->stage;
        $task->stage = $validated['stage'];
        $task->save();

        // Notify assignees and tagged users via Telegram about stage change
        if ($oldStage !== $task->stage) {
            try {
                /** @var \App\Services\TelegramService $telegram */
                $telegram = app(TelegramService::class);
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
                        $lines[] = '🔔 <b>Task stage updated</b>';
                        $lines[] = '';
                        $lines[] = '<b>Task:</b> ' . e($task->title);

                        if ($project) {
                            $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                        }

                        $lines[] = '<b>New Stage:</b> ' . ucfirst(str_replace('_', ' ', $task->stage));

                        if ($actor) {
                            $lines[] = '<b>Updated by:</b> ' . e($actor->name);
                        }

                        $lines[] = '';
                        $lines[] = 'Open the Unitecture app to view full details.';

                        $message = implode("\n", $lines);

                        $telegram->sendMessage($user->telegram_chat_id, $message);
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send Telegram task stage notifications: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Stage updated successfully', 'stage' => $task->stage]);
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

        $task->end_date = $endDateTime;

        // Recalculate stage based on new end date if not manually overridden
        $endCarbon = \Carbon\Carbon::parse($endDateTime);
        if ($endCarbon->isPast() && $task->stage !== 'completed') {
            $task->stage = 'overdue';
        } elseif (!$endCarbon->isPast() && $task->stage === 'overdue') {
            $task->stage = 'pending';
        }

        $task->save();

        return response()->json([
            'message' => 'Due date updated successfully.',
            'end_date' => $task->end_date->toIso8601String(),
            'stage' => $task->stage,
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
            ->with('user:id,full_name,name')
            ->get()
            ->map(function (TaskComment $comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->toDateTimeString(),
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->full_name ?? $comment->user->name,
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

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $data['comment'],
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toDateTimeString(),
                'created_at_human' => $comment->created_at->diffForHumans(),
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

        $task->loadMissing(['assignees:id', 'taggedUsers:id']);

        if ($task->assignees->contains('id', $user->id)) {
            return true;
        }

        if ($task->taggedUsers->contains('id', $user->id)) {
            return true;
        }

        return false;
    }
}
