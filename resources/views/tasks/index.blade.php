@extends('layouts.app')

@section('content')
    <div class="h-screen flex flex-col bg-[#F8F9FB] overflow-hidden" x-data="taskManager(@json($tasks), @json($statuses))">

        <!-- Header & Toolbar -->
        <header
            class="bg-white border-b border-slate-100 py-4 px-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 z-10">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Task Overview</h1>
                <p class="text-slate-400 text-sm font-medium">Manage and track your project tasks</p>
            </div>

            <div class="flex items-center gap-3">
                <!-- View Toggles -->
                <div class="flex bg-slate-100 p-1 rounded-lg">
                    <button @click="view = 'board'" class="px-3 py-1.5 rounded-md text-sm font-bold transition-all"
                        :class="view === 'board' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        Board
                    </button>
                    <button @click="view = 'table'" class="px-3 py-1.5 rounded-md text-sm font-bold transition-all"
                        :class="view === 'table' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        List
                    </button>
                </div>

                @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                    <a href="{{ route('tasks.create') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-indigo-200 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Task
                    </a>
                @endif
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-hidden relative">

            <!-- Board View -->
            <div x-show="view === 'board'" class="h-full overflow-x-auto overflow-y-hidden p-6 whitespace-nowrap"
                style="scrollbar-width: thin;">
                <div class="inline-flex h-full gap-6 items-start pb-4">
                    <template x-for="status in statuses" :key="status">
                        <div class="w-80 flex flex-col h-full bg-slate-100/50 rounded-xl border border-slate-200/60 max-h-full"
                            @dragover.prevent="dragOverStyle = status" @dragleave="dragOverStyle = null"
                            @drop="drop($event, status); dragOverStyle = null"
                            :class="{ 'ring-2 ring-indigo-400 ring-inset bg-indigo-50/50': dragOverStyle === status }">

                            <!-- Column Header -->
                            <div
                                class="p-3 border-b border-slate-200/60 flex items-center justify-between shrink-0 bg-slate-50 rounded-t-xl sticky top-0 z-10">
                                <div class="flex items-center gap-2">
                                    <template x-if="status === 'wip'">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    </template>
                                    <template x-if="status === 'completed'">
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    </template>
                                    <template x-if="status === 'not_assigned'">
                                        <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                    </template>
                                    <template x-if="!['wip','completed','not_assigned'].includes(status)">
                                        <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                    </template>

                                    <span class="text-xs font-bold uppercase text-slate-500"
                                        x-text="formatStatus(status)"></span>
                                    <span class="bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-bold"
                                        x-text="tasksByStatus(status).length"></span>
                                </div>
                            </div>

                            <!-- Cards Container -->
                            <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar" style="min-height: 100px;">
                                <template x-for="task in tasksByStatus(status)" :key="task.id">
                                    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 cursor-grab hover:shadow-md transition-all active:cursor-grabbing group relative"
                                        draggable="true" @dragstart="dragStart($event, task)" @click="openModal(task)">

                                        <!-- Priority Badge -->
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border" :class="{
                                                      'text-red-600 bg-red-50 border-red-100': task.priority === 'high',
                                                      'text-orange-600 bg-orange-50 border-orange-100': task.priority === 'medium',
                                                      'text-green-600 bg-green-50 border-green-100': task.priority === 'low',
                                                      'text-slate-600 bg-slate-50 border-slate-100': task.priority === 'free'
                                                  }" x-text="task.priority"></span>

                                            <!-- Edit Icon (visible on hover) -->
                                            <button
                                                class="text-slate-300 hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <h3 class="text-sm font-bold text-slate-800 leading-tight mb-1" x-text="task.title">
                                        </h3>
                                        <p class="text-xs text-slate-400 font-medium mb-3 truncate"
                                            x-text="task.project?.name || 'No Project'"></p>

                                        <div class="flex items-center justify-between border-t border-slate-50 pt-3 mt-1">
                                            <!-- Assignees -->
                                            <div class="flex -space-x-2 overflow-hidden">
                                                <template x-for="(assignee, index) in task.assignees.slice(0, 3)"
                                                    :key="assignee.id">
                                                    <div class="w-6 h-6 rounded-full border border-white bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-700"
                                                        :title="assignee.name" x-text="assignee.name.charAt(0)"></div>
                                                </template>
                                                <template x-if="task.assignees.length > 3">
                                                    <div class="w-6 h-6 rounded-full border border-white bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500"
                                                        x-text="`+${task.assignees.length - 3}`"></div>
                                                </template>
                                            </div>

                                            <!-- Due Date -->
                                            <div class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                <span x-text="formatDate(task.end_date)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- List View (Table) -->
            <div x-show="view === 'table'" class="h-full overflow-y-auto p-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Task Name</th>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Project</th>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Priority</th>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Status</th>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Assignees</th>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Due Date</th>
                                <th class="px-6 py-4 font-bold text-slate-500 uppercase text-xs">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="task in tasks" :key="task.id">
                                <tr class="hover:bg-slate-50/50 group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700 hover:text-indigo-600 cursor-pointer"
                                            @click="openModal(task)" x-text="task.title"></div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600" x-text="task.project?.name || '-'"></td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded textxs font-bold capitalize"
                                            :class="{
                                                  'text-red-600 bg-red-50': task.priority === 'high',
                                                  'text-orange-600 bg-orange-50': task.priority === 'medium',
                                                  'text-green-600 bg-green-50': task.priority === 'low',
                                                  'text-slate-600 bg-slate-50': task.priority === 'free'
                                              }" x-text="task.priority"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <select @change="updateStatus(task.id, $event.target.value)"
                                            class="bg-transparent text-xs font-bold rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 py-1 pl-2 pr-8"
                                            :class="{
                                                'text-blue-600 bg-blue-50 border-blue-100': task.status === 'wip',
                                                'text-green-600 bg-green-50 border-green-100': task.status === 'completed',
                                                'text-slate-600 bg-slate-50 border-slate-100': !['wip','completed'].includes(task.status)
                                            }">
                                            <template x-for="status in statuses" :key="status">
                                                <option :value="status" :selected="task.status === status"
                                                    x-text="formatStatus(status)"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex -space-x-2">
                                            <template x-for="assignee in task.assignees.slice(0,3)">
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 border border-white flex items-center justify-center text-[10px] font-bold text-indigo-600"
                                                    :title="assignee.name" x-text="assignee.name.charAt(0)"></div>
                                            </template>
                                            <template x-if="task.assignees.length > 3">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 border border-white flex items-center justify-center text-[10px] font-bold text-slate-500"
                                                    x-text="`+${task.assignees.length - 3}`"></div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 font-medium" x-text="formatDate(task.end_date)">
                                    </td>
                                    <td class="px-6 py-4">
                                        <button @click="openModal(task)"
                                            class="text-slate-400 hover:text-indigo-600 font-bold text-xs uppercase">View</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Task Detail Modal -->
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
                                        class="w-full rounded-lg border-slate-200 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50">
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
                                            <div class="flex items-center gap-2 bg-indigo-50 px-2 py-1 rounded-lg">
                                                <div class="w-5 h-5 rounded-full bg-indigo-200 flex items-center justify-center text-[10px] font-bold text-indigo-700"
                                                    x-text="assignee.name.charAt(0)"></div>
                                                <span class="text-xs font-bold text-indigo-800"
                                                    x-text="assignee.name"></span>
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
            Alpine.data('taskManager', (initialTasks, allStatuses) => ({
                tasks: initialTasks,
                statuses: allStatuses,
                view: 'board', // 'board' or 'table'
                selectedTask: null,
                dragOverStyle: null,

                tasksByStatus(status) {
                    return this.tasks.filter(t => t.status === status);
                },

                formatStatus(status) {
                    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                },

                formatDate(dateString, full = false) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return full ? date.toLocaleString() : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                },

                async updateStatus(taskId, newStatus) {
                    const task = this.tasks.find(t => t.id === taskId);
                    if (!task) return;
                    const oldStatus = task.status;
                    task.status = newStatus;

                    // Sync selected task if open
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
                        // Revert on failure
                        task.status = oldStatus;
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask.status = oldStatus;
                        }
                        console.error('Failed to update status');
                    }
                },

                dragStart(event, task) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', task.id);
                    // Optional: set custom drag image
                },

                drop(event, newStatus) {
                    const taskId = parseInt(event.dataTransfer.getData('text/plain'));
                    if (taskId) {
                        this.updateStatus(taskId, newStatus);
                    }
                },

                openModal(task) {
                    this.selectedTask = task;
                }
            }));
        });
    </script>
    <style>
        /* Custom Scrollbar for columns */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
@endsection