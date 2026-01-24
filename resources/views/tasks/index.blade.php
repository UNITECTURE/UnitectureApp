@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="taskManager({{ json_encode($tasks) }}, {{ json_encode($statuses) }}, {{ json_encode($stages) }}, {{ json_encode($counts) }})">
        <!-- Sidebar -->
        @php
            $userRole = 'employee';
            if(auth()->user()->isAdmin()) $userRole = 'admin';
            elseif(auth()->user()->isSupervisor()) $userRole = 'supervisor';
        @endphp
        <x-sidebar :role="$userRole" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header & Toolbar -->
            <header class="bg-white border-b border-slate-100 py-4 px-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 z-10">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Tasks</h1>
                    <p class="text-slate-400 text-sm font-medium">Manage and track your assigned tasks</p>
                </div>

                <div class="flex items-center gap-3">
                    @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                        <a href="{{ route('tasks.create') }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-indigo-200 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Task
                        </a>
                    @endif
                </div>
            </header>

            <!-- Filters and Search -->
            <div class="bg-white border-b border-slate-100 px-6 py-4 flex flex-wrap items-center gap-4 shrink-0">
                <button @click="filterStatus = 'all'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all border-2"
                    :class="filterStatus === 'all' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'">
                    All (<span x-text="counts.all"></span>)
                </button>
                <button @click="filterStatus = 'pending'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all border-2"
                    :class="filterStatus === 'pending' ? 'bg-slate-50 border-slate-400 text-slate-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'">
                    Pending (<span x-text="counts.pending"></span>)
                </button>
                <button @click="filterStatus = 'in_progress'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all border-2"
                    :class="filterStatus === 'in_progress' ? 'bg-purple-50 border-purple-400 text-purple-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'">
                    In Progress (<span x-text="counts.in_progress"></span>)
                </button>
                <button @click="filterStatus = 'completed'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all border-2"
                    :class="filterStatus === 'completed' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'">
                    Completed (<span x-text="counts.completed"></span>)
                </button>
                <button @click="filterStatus = 'overdue'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all border-2"
                    :class="filterStatus === 'overdue' ? 'bg-red-50 border-red-400 text-red-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'">
                    Overdue (<span x-text="counts.overdue"></span>)
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </button>
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="Search tasks..."
                            class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Task Cards Grid -->
                <div x-show="filteredTasks.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="task in filteredTasks" :key="task.id">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-all cursor-pointer group"
                            @click="openModal(task)">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                                    :class="{
                                        'bg-red-100 text-red-700': task.priority === 'high',
                                        'bg-orange-100 text-orange-700': task.priority === 'medium',
                                        'bg-green-100 text-green-700': task.priority === 'low',
                                        'bg-slate-100 text-slate-700': task.priority === 'free'
                                    }"
                                    x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'">
                                </span>
                                <span class="text-xs font-medium text-slate-500" x-text="task.project?.name || 'No Project'"></span>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors"
                                x-text="task.title">
                            </h3>

                            <!-- Description -->
                            <p class="text-sm text-slate-500 mb-4 line-clamp-2" x-text="task.description || ''"></p>

                            <!-- Footer -->
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                <div class="flex -space-x-2">
                                    <template x-for="(assignee, index) in task.assignees.slice(0, 3)" :key="assignee.id">
                                        <img :src="getProfileImageUrl(assignee)"
                                            :alt="assignee.full_name || assignee.name"
                                            :title="assignee.full_name || assignee.name"
                                            class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm">
                                    </template>
                                    <template x-if="task.assignees.length > 3">
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 shadow-sm"
                                            :title="'+' + (task.assignees.length - 3) + ' more'"
                                            x-text="'+' + (task.assignees.length - 3)">
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-1 text-xs font-medium text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Due: <span x-text="formatDate(task.end_date)"></span></span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="mt-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                    :class="{
                                        'bg-slate-100 text-slate-600': task.stage === 'pending',
                                        'bg-blue-100 text-blue-600': task.stage === 'in_progress',
                                        'bg-green-100 text-green-600': task.stage === 'completed',
                                        'bg-red-100 text-red-600': task.stage === 'overdue'
                                    }"
                                    x-text="formatStage(task.stage)">
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="filteredTasks.length === 0" class="flex flex-col items-center justify-center py-20">
                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-slate-500 font-medium">No tasks found</p>
                </div>
            </main>

            <!-- Task Detail Modal -->
            <template x-teleport="body">
                <div x-show="selectedTask"
                    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                    x-transition.opacity style="display: none;"
                @click.self="selectedTask = null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                    @click.stop>
                    <template x-if="selectedTask">
                        <div>
                            <div class="p-6 border-b border-slate-100 flex justify-between items-start">
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mb-2"
                                        :class="{
                                              'bg-red-50 text-red-600': selectedTask.priority === 'high',
                                              'bg-orange-50 text-orange-600': selectedTask.priority === 'medium',
                                              'bg-green-50 text-green-600': selectedTask.priority === 'low',
                                              'bg-slate-50 text-slate-600': selectedTask.priority === 'free'
                                          }" x-text="selectedTask.priority"></span>
                                    <h2 class="text-xl font-bold text-slate-900" x-text="selectedTask.title"></h2>
                                    <p class="text-sm text-slate-500 font-medium" x-text="selectedTask.project?.name"></p>
                                </div>
                                <button @click="selectedTask = null"
                                    class="text-slate-400 hover:text-slate-600 bg-slate-50 p-2 rounded-full transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="p-6 space-y-6">
                                <!-- Description -->
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</h3>
                                    <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap"
                                        x-text="selectedTask.description"></div>
                                </div>

                                <!-- Details Grid -->
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</h3>
                                        <select @change="updateStatus(selectedTask.id, $event.target.value)"
                                            class="w-full rounded-lg border-slate-200 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50">
                                            <template x-for="status in statuses" :key="status">
                                                <option :value="status" :selected="selectedTask.status === status"
                                                    x-text="formatStatus(status)"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stage</h3>
                                        <select @change="updateStage(selectedTask.id, $event.target.value)"
                                            class="w-full rounded-lg border-slate-200 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50">
                                            <template x-for="stage in stages" :key="stage">
                                                <option :value="stage" :selected="selectedTask.stage === stage"
                                                    x-text="formatStage(stage)"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Due Date</h3>
                                        <p class="text-sm font-bold text-slate-700"
                                            x-text="formatDate(selectedTask.end_date, true)"></p>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assignees</h3>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="assignee in selectedTask.assignees" :key="assignee.id">
                                                <div class="flex items-center gap-2 bg-indigo-50 px-2 py-1 rounded-lg">
                                                    <img :src="getProfileImageUrl(assignee)"
                                                        :alt="assignee.full_name || assignee.name"
                                                        class="w-5 h-5 rounded-full object-cover">
                                                    <span class="text-xs font-bold text-indigo-800"
                                                        x-text="assignee.full_name || assignee.name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 px-6 py-4 flex justify-end rounded-b-2xl">
                                <button @click="selectedTask = null"
                                    class="text-slate-600 font-bold text-sm hover:underline">Close</button>
                            </div>
                        </div>
                    </template>
                    </div>
            </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('taskManager', (initialTasks, allStatuses, allStages, initialCounts) => ({
                tasks: initialTasks,
                statuses: allStatuses,
                stages: allStages,
                counts: initialCounts,
                search: '',
                sidebarOpen: true,
                filterStatus: 'all',
                selectedTask: null,

                get filteredTasks() {
                    let filtered = this.tasks;

                    // Filter by Search
                    if (this.search) {
                        const q = this.search.toLowerCase();
                        filtered = filtered.filter(t =>
                            (t.title && t.title.toLowerCase().includes(q)) ||
                            (t.description && t.description.toLowerCase().includes(q)) ||
                            (t.project && t.project.name && t.project.name.toLowerCase().includes(q))
                        );
                    }

                    // Filter by Stage
                    if (this.filterStatus === 'pending') {
                        filtered = filtered.filter(t => t.stage === 'pending');
                    } else if (this.filterStatus === 'in_progress') {
                        filtered = filtered.filter(t => t.stage === 'in_progress');
                    } else if (this.filterStatus === 'completed') {
                        filtered = filtered.filter(t => t.stage === 'completed');
                    } else if (this.filterStatus === 'overdue') {
                        filtered = filtered.filter(t => t.stage === 'overdue');
                    }

                    return filtered;
                },

                formatStatus(status) {
                    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                },

                formatStage(stage) {
                    return stage ? stage.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') : 'Pending';
                },

                formatDate(dateString, full = false) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return full ? date.toLocaleString() : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                },

                getProfileImageUrl(assignee) {
                    if (assignee.profile_image_url) {
                        return assignee.profile_image_url;
                    }
                    if (assignee.profile_image) {
                        if (assignee.profile_image.startsWith('http://') || assignee.profile_image.startsWith('https://')) {
                            return assignee.profile_image;
                        }
                        return '/storage/' + assignee.profile_image;
                    }
                    const name = assignee.full_name || assignee.name || 'User';
                    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=6366f1&color=fff&size=128';
                },

                async updateStatus(taskId, newStatus) {
                    const task = this.tasks.find(t => t.id === taskId);
                    if (!task) return;
                    const oldStatus = task.status;
                    task.status = newStatus;

                    if (this.selectedTask && this.selectedTask.id === taskId) {
                        this.selectedTask.status = newStatus;
                    }

                    try {
                        const response = await fetch(`/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ status: newStatus })
                        });

                        if (!response.ok) throw new Error();
                    } catch (e) {
                        task.status = oldStatus;
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask.status = oldStatus;
                        }
                        console.error('Failed to update status');
                    }
                },

                async updateStage(taskId, newStage) {
                    const task = this.tasks.find(t => t.id === taskId);
                    if (!task) return;
                    const oldStage = task.stage;
                    task.stage = newStage;

                    if (this.selectedTask && this.selectedTask.id === taskId) {
                        this.selectedTask.stage = newStage;
                    }

                    try {
                        const response = await fetch(`/tasks/${taskId}/stage`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ stage: newStage })
                        });

                        if (!response.ok) throw new Error();
                    } catch (e) {
                        task.stage = oldStage;
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask.stage = oldStage;
                        }
                        console.error('Failed to update stage');
                    }
                },

                openModal(task) {
                    this.selectedTask = task;
                }
            }));
        });
    </script>
@endsection
