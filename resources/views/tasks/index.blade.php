@extends('layouts.app')

@section('content')
    <div class="h-screen flex flex-col bg-[#F8F9FB] overflow-hidden"
        x-data="taskManager(@json($tasks), @json($statuses), @json($counts))">

        <!-- Header -->
        <header class="bg-white border-b border-slate-100 py-6 px-8 flex items-center justify-between shrink-0 z-20">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Tasks</h1>
                <p class="text-slate-500 text-sm font-medium mt-1">Manage and track your assigned tasks.</p>
            </div>
            @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                <a href="{{ route('tasks.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-lg shadow-blue-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Task
                </a>
            @endif
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">

            <!-- Filter & Search -->
            <div class="flex flex-col md:flex-row gap-4 mb-8">
                <button
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:border-slate-300 hover:shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Filter
                </button>
                <div class="relative flex-1 max-w-md">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" x-model="search" placeholder="Search tasks..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm font-medium placeholder-slate-400">
                </div>
            </div>

            <!-- Stats Overview (Tabs) -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <!-- All -->
                <div @click="setFilter('all')"
                    class="bg-white p-5 rounded-2xl border cursor-pointer transition-all hover:shadow-md"
                    :class="filterStatus === 'all' ? 'border-blue-500 shadow-blue-100 ring-1 ring-blue-500' : 'border-slate-100 hover:border-blue-200'">
                    <div class="text-3xl font-bold text-blue-600 mb-1" x-text="counts.all"></div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">All</div>
                </div>

                <!-- Pending -->
                <div @click="setFilter('pending')"
                    class="bg-white p-5 rounded-2xl border cursor-pointer transition-all hover:shadow-md"
                    :class="filterStatus === 'pending' ? 'border-amber-500 shadow-amber-100 ring-1 ring-amber-500' : 'border-slate-100 hover:border-amber-200'">
                    <div class="text-3xl font-bold text-slate-700 mb-1" x-text="counts.pending"></div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Pending</div>
                </div>

                <!-- In Progress -->
                <div @click="setFilter('in_progress')"
                    class="bg-white p-5 rounded-2xl border cursor-pointer transition-all hover:shadow-md"
                    :class="filterStatus === 'in_progress' ? 'border-blue-500 shadow-blue-100 ring-1 ring-blue-500' : 'border-slate-100 hover:border-blue-200'">
                    <div class="text-3xl font-bold text-blue-600 mb-1" x-text="counts.in_progress"></div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">In Progress</div>
                </div>

                <!-- Completed -->
                <div @click="setFilter('completed')"
                    class="bg-white p-5 rounded-2xl border cursor-pointer transition-all hover:shadow-md"
                    :class="filterStatus === 'completed' ? 'border-green-500 shadow-green-100 ring-1 ring-green-500' : 'border-slate-100 hover:border-green-200'">
                    <div class="text-3xl font-bold text-green-600 mb-1" x-text="counts.completed"></div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Completed</div>
                </div>

                <!-- Overdue -->
                <div @click="setFilter('overdue')"
                    class="bg-white p-5 rounded-2xl border cursor-pointer transition-all hover:shadow-md"
                    :class="filterStatus === 'overdue' ? 'border-red-500 shadow-red-100 ring-1 ring-red-500' : 'border-slate-100 hover:border-red-200'">
                    <div class="text-3xl font-bold text-red-500 mb-1" x-text="counts.overdue"></div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Overdue</div>
                </div>
            </div>

            <!-- Task Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="task in filteredTasks" :key="task.id">
                    <div class="bg-white p-6 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-50 hover:shadow-[0_8px_25px_-4px_rgba(0,0,0,0.1)] transition-all cursor-pointer group relative flex flex-col h-full"
                        @click="openModal(task)">

                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <!-- Priority Badge -->
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border"
                                :class="{
                                          'text-red-600 bg-red-50 border-red-100': task.priority === 'high',
                                          'text-orange-600 bg-orange-50 border-orange-100': task.priority === 'medium',
                                          'text-green-600 bg-green-50 border-green-100': task.priority === 'low',
                                          'text-slate-600 bg-slate-50 border-slate-100': task.priority === 'free'
                                      }" x-text="task.priority"></span>

                            <span class="text-xs font-bold text-slate-400"
                                x-text="task.project?.name || 'No Project'"></span>
                        </div>

                        <!-- Content -->
                        <h3 class="text-lg font-bold text-slate-800 mb-2 leading-snug group-hover:text-blue-600 transition-colors"
                            x-text="task.title"></h3>
                        <p class="text-sm text-slate-500 font-medium mb-6 line-clamp-2 flex-1" x-text="task.description">
                        </p>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-50 mt-auto">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Due
                                    Date</span>
                                <span class="text-xs font-bold text-slate-600" x-text="formatDate(task.end_date)"></span>
                            </div>

                            <!-- Detailed Status Badge -->
                            <span class="text-xs font-bold capitalize px-2 py-1 rounded-lg" :class="{
                                          'text-blue-600 bg-blue-50': ['wip', 'revision', 'shop_drawings'].includes(task.status),
                                          'text-green-600 bg-green-50': ['completed', 'closed'].includes(task.status),
                                          'text-amber-600 bg-amber-50': ['emailed_under_review', 'awaiting_resources', 'awaiting_consultancy'].includes(task.status),
                                          'text-slate-500 bg-slate-50': ['not_assigned', 'hold'].includes(task.status),
                                          'text-red-600 bg-red-50': isOverdue(task)
                                       }" x-text="formatStatus(task.status)"></span>
                        </div>

                        <!-- Assignee Avatars (Absolute on top right or overlapping?) - Design shows Clean cards. Let's add assignees if needed or keep clean. -->
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredTasks.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                </div>
                <p class="text-slate-500 font-medium">No tasks found matching your filter.</p>
            </div>
        </main>

        <!-- Task Detail Modal (Reused) -->
        <div x-show="selectedTask"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            x-transition.opacity style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                @click.outside="selectedTask = null">
                <template x-if="selectedTask">
                    <div>
                        <div class="p-6 border-b border-slate-100 flex justify-between items-start">
                            <div>
                                <span
                                    class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mb-2"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
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
                                        class="w-full rounded-lg border-slate-200 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                                        <template x-for="status in statuses" :key="status">
                                            <option :value="status" :selected="selectedTask.status === status"
                                                x-text="formatStatus(status)"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Due Date</h3>
                                    <p class="text-sm font-bold text-slate-700"
                                        x-text="formatDate(selectedTask.end_date, true)"></p>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assignees
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="assignee in selectedTask.assignees" :key="assignee.id">
                                            <div class="flex items-center gap-2 bg-blue-50 px-2 py-1 rounded-lg">
                                                <div class="w-5 h-5 rounded-full bg-blue-200 flex items-center justify-center text-[10px] font-bold text-blue-700"
                                                    x-text="assignee.name.charAt(0)"></div>
                                                <span class="text-xs font-bold text-blue-800" x-text="assignee.name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <!-- Tagged Users -->
                                <div x-show="selectedTask.tagged_users && selectedTask.tagged_users.length > 0">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tagged
                                        (Notify)</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="user in selectedTask.tagged_users" :key="user.id">
                                            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded"
                                                x-text="user.name"></span>
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
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('taskManager', (initialTasks, allStatuses, initialCounts) => ({
                tasks: initialTasks,
                statuses: allStatuses,
                counts: initialCounts,
                search: '',
                filterStatus: 'all', // 'all', 'pending', 'in_progress', 'completed', 'overdue'
                selectedTask: null,

                get filteredTasks() {
                    let filtered = this.tasks;

                    // Filter by Search
                    if (this.search) {
                        const q = this.search.toLowerCase();
                        filtered = filtered.filter(t =>
                            t.title.toLowerCase().includes(q) ||
                            (t.description && t.description.toLowerCase().includes(q)) ||
                            (t.project && t.project.name.toLowerCase().includes(q))
                        );
                    }

                    // Filter by Status/Tabs
                    if (this.filterStatus === 'pending') {
                        filtered = filtered.filter(t => ['not_assigned', 'hold'].includes(t.status));
                    } else if (this.filterStatus === 'in_progress') {
                        filtered = filtered.filter(t => ['wip', 'revision', 'emailed_under_review', 'awaiting_resources', 'awaiting_consultancy', 'shop_drawings'].includes(t.status));
                    } else if (this.filterStatus === 'completed') {
                        filtered = filtered.filter(t => ['completed', 'closed'].includes(t.status));
                    } else if (this.filterStatus === 'overdue') {
                        filtered = filtered.filter(t => this.isOverdue(t));
                    }

                    return filtered;
                },

                setFilter(status) {
                    this.filterStatus = status;
                },

                isOverdue(task) {
                    if (!task.end_date) return false;
                    const end = new Date(task.end_date);
                    const now = new Date();
                    return end < now && !['completed', 'closed'].includes(task.status);
                },

                formatStatus(status) {
                    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                },

                formatDate(dateString, full = false) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return full ? date.toLocaleString() : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
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

                        // Note: To really update the 'counts' accurately we'd need to re-fetch or adjust locally.
                        // For this iteration, we accept counts might be slightly stale until refresh.
                    } catch (e) {
                        task.status = oldStatus;
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask.status = oldStatus;
                        }
                        console.error('Failed to update status');
                    }
                },

                openModal(task) {
                    this.selectedTask = task;
                }
            }));
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endsection