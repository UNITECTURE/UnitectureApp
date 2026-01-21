@extends('layouts.app')

@section('content')
    <div class="h-screen flex flex-col bg-[#F8F9FB] overflow-hidden" x-data="assignedTasks(@json($tasks), @json($statuses))">
        
        <!-- Header -->
        <header class="bg-white border-b border-slate-100 py-4 px-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 z-10">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">My Tasks</h1>
                <p class="text-slate-400 text-sm font-medium">Track, prioritize, and complete your assigned tasks</p>
            </div>

            <div class="flex items-center gap-3">
                <!-- View Toggle -->
                <div class="flex bg-slate-100 p-1 rounded-lg">
                    <button @click="view = 'vertical'" class="px-3 py-1.5 rounded-md text-sm font-bold transition-all"
                        :class="view === 'vertical' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        Vertical
                    </button>
                    <button @click="view = 'horizontal'" class="px-3 py-1.5 rounded-md text-sm font-bold transition-all"
                        :class="view === 'horizontal' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Horizontal
                    </button>
                </div>

                <a href="{{ route('tasks.index') }}"
                    class="text-slate-600 hover:text-slate-800 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition-all text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Overview
                </a>
            </div>
        </header>

        <!-- Filters -->
        <div class="bg-white border-b border-slate-100 px-6 py-4 shrink-0">
            <div class="flex flex-wrap gap-2">
                <button @click="selectedStatus = null"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all"
                    :class="selectedStatus === null ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                    All Tasks ({{ tasks.length }})
                </button>

                <button @click="selectedStatus = 'not_assigned'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-2"
                    :class="selectedStatus === 'not_assigned' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Overdue
                </button>

                <button @click="selectedStatus = 'wip'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-2"
                    :class="selectedStatus === 'wip' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    In Progress
                </button>

                <button @click="selectedStatus = 'completed'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-2"
                    :class="selectedStatus === 'completed' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    Completed
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <main class="flex-1 overflow-auto">
            <!-- Vertical Card View -->
            <div x-show="view === 'vertical'" class="p-6">
                <div x-show="filteredTasks().length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-slate-500 font-medium">No tasks found</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <template x-for="task in filteredTasks()" :key="task.id">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border-l-4 cursor-pointer group"
                            :class="getStatusColor(task.status)"
                            @click="selectTask(task)">
                            
                            <!-- Task Header -->
                            <div class="p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <span x-text="task.code ?? 'N/A'"></span>
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
                                        :class="getStatusBadgeColor(task.status)"
                                        x-text="formatStatus(task.status)">
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3 class="font-bold text-slate-900 text-sm mb-3 line-clamp-2 group-hover:text-indigo-600 transition-colors"
                                    x-text="task.title">
                                </h3>

                                <!-- Due Date -->
                                <div class="flex items-center gap-2 mb-3 text-xs">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span x-text="formatDate(task.end_date)" class="text-slate-600"></span>
                                </div>

                                <!-- Priority Badge -->
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
                                        :class="getPriorityColor(task.priority)"
                                        x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'">
                                    </span>
                                </div>

                                <!-- Assignees -->
                                <div class="flex -space-x-2">
                                    <template x-for="assignee in (task.assignees || []).slice(0, 3)" :key="assignee.id">
                                        <img :src="'https://ui-avatars.com/api/?name=' + assignee.full_name"
                                            :title="assignee.full_name"
                                            class="w-6 h-6 rounded-full border-2 border-white">
                                    </template>
                                    <template x-if="(task.assignees || []).length > 3">
                                        <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-300 flex items-center justify-center text-xs font-bold text-slate-600"
                                            :title="'+' + ((task.assignees || []).length - 3)"
                                            x-text="'+' + ((task.assignees || []).length - 3)">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Horizontal Table View -->
            <div x-show="view === 'horizontal'" class="p-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-6 py-3 text-left font-bold text-slate-700">Task Code</th>
                                    <th class="px-6 py-3 text-left font-bold text-slate-700">Title</th>
                                    <th class="px-6 py-3 text-left font-bold text-slate-700">Status</th>
                                    <th class="px-6 py-3 text-left font-bold text-slate-700">Priority</th>
                                    <th class="px-6 py-3 text-left font-bold text-slate-700">Due Date</th>
                                    <th class="px-6 py-3 text-left font-bold text-slate-700">Assignees</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="task in filteredTasks()" :key="task.id">
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer"
                                        @click="selectTask(task)">
                                        <td class="px-6 py-3 font-bold text-slate-900" x-text="task.code ?? 'N/A'"></td>
                                        <td class="px-6 py-3 text-slate-900 font-medium" x-text="task.title"></td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
                                                :class="getStatusBadgeColor(task.status)"
                                                x-text="formatStatus(task.status)">
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
                                                :class="getPriorityColor(task.priority)"
                                                x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'">
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-600" x-text="formatDate(task.end_date)"></td>
                                        <td class="px-6 py-3">
                                            <div class="flex -space-x-2">
                                                <template x-for="assignee in (task.assignees || []).slice(0, 3)" :key="assignee.id">
                                                    <img :src="'https://ui-avatars.com/api/?name=' + assignee.full_name"
                                                        :title="assignee.full_name"
                                                        class="w-6 h-6 rounded-full border-2 border-white">
                                                </template>
                                                <template x-if="(task.assignees || []).length > 3">
                                                    <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-300 flex items-center justify-center text-xs font-bold text-slate-600"
                                                        :title="'+' + ((task.assignees || []).length - 3)"
                                                        x-text="'+' + ((task.assignees || []).length - 3)">
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="filteredTasks().length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-slate-500 font-medium">No tasks found</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('assignedTasks', (initialTasks, statuses) => ({
                tasks: initialTasks,
                statuses: statuses,
                view: 'vertical',
                selectedStatus: null,

                filteredTasks() {
                    return this.selectedStatus 
                        ? this.tasks.filter(t => t.status === this.selectedStatus)
                        : this.tasks;
                },

                selectTask(task) {
                    window.location.href = `/tasks/${task.id}`;
                },

                formatStatus(status) {
                    return status.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                },

                getStatusColor(status) {
                    const colors = {
                        'not_assigned': 'border-red-500 bg-red-50',
                        'wip': 'border-yellow-500 bg-yellow-50',
                        'completed': 'border-green-500 bg-green-50',
                        'revision': 'border-orange-500 bg-orange-50',
                        'closed': 'border-slate-500 bg-slate-50',
                        'hold': 'border-purple-500 bg-purple-50',
                    };
                    return colors[status] || 'border-slate-300 bg-white';
                },

                getStatusBadgeColor(status) {
                    const colors = {
                        'not_assigned': 'bg-red-100 text-red-700',
                        'wip': 'bg-yellow-100 text-yellow-700',
                        'completed': 'bg-green-100 text-green-700',
                        'revision': 'bg-orange-100 text-orange-700',
                        'closed': 'bg-slate-100 text-slate-700',
                        'hold': 'bg-purple-100 text-purple-700',
                    };
                    return colors[status] || 'bg-slate-100 text-slate-700';
                },

                getPriorityColor(priority) {
                    const colors = {
                        'high': 'bg-red-100 text-red-700',
                        'medium': 'bg-yellow-100 text-yellow-700',
                        'low': 'bg-green-100 text-green-700',
                    };
                    return colors[priority] || 'bg-slate-100 text-slate-700';
                },

                formatDate(date) {
                    if (!date) return 'N/A';
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }
            }));
        });
    </script>
@endsection
