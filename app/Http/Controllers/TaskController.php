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
        'not_assigned',
        'wip',
        'completed',
        'revision',
        'closed',
        'hold',
        'emailed_under_review',
        'awaiting_resources',
        'awaiting_consultancy',
        'shop_drawings',
    ];

    /**
     * Display a listing of the tasks (Overview Dashboard).
     */
    public function index()
    {
        // For now, show all tasks if admin/supervisor, or assigned tasks if employee
        $user = Auth::user();

        if ($user->isEmployee()) {
            $tasks = $user->tasks()->with(['project', 'assignees'])->latest()->get(); // Assuming 'tasks' relationship exists or using query
            // Note: I need to add 'tasks' relationship to User model or query manually. 
            // Let's query manually for safety if User model isn't edited yet.
            $tasks = Task::whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with(['project', 'assignees'])->latest()->get();
        } else {
            $tasks = Task::with(['project', 'assignees', 'creator'])->latest()->get();
        }

        $statuses = self::STATUSES;
        return view('tasks.index', compact('tasks', 'statuses'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $projects = Project::where('status', 'active')->get();
        $users = User::all(); // load all users for assignment

        return view('tasks.create', compact('projects', 'users'));
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
            'title' => 'required|string|max:255',
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
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'time_estimate' => 'nullable|string|max:50',
            'priority' => 'required|in:high,medium,low,free',
            'assignees' => 'required|array', // Array of user IDs
            'assignees.*' => 'exists:users,id',
            'tagged' => 'nullable|array', // Array of user IDs
            'tagged.*' => 'exists:users,id',
            'category_tags' => 'nullable|string',
        ]);

        if (in_array(auth()->user()->role->name ?? '', ['admin', 'supervisor']) && $request->has('status')) {
            $request->validate(['status' => 'in:' . implode(',', self::STATUSES)]);
        }

        $task = new Task($validated);
        if ($request->has('status')) {
            $task->status = $request->status;
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
}
