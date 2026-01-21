@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->role->name ?? 'employee'" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <!-- Header -->
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

                    <div class="max-w-4xl mx-auto" 
                         x-data="taskForm(@json($projects))">
                        
                        <form action="{{ route('tasks.store') }}" method="POST"
                            class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-6">
                                
                                <!-- Left Column -->
                                <div class="space-y-6">
                                    <!-- Project Selection -->
                                    <div>
                                        <label for="project_id" class="block text-sm font-bold text-slate-700 mb-2">Project</label>
                                        <select name="project_id" id="project_id" x-model="selectedProjectId"
                                            class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5"
                                            required>
                                            <option value="">Select a Project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}">
                                                    {{ $project->name }} ({{ $project->project_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-indigo-500 mt-1" x-show="selectedProject" x-text="`Project Dates: ${formatDate(selectedProject?.start_date)} - ${formatDate(selectedProject?.end_date)}`"></p>
                                        @error('project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Task Title -->
                                    <div>
                                        <label for="title" class="block text-sm font-bold text-slate-700 mb-2">Task Title</label>
                                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                                            class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5"
                                            placeholder="Enter task title" required>
                                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="description" class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                                        <textarea name="description" id="description" rows="5"
                                            class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            placeholder="Enter task details..."
                                            required>{{ old('description') }}</textarea>
                                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    
                                     <!-- Labels / Tags -->
                                    <div>
                                        <label for="category_tags" class="block text-sm font-bold text-slate-700 mb-2">Tags / Labels</label>
                                        <input type="text" name="category_tags" id="category_tags"
                                            value="{{ old('category_tags') }}"
                                            class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5"
                                            placeholder="e.g. Design, Urgent (comma separated)">
                                        @error('category_tags') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-6">
                                    <!-- Priority (Radio Buttons) -->
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-3">Priority</label>
                                        <div class="flex flex-wrap gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="priority" value="high" x-model="priority" class="w-4 h-4 text-red-600 focus:ring-red-500 border-gray-300">
                                                <span class="text-sm font-bold text-red-600 bg-red-50 px-3 py-1 rounded-full border border-red-100">High</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="priority" value="medium" x-model="priority" class="w-4 h-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                                                <span class="text-sm font-bold text-orange-600 bg-orange-50 px-3 py-1 rounded-full border border-orange-100">Medium</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="priority" value="low" x-model="priority" class="w-4 h-4 text-green-500 focus:ring-green-500 border-gray-300">
                                                <span class="text-sm font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-100">Low</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="priority" value="free" x-model="priority" class="w-4 h-4 text-slate-500 focus:ring-slate-500 border-gray-300">
                                                <span class="text-sm font-bold text-slate-600 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">Free</span>
                                            </label>
                                        </div>
                                        @error('priority') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Dates & Time -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Start Date -->
                                        <div class="col-span-2">
                                            <label for="start_date" class="block text-sm font-bold text-slate-700 mb-2">Start Date</label>
                                            <input type="date" name="start_date" id="start_date"
                                                x-model="startDate"
                                                :min="minDate" :max="maxDate"
                                                class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5"
                                                required>
                                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- End Date -->
                                        <div>
                                            <label for="end_date_input" class="block text-sm font-bold text-slate-700 mb-2">End Date</label>
                                            <input type="date" name="end_date_input" id="end_date_input"
                                                :min="startDate || minDate" :max="maxDate"
                                                :disabled="priority === 'free'"
                                                :class="{'bg-slate-50 text-slate-400': priority === 'free'}"
                                                class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5">
                                            @error('end_date_input') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- End Time -->
                                        <div>
                                            <label for="end_time_input" class="block text-sm font-bold text-slate-700 mb-2">End Time</label>
                                            <input type="time" name="end_time_input" id="end_time_input"
                                                :disabled="priority === 'free'"
                                                :class="{'bg-slate-50 text-slate-400': priority === 'free'}"
                                                class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5">
                                            @error('end_time_input') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <!-- Time Estimate (Visual Only for now as requested or string) -->
                                     <div>
                                        <label for="time_estimate" class="block text-sm font-bold text-slate-700 mb-2">Time Estimate</label>
                                        <input type="text" name="time_estimate" id="time_estimate"
                                            value="{{ old('time_estimate') }}"
                                            class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5"
                                            placeholder="e.g. 4 Hours">
                                    </div>
                                </div>
                                
                                <!-- Full Width Sections -->
                                <div class="md:col-span-2 space-y-6">
                                    <hr class="border-slate-100">
                                    
                                    <!-- Assignees -->
                                    <div>
                                        <label for="assignees" class="block text-sm font-bold text-slate-700 mb-2">Assign To (Employees)</label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-h-48 overflow-y-auto p-1">
                                            @foreach($users as $user)
                                                <label class="flex items-center p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                                    <input type="checkbox" name="assignees[]" value="{{ $user->id }}" 
                                                        {{ (collect(old('assignees'))->contains($user->id)) ? 'checked' : '' }}
                                                        class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <div class="ml-3">
                                                        <p class="text-sm font-bold text-slate-700">{{ $user->name }}</p>
                                                        <p class="text-xs text-slate-400">{{ $user->role->name ?? 'Employee' }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('assignees') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Tagged Users -->
                                    <div>
                                        <label for="tagged" class="block text-sm font-bold text-slate-700 mb-2">Tag/Notify (Optional)</label>
                                        <select name="tagged[]" id="tagged" multiple
                                            class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm h-24">
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ (collect(old('tagged'))->contains($user->id)) ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Hold Ctrl/Cmd to select multiple.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6 border-t border-slate-50">
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('taskForm', (projects) => ({
                projects: projects,
                selectedProjectId: '{{ old('project_id') }}',
                priority: '{{ old('priority', 'medium') }}',
                startDate: '{{ old('start_date') }}',
                
                get selectedProject() {
                    return this.projects.find(p => p.id == this.selectedProjectId);
                },

                get minDate() {
                    return this.selectedProject ? this.selectedProject.start_date.split('T')[0] : null;
                },

                get maxDate() {
                    return this.selectedProject ? (this.selectedProject.end_date ? this.selectedProject.end_date.split('T')[0] : null) : null;
                },
                
                formatDate(dateStr) {
                    if(!dateStr) return 'N/A';
                    return new Date(dateStr).toLocaleDateString();
                }
            }));
        });
    </script>
@endsection