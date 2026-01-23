<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
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

        return redirect()->route('dashboard')->with('success', 'Project created successfully!');
    }
}
