<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of all projects (visible to all supervisors).
     */
    public function index(Request $request)
    {
        // Only supervisors and admins can view projects
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all projects created by supervisors (or admins)
        $search = trim((string) $request->query('q', ''));
        $view = $request->query('view', 'active');

        $projectsQuery = Project::with('creator');

        if ($view === 'parked') {
            $projectsQuery->withoutGlobalScope('exclude_parked')->where('is_parked', true);
        } else {
            // Default active view uses the global scope automatically
            $projectsQuery->where('is_parked', false);
        }

        $projectsQuery->whereHas('creator', function ($query) {
            $query->whereIn('role_id', [1, 2, 3]); // Supervisor, Admin, Super Admin
        });

        if ($search !== '') {
            $projectsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('project_code', 'like', "%{$search}%")
                    ->orWhere('project_custom_id', 'like', "%{$search}%");
            });
        }

        $projects = $projectsQuery->latest()->get();

        return view('projects.index', compact('projects', 'search'));
    }

    /**
     * Display the specified project with full details (created by, etc.).
     */
    public function show(Project $project)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $project->load('creator');
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // Ensure user is supervisor
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('projects.create');
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'project_custom_id' => 'required|string|max:255',
            'project_code' => 'required|string|max:255|unique:projects',
            'name' => 'required|string|max:255',
            // Start date can be any valid date (including past)
            'start_date' => 'required|date',
            // End date is optional, but when present must be on/after start date
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'required|string',
        ]);

        $project = new Project($validated);
        $project->created_by = Auth::id();
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    /**
     * Show the form for editing the specified project.
     * Only the supervisor who created the project (or admin) can edit.
     */
    public function edit(Project $project)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        if (Auth::id() !== (int) $project->created_by && !Auth::user()->isAdmin()) {
            abort(403, 'Only the supervisor who created this project can edit it.');
        }

        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage.
     * Only the supervisor who created the project (or admin) can update; changes reflect everywhere.
     */
    public function update(Request $request, Project $project)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        if (Auth::id() !== (int) $project->created_by && !Auth::user()->isAdmin()) {
            abort(403, 'Only the supervisor who created this project can edit it.');
        }

        $validated = $request->validate([
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'project_custom_id' => 'required|string|max:255',
            'project_code' => 'required|string|max:255|unique:projects,project_code,' . $project->id,
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'required|string',
            'status' => 'required|in:active,completed,archived',
        ]);

        $project->fill($validated);
        $project->save();

        // Notify supervisors/admins about the update (Telegram, if mapped)
        try {
            /** @var TelegramService $telegram */
            $telegram = app(TelegramService::class);
            $editor = Auth::user();

            $recipients = User::whereIn('role_id', [1, 2, 3])
                ->whereNotNull('telegram_chat_id')
                ->get();

            if ($recipients->isNotEmpty()) {
                foreach ($recipients as $recipient) {
                    $lines = [];
                    $lines[] = '🔔 <b>Project details updated</b>';
                    $lines[] = '';
                    $lines[] = '<b>Project:</b> ' . e($project->name) . ' (' . e($project->project_code) . ')';
                    $lines[] = '<b>Department:</b> ' . e($project->department);
                    $lines[] = '<b>Location:</b> ' . e($project->location);

                    if ($project->start_date) {
                        $lines[] = '<b>Start:</b> ' . e($project->start_date);
                    }
                    if ($project->end_date) {
                        $lines[] = '<b>End:</b> ' . e($project->end_date);
                    }

                    if ($editor) {
                        $lines[] = '<b>Edited by:</b> ' . e($editor->name);
                    }

                    $lines[] = '';
                    $lines[] = 'Open the Unitecture app to view full project details.';

                    $telegram->sendMessage($recipient->telegram_chat_id, implode("\n", $lines));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send Telegram project update notifications: ' . $e->getMessage());
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully!');
    }
    /**
     * Park the specified project.
     */
    public function park(Project $project)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $project->is_parked = true;
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project parked successfully! It is now hidden from all views.');
    }

    /**
     * Unpark the specified project.
     */
    public function unpark($id)
    {
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // We use withoutGlobalScope because parked projects are hidden by default
        $project = Project::withoutGlobalScope('exclude_parked')->findOrFail($id);
        $project->is_parked = false;
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project unparked successfully!');
    }
}
