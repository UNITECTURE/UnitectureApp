@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Edit Project</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">Update project details</p>
                        </div>
                        <a href="{{ route('projects.index') }}"
                            class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Projects
                        </a>
                    </div>

                    <div class="max-w-3xl mx-auto">
                        <form action="{{ route('projects.update', $project) }}" method="POST"
                            class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Dept -->
                                <div>
                                    <label for="department"
                                        class="block text-sm font-bold text-slate-700 mb-2">Department</label>
                                    <input type="text" name="department" id="department"
                                        value="{{ old('department', $project->department) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. Architecture" required>
                                    @error('department') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Location -->
                                <div>
                                    <label for="location"
                                        class="block text-sm font-bold text-slate-700 mb-2">Location</label>
                                    <input type="text" name="location" id="location"
                                        value="{{ old('location', $project->location) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. New York Office" required>
                                    @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Project ID -->
                                <div>
                                    <label for="project_custom_id"
                                        class="block text-sm font-bold text-slate-700 mb-2">Project ID</label>
                                    <input type="text" name="project_custom_id" id="project_custom_id"
                                        value="{{ old('project_custom_id', $project->project_custom_id) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. PID-001" required>
                                    @error('project_custom_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Project Code -->
                                <div>
                                    <label for="project_code" class="block text-sm font-bold text-slate-700 mb-2">
                                        Project Code
                                    </label>
                                    <input type="text" name="project_code" id="project_code"
                                        value="{{ old('project_code', $project->project_code) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. PRJ-2024-A" required>
                                    @error('project_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Project Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">
                                        Project Name
                                    </label>
                                    <input type="text" name="name" id="name"
                                        value="{{ old('name', $project->name) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="Enter full project name" required>
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-bold text-slate-700 mb-2">
                                        Start Date
                                    </label>
                                    <input type="date" name="start_date" id="start_date"
                                        value="{{ old('start_date', optional($project->start_date)->toDateString()) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        required>
                                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="block text-sm font-bold text-slate-700 mb-2">
                                        End Date (Optional)
                                    </label>
                                    <input type="date" name="end_date" id="end_date"
                                        value="{{ old('end_date', optional($project->end_date)->toDateString()) }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description"
                                        class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                                    <textarea name="description" id="description" rows="4"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="Detailed description of the project..."
                                        required>{{ old('description', $project->description) }}</textarea>
                                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-slate-50">
                                <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5">
                                    Update Project
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection

