<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
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
        $task->assignees()->attach($request->assignees, ['type' => 'assignee']);

        // Attach Tagged Users
        if ($request->has('tagged')) {
            $task->taggedUsers()->attach($request->tagged, ['type' => 'tagged']);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        // Check authorization if needed (can user view this task?)
        // For now, assuming if they can list, they can view details.

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

        $task->status = $validated['status'];
        $task->save();

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

        $task->stage = $validated['stage'];
        $task->save();

        return response()->json(['message' => 'Stage updated successfully', 'stage' => $task->stage]);
    }
}
