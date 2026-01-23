<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of all projects (visible to all supervisors).
     */
    public function index()
    {
        // Only supervisors and admins can view projects
        if (!Auth::user()->isSupervisor() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all projects created by supervisors (or admins)
        $projects = Project::with('creator')
            ->whereHas('creator', function ($query) {
                $query->whereIn('role_id', [1, 2, 3]); // Supervisor, Admin, Super Admin
            })
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // Ensure user is supervisor
        if (!Auth::user()->isSupervisor()) {
            abort(403, 'Unauthorized action.');
        }

        return view('projects.create');
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSupervisor()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'project_custom_id' => 'required|string|max:255',
            'project_code' => 'required|string|max:255|unique:projects',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'required|string',
        ]);

        $project = new Project($validated);
        $project->created_by = Auth::id();
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }
}
