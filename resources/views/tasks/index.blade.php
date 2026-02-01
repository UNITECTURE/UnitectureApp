@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="taskManager({{ json_encode($tasks) }}, {{ json_encode($statuses) }}, {{ json_encode($stages) }}, {{ json_encode($counts) }}, {{ Auth::user()->isAdmin() || Auth::user()->isSupervisor() ? 'true' : 'false' }})">
        <!-- Sidebar -->
        @php
            $userRole = 'employee';
            if(auth()->user()->isAdmin()) $userRole = 'admin';
            elseif(auth()->user()->isSupervisor()) $userRole = 'supervisor';
        @endphp
        <x-sidebar :role="$userRole" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <!-- Header & Toolbar -->
            <header class="bg-white border-b border-slate-100 py-5 px-4 sm:px-6 shrink-0 z-10">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Tasks</h1>
                        <p class="text-slate-500 text-sm font-medium">Manage and track your assigned tasks</p>
                    </div>

                    @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                        <a href="{{ route('tasks.create') }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 shadow-md w-fit transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Add Task</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- Filters and Search -->
            <div class="bg-white border-b border-slate-100 px-4 sm:px-6 py-4 sm:py-5 shrink-0">
                <div class="flex items-center gap-4 justify-between w-full">
                    <!-- Filter Buttons -->
                    <div class="flex items-center gap-2 flex-1 overflow-x-auto">
                        <button @click="filterStatus = 'all'"
                            class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all border whitespace-nowrap flex-shrink-0"
                            :class="filterStatus === 'all' ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            All (<span x-text="counts.all"></span>)
                        </button>
                        <button @click="filterStatus = 'pending'"
                            class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all border whitespace-nowrap flex-shrink-0"
                            :class="filterStatus === 'pending' ? 'bg-yellow-500 border-yellow-500 text-white' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            Pending (<span x-text="counts.pending"></span>)
                        </button>
                        <button @click="filterStatus = 'in_progress'"
                            class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all border whitespace-nowrap flex-shrink-0"
                            :class="filterStatus === 'in_progress' ? 'bg-blue-500 border-blue-500 text-white' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            In Progress (<span x-text="counts.in_progress"></span>)
                        </button>
                        <button @click="filterStatus = 'completed'"
                            class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all border whitespace-nowrap flex-shrink-0"
                            :class="filterStatus === 'completed' ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            Completed (<span x-text="counts.completed"></span>)
                        </button>
                        <button @click="filterStatus = 'overdue'"
                            class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all border whitespace-nowrap flex-shrink-0"
                            :class="filterStatus === 'overdue' ? 'bg-red-500 border-red-500 text-white' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            Overdue (<span x-text="counts.overdue"></span>)
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="relative ml-4 flex-shrink-0">
                        <input type="text" x-model="search" placeholder="Search tasks..."
                            class="w-64 pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50">
                <!-- Task Cards Grid -->
                <div x-show="filteredTasks.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="task in filteredTasks" :key="task.id">
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5 hover:shadow-md hover:border-slate-300 transition-all cursor-pointer group"
                            @click="openModal(task)">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3 gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold shrink-0"
                                    :class="{
                                        'bg-red-100 text-red-700': task.priority === 'high',
                                        'bg-orange-100 text-orange-700': task.priority === 'medium',
                                        'bg-green-100 text-green-700': task.priority === 'low',
                                        'bg-slate-100 text-slate-700': task.priority === 'free'
                                    }"
                                    x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'">
                                </span>
                                <span class="text-xs font-medium text-slate-500 truncate" x-text="task.project?.name || 'No Project'"></span>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors"
                                x-text="task.title">
                            </h3>

                            <!-- Description -->
                            <p class="text-xs text-slate-500 mb-3 line-clamp-2" x-text="task.description || ''"></p>

                            <!-- Footer -->
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 gap-2">
                                <div class="flex -space-x-2 shrink-0">
                                    <template x-for="(assignee, index) in task.assignees.slice(0, 3)" :key="assignee.id">
                                        <img :src="getProfileImageUrl(assignee)"
                                            :alt="assignee.full_name || assignee.name"
                                            :title="assignee.full_name || assignee.name"
                                            class="w-7 h-7 rounded-full border-2 border-white object-cover shadow-sm">
                                    </template>
                                    <template x-if="task.assignees.length > 3">
                                        <div class="w-7 h-7 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 shadow-sm"
                                            :title="'+' + (task.assignees.length - 3) + ' more'"
                                            x-text="'+' + (task.assignees.length - 3)">
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs font-medium text-slate-400 min-w-0">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="truncate">Due: <span x-text="formatDate(task.end_date)"></span></span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="mt-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold"
                                    :class="{
                                        'bg-slate-100 text-slate-700': task.stage === 'pending',
                                        'bg-blue-100 text-blue-700': task.stage === 'in_progress',
                                        'bg-green-100 text-green-700': task.stage === 'completed',
                                        'bg-red-100 text-red-700': task.stage === 'overdue'
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
                    class="fixed inset-0 flex items-center justify-center p-2 sm:p-4 bg-slate-900/50 backdrop-blur-sm"
                    style="z-index: 99999; display: none;"
                    x-transition.opacity
                @click.self="selectedTask = null">
                <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-xl max-h-[95vh] sm:max-h-[90vh] overflow-y-auto"
                    @click.stop>
                    <template x-if="selectedTask">
                        <div>
                            <div class="p-4 sm:p-6 border-b border-slate-100 flex justify-between items-start gap-3">
                                <div class="min-w-0 flex-1">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mb-2"
                                        :class="{
                                              'bg-red-50 text-red-600': selectedTask.priority === 'high',
                                              'bg-orange-50 text-orange-600': selectedTask.priority === 'medium',
                                              'bg-green-50 text-green-600': selectedTask.priority === 'low',
                                              'bg-slate-50 text-slate-600': selectedTask.priority === 'free'
                                          }" x-text="selectedTask.priority"></span>
                                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 break-words" x-text="selectedTask.title"></h2>
                                    <p class="text-xs sm:text-sm text-slate-500 font-medium truncate" x-text="selectedTask.project?.name"></p>
                                </div>
                                <button @click="selectedTask = null"
                                    class="text-slate-400 hover:text-slate-600 bg-slate-50 p-2 rounded-full transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                                <!-- Description -->
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</h3>
                                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-wrap break-words"
                                        x-text="selectedTask.description"></div>
                                </div>

                                <!-- Details Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
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
                                    <div class="sm:col-span-2">
                                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Due Date</h3>
                                        <template x-if="!canEditDue">
                                            <p class="text-xs sm:text-sm font-bold text-slate-700"
                                                x-text="formatDate(selectedTask.end_date, true)"></p>
                                        </template>
                                        <template x-if="canEditDue">
                                            <div class="space-y-2">
                                                <p class="text-[11px] text-slate-400">
                                                    Current:
                                                    <span class="font-semibold text-slate-600"
                                                        x-text="formatDate(selectedTask.end_date, true)"></span>
                                                </p>
                                                <div class="flex flex-col sm:flex-row gap-2">
                                                    <input type="date"
                                                        x-model="editEndDate"
                                                        class="flex-1 rounded-lg border-slate-200 text-xs px-2 py-1.5 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <input type="time"
                                                        x-model="editEndTime"
                                                        :disabled="selectedTask.priority === 'free'"
                                                        class="w-full sm:w-24 rounded-lg border-slate-200 text-xs px-2 py-1.5 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-400">
                                                </div>
                                                <button type="button"
                                                    @click="saveDue(selectedTask.id)"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-bold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    :disabled="!editEndDate">
                                                    Update Due
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                <div class="sm:col-span-2">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assignees</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="assignee in selectedTask.assignees" :key="assignee.id">
                                            <div class="flex items-center gap-1.5 sm:gap-2 bg-indigo-50 px-2 py-1 rounded-lg">
                                                <img :src="getProfileImageUrl(assignee)"
                                                    :alt="assignee.full_name || assignee.name"
                                                    class="w-4 h-4 sm:w-5 sm:h-5 rounded-full object-cover">
                                                <span class="text-xs font-bold text-indigo-800 truncate max-w-[120px] sm:max-w-none"
                                                    x-text="assignee.full_name || assignee.name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments -->
                            <div class="px-4 sm:px-6 pb-4 sm:pb-6 space-y-3 border-t border-slate-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Comments</h3>
                                    <button type="button"
                                        class="text-[11px] font-medium text-slate-500 hover:text-slate-700"
                                        @click="loadComments(selectedTask.id)">
                                        Refresh
                                    </button>
                                </div>

                                <div class="max-h-48 overflow-y-auto space-y-3 pr-1">
                                    <template x-if="commentsLoading">
                                        <p class="text-xs text-slate-400 italic">Loading comments...</p>
                                    </template>
                                    <template x-if="!commentsLoading && taskComments.length === 0">
                                        <p class="text-xs text-slate-400 italic">No comments yet. Start the discussion.</p>
                                    </template>
                                    <template x-for="comment in taskComments" :key="comment.id">
                                        <div class="flex items-start gap-2">
                                            <div
                                                class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-700">
                                                <span x-text="(comment.user.name || 'U').slice(0, 2).toUpperCase()"></span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-semibold text-slate-700"
                                                        x-text="comment.user.name"></span>
                                                    <span class="text-[10px] text-slate-400"
                                                        x-text="comment.created_at_human"></span>
                                                </div>
                                                <p class="text-xs text-slate-600 mt-0.5 whitespace-pre-wrap"
                                                    x-text="comment.comment"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <form @submit.prevent="submitComment(selectedTask.id)" class="space-y-2">
                                    <textarea x-model="newComment"
                                        class="w-full text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 px-3 py-2"
                                        rows="2"
                                        placeholder="Add a comment..."></textarea>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="newComment = ''"
                                            class="text-[11px] font-medium text-slate-500 hover:text-slate-700">
                                            Clear
                                        </button>
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-bold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="!newComment.trim()">
                                            Post
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="bg-slate-50 px-4 sm:px-6 py-3 sm:py-4 flex justify-end rounded-b-xl sm:rounded-b-2xl">
                                <button @click="selectedTask = null"
                                    class="px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-all">Done</button>
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
            Alpine.data('taskManager', (initialTasks, allStatuses, allStages, initialCounts, canEditDue) => ({
                tasks: initialTasks,
                statuses: allStatuses,
                stages: allStages,
                counts: initialCounts,
                canEditDue: canEditDue,
                search: '',
                sidebarOpen: true,
                filterStatus: 'all',
                selectedTask: null,
                editEndDate: '',
                editEndTime: '',
                taskComments: [],
                commentsLoading: false,
                newComment: '',
                isPostingComment: false,

                normalizeComments(data) {
                    const list = Array.isArray(data) ? data : Object.values(data);
                    const byId = new Map();
                    for (const c of list) {
                        if (!c || c.id == null) continue;
                        byId.set(c.id, c);
                    }
                    return Array.from(byId.values()).sort((a, b) => (b.id ?? 0) - (a.id ?? 0));
                },

                upsertComment(comment) {
                    if (!comment || comment.id == null) return;
                    const existingIdx = this.taskComments.findIndex(c => c && c.id === comment.id);
                    if (existingIdx !== -1) {
                        this.taskComments.splice(existingIdx, 1, comment);
                        return;
                    }
                    this.taskComments.unshift(comment);
                    this.taskComments = this.normalizeComments(this.taskComments);
                },

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

                async saveDue(taskId) {
                    if (!this.canEditDue || !this.editEndDate) return;

                    const task = this.tasks.find(t => t.id === taskId);
                    if (!task) return;

                    try {
                        const response = await fetch(`/tasks/${taskId}/due`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                end_date_input: this.editEndDate,
                                end_time_input: task.priority === 'free' ? null : this.editEndTime
                            })
                        });
                        if (!response.ok) throw new Error();
                        const data = await response.json();

                        // Update task end_date and stage from response
                        if (data.end_date) {
                            task.end_date = data.end_date;
                            if (this.selectedTask && this.selectedTask.id === taskId) {
                                this.selectedTask.end_date = data.end_date;
                            }
                        }
                        if (data.stage) {
                            task.stage = data.stage;
                            if (this.selectedTask && this.selectedTask.id === taskId) {
                                this.selectedTask.stage = data.stage;
                            }
                        }
                    } catch (e) {
                        console.error('Failed to update due date', e);
                    }
                },

                openModal(task) {
                    this.selectedTask = task;

                    // Initialize editable due date/time from task end_date
                    if (task.end_date) {
                        const d = new Date(task.end_date);
                        if (!isNaN(d.getTime())) {
                            this.editEndDate = d.toISOString().slice(0, 10);
                            this.editEndTime = d.toTimeString().slice(0, 5);
                        } else {
                            this.editEndDate = '';
                            this.editEndTime = '23:59';
                        }
                    } else {
                        this.editEndDate = '';
                        this.editEndTime = '23:59';
                    }

                    this.loadComments(task.id);
                },

                async loadComments(taskId) {
                    this.commentsLoading = true;
                    this.taskComments = [];
                    try {
                        const response = await fetch(`/tasks/${taskId}/comments`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!response.ok) throw new Error();
                        const data = await response.json();
                        this.taskComments = this.normalizeComments(data);
                    } catch (e) {
                        console.error('Failed to load comments', e);
                    } finally {
                        this.commentsLoading = false;
                    }
                },

                async submitComment(taskId) {
                    const text = this.newComment.trim();
                    if (!text || this.isPostingComment) return;
                    this.isPostingComment = true;

                    try {
                        const response = await fetch(`/tasks/${taskId}/comments`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ comment: text })
                        });
                        
                        if (!response.ok) {
                             const errText = await response.text();
                             console.error('Server Error:', errText);
                             throw new Error('Server responded with ' + response.status);
                        }
                        
                        const data = await response.json();
                        if (data.comment) {
                            this.upsertComment(data.comment);
                            this.newComment = '';
                        } else {
                            await this.loadComments(taskId);
                            this.newComment = '';
                        }
                    } catch (e) {
                        console.error('Failed to post comment', e);
                        alert('Failed to post comment. Check console for details.');
                    } finally {
                        this.isPostingComment = false;
                    }
                }
            }));
        });
    </script>
@endsection
