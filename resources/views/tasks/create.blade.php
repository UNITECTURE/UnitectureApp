@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->role->name ?? 'employee'" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Create Task</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">Assign a new task to team members</p>
                        </div>
                        <a href="{{ route('tasks.index') }}"
                            class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Tasks
                        </a>
                    </div>

                    <div class="max-w-3xl mx-auto">
                        <form action="{{ route('tasks.store') }}" method="POST"
                            class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" x-data="{ priority: '{{ old('priority', 'medium') }}' }">
                                <!-- Project Selection -->
                                <div class="md:col-span-2">
                                    <label for="project_id"
                                        class="block text-sm font-bold text-slate-700 mb-2">Project</label>
                                    <select name="project_id" id="project_id"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        required>
                                        <option value="">Select a Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }} ({{ $project->project_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Task Title -->
                                <div class="md:col-span-2">
                                    <label for="title" class="block text-sm font-bold text-slate-700 mb-2">Task Title /
                                        Description Header</label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. Design Homepage Draft" required>
                                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-bold text-slate-700 mb-2">Detailed
                                        Description</label>
                                    <textarea name="description" id="description" rows="4"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="Enter full task details..."
                                        required>{{ old('description') }}</textarea>
                                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-bold text-slate-700 mb-2">Start
                                        Date</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        required>
                                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="block text-sm font-bold text-slate-700 mb-2">End Date & Time</label>
                                    <input type="datetime-local" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                        :disabled="priority === 'free'"
                                        :required="priority !== 'free'">
                                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Time Estimate -->
                                <div>
                                    <label for="time_estimate" class="block text-sm font-bold text-slate-700 mb-2">Time
                                        Estimate</label>
                                    <input type="text" name="time_estimate" id="time_estimate"
                                        value="{{ old('time_estimate') }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. 4 hrs, 2 days">
                                    @error('time_estimate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label for="priority"
                                        class="block text-sm font-bold text-slate-700 mb-2">Priority</label>
                                    <select name="priority" id="priority" x-model="priority"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="high" class="text-red-600 font-bold">High</option>
                                        <option value="medium" class="text-orange-600 font-bold">Medium</option>
                                        <option value="low" class="text-green-600 font-bold">Low</option>
                                        <option value="free" class="text-slate-600 font-bold">Free</option>
                                    </select>
                                    @error('priority') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Assign Employees (Multi-select) -->
                                <div class="md:col-span-2">
                                    <label for="assignees" class="block text-sm font-bold text-slate-700 mb-2">Add Employees
                                        (Assign)</label>
                                    <select name="assignees[]" id="assignees" multiple
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm h-32"
                                        required>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ (collect(old('assignees'))->contains($user->id)) ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->role->name ?? 'Employee' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-400 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select
                                        multiple.</p>
                                    @error('assignees') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Tag Employees (Multi-select) -->
                                <div class="md:col-span-2">
                                    <label for="tagged" class="block text-sm font-bold text-slate-700 mb-2">Tag Employees
                                        (Notify)</label>
                                    <select name="tagged[]" id="tagged" multiple
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm h-32">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ (collect(old('tagged'))->contains($user->id)) ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-400 mt-1">Optional: Select users to just tag/notify without
                                        full assignment.</p>
                                    @error('tagged') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Category / Text Tags -->
                                <div class="md:col-span-2">
                                    <label for="category_tags" class="block text-sm font-bold text-slate-700 mb-2">Tags /
                                        Labels</label>
                                    <input type="text" name="category_tags" id="category_tags"
                                        value="{{ old('category_tags') }}"
                                        class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="e.g. Frontend, Urgent, Bugfix (Comma separated)">
                                    @error('category_tags') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-slate-50">
                                <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5">
                                    Create Task
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection