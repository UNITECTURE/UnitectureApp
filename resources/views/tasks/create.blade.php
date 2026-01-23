@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

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
                         x-data="taskForm(@json($projects), '{{ now()->format('Y-m-d') }}')">
                        
                        <form action="{{ route('tasks.store') }}" method="POST"
                            class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
                            @csrf

                            <!-- Modal Header -->
                            <div class="px-8 py-6 border-b border-slate-100">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-xl font-bold text-slate-800">Add New Task</h2>
                                    <a href="{{ route('tasks.index') }}"
                                        class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-8 py-6 space-y-6">
                                <!-- Project Selection -->
                                <div>
                                    <label for="project_id" class="block text-sm font-semibold text-slate-700 mb-2">Project</label>
                                    <div class="relative">
                                        <select name="project_id" id="project_id" x-model="selectedProjectId"
                                            class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 text-slate-800 bg-slate-50 transition-all duration-200"
                                            required>
                                            <option value="">Select a Project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}">
                                                    {{ $project->name }} ({{ $project->project_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                                    <textarea name="description" id="description" rows="4"
                                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                        placeholder="Enter task description..."
                                        required>{{ old('description') }}</textarea>
                                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Date Fields -->
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Start Date -->
                                    <div>
                                        <label for="start_date" class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                                        <div class="relative">
                                            <input type="date" name="start_date" id="start_date"
                                                x-model="startDate"
                                                :min="todayDate"
                                                :max="maxDate"
                                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 pl-10 text-slate-800 bg-slate-50 transition-all duration-200"
                                                required>
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- End Date -->
                                    <div>
                                        <label for="end_date_input" class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                                        <div class="relative">
                                            <input type="date" name="end_date_input" id="end_date_input"
                                                x-model="endDate"
                                                :min="startDate || todayDate"
                                                :max="maxDate"
                                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 pl-10 text-slate-800 bg-slate-50 transition-all duration-200">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('end_date_input') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- End Time -->
                                <div>
                                    <label for="end_time_input" class="block text-sm font-semibold text-slate-700 mb-2">End Time</label>
                                    <div class="relative">
                                        <input type="time" name="end_time_input" id="end_time_input"
                                            class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 pl-10 text-slate-800 bg-slate-50 transition-all duration-200">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('end_time_input') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Priority Radio Buttons -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-3">Priority</label>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="radio" name="priority" value="high" x-model="priority" 
                                                class="sr-only peer">
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 rounded-full border-2 transition-all"
                                                    :class="priority === 'high' ? 'bg-red-500 border-red-500' : 'border-slate-300 bg-white group-hover:border-red-300'"></div>
                                                <span class="text-sm font-medium text-slate-700">High</span>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="radio" name="priority" value="medium" x-model="priority"
                                                class="sr-only peer">
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 rounded-full border-2 transition-all"
                                                    :class="priority === 'medium' ? 'bg-blue-500 border-blue-500' : 'border-slate-300 bg-white group-hover:border-blue-300'"></div>
                                                <span class="text-sm font-medium text-slate-700">Moderate</span>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="radio" name="priority" value="low" x-model="priority"
                                                class="sr-only peer">
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 rounded-full border-2 transition-all"
                                                    :class="priority === 'low' ? 'bg-green-500 border-green-500' : 'border-slate-300 bg-white group-hover:border-green-300'"></div>
                                                <span class="text-sm font-medium text-slate-700">Low</span>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="radio" name="priority" value="free" x-model="priority"
                                                class="sr-only peer">
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 rounded-full border-2 transition-all"
                                                    :class="priority === 'free' ? 'bg-slate-500 border-slate-500' : 'border-slate-300 bg-white group-hover:border-slate-400'"></div>
                                                <span class="text-sm font-medium text-slate-700">Free</span>
                                            </div>
                                        </label>
                                    </div>
                                    @error('priority') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Add People Section -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-3">Add People</label>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <!-- Selected Employees (Profile Photos) -->
                                        <template x-for="employee in selectedEmployees" :key="employee.id">
                                            <div class="relative group">
                                                <img :src="getProfileImageUrl(employee)"
                                                    :alt="employee.full_name"
                                                    class="w-10 h-10 rounded-full border-2 border-white shadow-sm object-cover cursor-pointer hover:ring-2 hover:ring-blue-400 transition-all"
                                                    :title="employee.full_name">
                                                <button type="button" @click="removeEmployee(employee.id)"
                                                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                                                    ×
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Plus Button -->
                                        <button type="button" @click="showEmployeeModal = true"
                                            class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600 transition-colors shadow-sm">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Hidden Input for Selected Employees -->
                                    <template x-for="employeeId in selectedEmployeeIds" :key="employeeId">
                                        <input type="hidden" name="assignees[]" :value="employeeId">
                                    </template>

                                    @error('assignees') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Employee Selection Modal -->
                                <div x-show="showEmployeeModal" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                                    style="display: none;"
                                    @click.self="showEmployeeModal = false">
                                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col"
                                        @click.stop>
                                        <!-- Modal Header -->
                                        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                                            <h3 class="text-lg font-bold text-slate-800">Select Employees</h3>
                                            <button type="button" @click="showEmployeeModal = false"
                                                class="text-slate-400 hover:text-slate-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Employee List -->
                                        <div class="flex-1 overflow-y-auto p-4 space-y-2">
                                            <template x-for="employee in availableEmployees" :key="employee.id">
                                                <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors"
                                                    :class="isEmployeeSelected(employee.id) ? 'bg-slate-100 opacity-50' : 'hover:bg-slate-50'">
                                                    <input type="checkbox" 
                                                        :value="employee.id"
                                                        :checked="isEmployeeSelected(employee.id)"
                                                        @change="toggleEmployee(employee)"
                                                        :disabled="isEmployeeSelected(employee.id)"
                                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                                                    <img :src="getProfileImageUrl(employee)"
                                                        :alt="employee.full_name"
                                                        class="w-10 h-10 rounded-full object-cover">
                                                    <span class="text-sm font-medium text-slate-700 flex-1" 
                                                        :class="isEmployeeSelected(employee.id) ? 'text-slate-400' : ''"
                                                        x-text="employee.full_name"></span>
                                                </label>
                                            </template>
                                        </div>

                                        <!-- Modal Footer -->
                                        <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                                            <button type="button" @click="showEmployeeModal = false"
                                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="button" @click="showEmployeeModal = false"
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors">
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comments Section -->
                                <div>
                                    <label for="comments" class="block text-sm font-semibold text-slate-700 mb-2">Comments</label>
                                    <textarea name="comments" id="comments" rows="4"
                                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                        placeholder="Start writing here..."></textarea>
                                    <div class="flex items-center gap-2 mt-2">
                                        <button type="button" class="text-blue-500 hover:text-blue-600 text-sm font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            +
                                        </button>
                                        <button type="button" class="text-blue-500 hover:text-blue-600 text-sm font-medium">
                                            @ Tag
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-8 py-6 border-t border-slate-100 bg-slate-50 flex justify-end">
                                <button type="submit"
                                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all transform hover:-translate-y-0.5">
                                    Done
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
            Alpine.data('taskForm', (projects, todayDate) => ({
                projects: projects,
                selectedProjectId: '{{ old('project_id') }}',
                priority: '{{ old('priority', 'medium') }}',
                startDate: '{{ old('start_date') }}',
                endDate: '{{ old('end_date_input') }}',
                todayDate: todayDate,
                selectedEmployees: [],
                availableEmployees: [],
                showEmployeeModal: false,

                get selectedProject() {
                    return this.projects.find(p => p.id == this.selectedProjectId);
                },

                get maxDate() {
                    if (!this.selectedProject) return null;
                    return this.selectedProject.end_date ? this.selectedProject.end_date.split('T')[0] : null;
                },

                get selectedEmployeeIds() {
                    return this.selectedEmployees.map(e => e.id);
                },

                async init() {
                    // Load employees when modal opens
                    this.$watch('showEmployeeModal', async (value) => {
                        if (value && this.availableEmployees.length === 0) {
                            await this.loadEmployees();
                        }
                    });
                },

                async loadEmployees() {
                    try {
                        const response = await fetch('{{ route('tasks.employees') }}');
                        const data = await response.json();
                        this.availableEmployees = data;
                    } catch (error) {
                        console.error('Failed to load employees:', error);
                    }
                },

                toggleEmployee(employee) {
                    const index = this.selectedEmployees.findIndex(e => e.id === employee.id);
                    if (index > -1) {
                        this.selectedEmployees.splice(index, 1);
                    } else {
                        this.selectedEmployees.push(employee);
                    }
                },

                removeEmployee(employeeId) {
                    this.selectedEmployees = this.selectedEmployees.filter(e => e.id !== employeeId);
                },

                isEmployeeSelected(employeeId) {
                    return this.selectedEmployees.some(e => e.id === employeeId);
                },

                getProfileImageUrl(employee) {
                    // Use profile_image_url if available (from API), otherwise construct it
                    if (employee.profile_image_url) {
                        return employee.profile_image_url;
                    }
                    if (employee.profile_image) {
                        // Check if it's already a full URL
                        if (employee.profile_image.startsWith('http://') || employee.profile_image.startsWith('https://')) {
                            return employee.profile_image;
                        }
                        // Otherwise, it's a relative path
                        return '/storage/' + employee.profile_image;
                    }
                    // Fallback to avatar generator
                    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(employee.full_name) + '&background=6366f1&color=fff&size=128';
                }
            }));
        });
    </script>
@endsection
