@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="taskManager({{ json_encode($tasks) }}, {{ json_encode($statuses) }}, {{ json_encode($stages) }}, {{ json_encode($counts) }}, {{ Auth::user()->isAdmin() || Auth::user()->isSupervisor() ? 'true' : 'false' }}, {{ json_encode($employees ?? []) }}, {{ Auth::user()->isAdmin() || Auth::user()->isSupervisor() ? 'true' : 'false' }}, {{ Auth::id() }})">
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
            <header class="bg-white border-b border-slate-100 py-6 px-4 sm:px-6 shrink-0 z-10">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Tasks</h1>
                            <p class="text-slate-500 text-sm font-medium">Manage and track your assigned tasks</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- View Toggle: Overview, Vertical (Kanban), Horizontal (List) -->
                            <div class="flex bg-white border border-slate-200 shadow-sm p-0.5 rounded-lg gap-0.5">
                                <button type="button" @click="view = 'overview'"
                                    class="p-2 rounded-md transition-all duration-200"
                                    :class="view === 'overview' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                                    title="Overview">
                                    <img src="{{ asset('images/overview.svg') }}" class="w-5 h-5" alt="Overview" />
                                </button>
                                <button type="button" @click="view = 'vertical'"
                                    class="p-2 rounded-md transition-all duration-200"
                                    :class="view === 'vertical' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                                    title="Kanban">
                                    <img src="{{ asset('images/kanban.svg') }}" class="w-5 h-5" alt="Kanban" />
                                </button>
                                <button type="button" @click="view = 'horizontal'"
                                    class="p-2 rounded-md transition-all duration-200"
                                    :class="view === 'horizontal' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                                    title="List">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
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
                    </div>
                </div>
            </header>

            <!-- Filters and Search -->
            <div class="bg-white border-b border-slate-100 px-4 sm:px-6 py-3 sm:py-4 shrink-0">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Filter Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <button @click="filterStatus = 'all'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all border whitespace-nowrap"
                            :class="filterStatus === 'all' ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            All Tasks (<span x-text="counts.all"></span>)
                        </button>
                        <button @click="filterStatus = 'overdue'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="filterStatus === 'overdue' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Overdue
                        </button>
                        <button @click="filterStatus = 'pending'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="filterStatus === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Pending
                        </button>
                        <button @click="filterStatus = 'in_progress'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="filterStatus === 'in_progress' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            In Progress
                        </button>
                        <button @click="filterStatus = 'completed'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="filterStatus === 'completed' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Completed
                        </button>
                    </div>

                    <!-- Filter by Employee (admin/supervisor only) -->
                    <div x-show="showEmployeeFilter" class="relative flex-shrink-0" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-full text-xs sm:text-sm font-semibold bg-white text-slate-700 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 shadow-sm">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Filter by employee</span>
                            <span x-show="filterEmployeeIds.length > 0" class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full" x-text="filterEmployeeIds.length"></span>
                            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-72 max-h-80 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg z-20 py-2">
                            <div class="px-3 py-2 border-b border-slate-100">
                                <button type="button" @click="filterEmployeeIds = []; open = false"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Clear filter</button>
                            </div>
                            <template x-for="emp in employees" :key="emp.id">
                                <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" :checked="filterEmployeeIds.includes(emp.id)"
                                        @change="toggleEmployeeFilter(emp.id)"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <img :src="emp.profile_image_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(emp.full_name || emp.name || '') + '&background=6366f1&color=fff&size=64')"
                                        class="w-6 h-6 rounded-full object-cover" :alt="emp.full_name">
                                    <span class="text-sm font-medium text-slate-700 truncate" x-text="emp.full_name || emp.name || 'Unknown'"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50">
                <!-- Overview: Task Cards Grid -->
                <div x-show="view === 'overview' && filteredTasks.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="task in filteredTasks" :key="task.id">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md hover:border-slate-300 transition-all cursor-pointer group"
                            @click="openModal(task)">
                            <!-- Header: project + priority -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide" x-text="task.project?.name || 'No Project'"></div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                    :class="{
                                        'bg-red-100 text-red-700': task.priority === 'high',
                                        'bg-yellow-100 text-yellow-700': task.priority === 'medium',
                                        'bg-green-100 text-green-700': task.priority === 'low',
                                        'bg-purple-100 text-purple-700': task.priority === 'free'
                                    }"
                                    x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'">
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="mt-3 text-lg font-semibold text-slate-900 leading-snug line-clamp-1"
                                :title="task.description || ''"
                                x-text="(task.description || '').length > 30 ? (task.description || '').substring(0, 30) + '...' : (task.description || '')"></p>

                            <!-- Stage + Status row -->
                            <div class="mt-4 mb-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"
                                    x-text="formatStage(task.stage)"></span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="getStatusBadgeColor(task.status)"
                                    x-text="formatStatus(task.status)"></span>
                            </div>

                            <!-- Footer -->
                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5 gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="flex -space-x-2 shrink-0">
                                        <template x-for="(assignee, index) in task.assignees.slice(0, 3)" :key="assignee.id">
                                            <img :src="getProfileImageUrl(assignee)"
                                                :alt="assignee.full_name || assignee.name"
                                                :title="assignee.full_name || assignee.name"
                                                class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm">
                                        </template>
                                        <template x-if="task.assignees.length > 3">
                                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 shadow-sm"
                                                :title="'+' + (task.assignees.length - 3) + ' more'"
                                                x-text="'+' + (task.assignees.length - 3)"></div>
                                        </template>
                                    </div>
                                    <div class="text-xs font-medium text-slate-500 truncate">
                                        <span>Due </span>
                                        <span x-text="formatDate(task.end_date)"></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 text-xs font-semibold shrink-0"
                                    :class="dueInClass(task)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="dueIn(task)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State (Overview) -->
                <div x-show="view === 'overview' && filteredTasks.length === 0" class="flex flex-col items-center justify-center py-20">
                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-slate-500 font-medium">No tasks found</p>
                </div>

                <!-- Vertical Kanban View -->
                <div x-show="view === 'vertical'" class="h-full overflow-x-auto overflow-y-hidden">
                    <div class="flex h-full gap-3 sm:gap-4 md:gap-6 items-start pb-4 w-full min-w-max">
                        <template x-for="stage in stages" :key="stage">
                            <div class="flex-1 min-w-[280px] sm:min-w-[20rem] flex flex-col h-full bg-slate-50 rounded-lg sm:rounded-xl border border-slate-200 max-h-full">
                                <div class="p-3 sm:p-4 border-b border-slate-200 flex items-center justify-between shrink-0 bg-white rounded-t-lg sm:rounded-t-xl">
                                    <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                                        <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full shrink-0"
                                            :class="{
                                                'bg-red-500': stage === 'overdue',
                                                'bg-yellow-500': stage === 'pending',
                                                'bg-blue-500': stage === 'in_progress',
                                                'bg-green-500': stage === 'completed'
                                            }"></div>
                                        <span class="text-xs sm:text-sm font-bold text-slate-700 uppercase truncate" x-text="formatStage(stage)"></span>
                                        <span class="bg-slate-200 text-slate-600 px-1.5 sm:px-2 py-0.5 rounded text-[10px] sm:text-xs font-bold shrink-0" x-text="tasksByStage(stage).length"></span>
                                    </div>
                                </div>
                                <div class="flex-1 overflow-y-auto p-2 sm:p-3 space-y-2 sm:space-y-3" style="min-height: 100px;">
                                    <template x-for="task in tasksByStage(stage)" :key="task.id">
                                        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border border-slate-100 cursor-pointer hover:shadow-md transition-all"
                                            @click="openModal(task)">
                                            <div class="flex items-start justify-between gap-2 mb-1.5 sm:mb-2">
                                                <span class="text-[9px] sm:text-[10px] font-bold shrink-0" :class="dueInClass(task)" x-text="dueIn(task)"></span>
                                                <div class="flex flex-col items-end gap-0.5 min-w-0">
                                                    <span class="text-[9px] sm:text-[10px] text-slate-500 truncate max-w-full" x-text="task.project?.name || 'No Project'"></span>
                                                    <span class="text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded border shrink-0" :class="{
                                                        'text-red-600 bg-red-50 border-red-100': task.priority === 'high',
                                                        'text-yellow-600 bg-yellow-50 border-yellow-100': task.priority === 'medium',
                                                        'text-green-600 bg-green-50 border-green-100': task.priority === 'low',
                                                        'text-purple-600 bg-purple-50 border-purple-100': task.priority === 'free'
                                                    }" x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'"></span>
                                                </div>
                                            </div>
                                            <p class="text-xs sm:text-sm font-bold text-slate-800 leading-tight mb-1.5 sm:mb-2 line-clamp-2 break-words" x-text="(task.description || '').substring(0, 60) + ((task.description || '').length > 60 ? '...' : '')"></p>
                                            <div class="flex flex-col gap-0.5 text-[10px] sm:text-xs text-slate-500 mb-2 sm:mb-3">
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    <span>Due: <span x-text="formatDate(task.end_date)"></span></span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span>End: <span x-text="formatTime(task.end_date)"></span></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between border-t border-slate-50 pt-2 sm:pt-3 mt-1.5 sm:mt-2 gap-2">
                                                <div class="flex -space-x-1.5 sm:-space-x-2 overflow-hidden shrink-0">
                                                    <template x-for="(assignee, index) in task.assignees.slice(0, 3)" :key="assignee.id">
                                                        <img :src="getProfileImageUrl(assignee)" :alt="assignee.full_name || assignee.name" :title="assignee.full_name || assignee.name" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white object-cover shadow-sm">
                                                    </template>
                                                    <template x-if="task.assignees.length > 3">
                                                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-slate-600" :title="'+' + (task.assignees.length - 3)" x-text="'+' + (task.assignees.length - 3)"></div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Horizontal Table View -->
                <div x-show="view === 'horizontal'" class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs sm:text-sm min-w-[800px]">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Sr No</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Project Code</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Task</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Status</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Stage</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Assigned To</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Start Date</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">End Date</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left font-bold text-slate-700 whitespace-nowrap">Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(task, index) in filteredTasks" :key="task.id">
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer" @click="openModal(task)">
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 font-bold text-slate-900 whitespace-nowrap" x-text="String(index + 1).padStart(2, '0')"></td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 font-bold text-slate-900 whitespace-nowrap" x-text="task.project?.project_code || 'N/A'"></td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 min-w-[200px]">
                                            <div class="font-medium text-slate-900 break-words line-clamp-2" x-text="(task.description || '').substring(0, 80) + ((task.description || '').length > 80 ? '...' : '')"></div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap" @click.stop>
                                            <select @change="updateStatus(task.id, $event.target.value)" class="text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full border-0 focus:ring-2 focus:ring-blue-500 cursor-pointer" :class="getStatusSelectColor(task.status)" :value="task.status">
                                                <template x-for="status in statusOptions" :key="status">
                                                    <option :value="status" x-text="formatStatus(status)"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold" :class="getStageSelectColor(task.stage)" x-text="formatStage(task.stage)"></span>
                                        </td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                            <div class="flex -space-x-1.5 sm:-space-x-2">
                                                <template x-for="assignee in task.assignees.slice(0, 3)" :key="assignee.id">
                                                    <img :src="getProfileImageUrl(assignee)" :alt="assignee.full_name || assignee.name" :title="assignee.full_name || assignee.name" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover shadow-sm">
                                                </template>
                                                <template x-if="task.assignees.length > 3">
                                                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[10px] sm:text-xs font-bold text-slate-600" :title="'+' + (task.assignees.length - 3)" x-text="'+' + (task.assignees.length - 3)"></div>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 text-slate-600 whitespace-nowrap" x-text="formatDate(task.start_date)"></td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 text-slate-600 whitespace-nowrap" x-text="formatDate(task.end_date) + ' ' + formatTime(task.end_date)"></td>
                                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold" :class="getPriorityColor(task.priority)" x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="filteredTasks.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-slate-500 font-medium">No tasks found</p>
                    </div>
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
                                              'bg-yellow-50 text-yellow-600': selectedTask.priority === 'medium',
                                              'bg-green-50 text-green-600': selectedTask.priority === 'low',
                                              'bg-purple-50 text-purple-600': selectedTask.priority === 'free'
                                          }" x-text="selectedTask.priority"></span>
                                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 break-words" x-text="(selectedTask.description || '').substring(0, 120) + ((selectedTask.description || '').length > 120 ? '...' : '')"></h2>
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
                                            <template x-for="status in statusOptions" :key="status">
                                                <option :value="status" :selected="selectedTask.status === status"
                                                    x-text="formatStatus(status)"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stage</h3>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                            :class="getStageBadgeColor(selectedTask.stage)"
                                            x-text="formatStage(selectedTask.stage)"></span>
                                        <p class="text-[11px] text-slate-400 mt-1">Stage is set automatically based on status and due date.</p>
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
                                            </div>
                                        </template>
                                    </div>
                                <div class="sm:col-span-2">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assignees</h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <template x-for="assignee in editAssignees" :key="assignee.id">
                                            <div class="flex items-center gap-1.5 sm:gap-2 bg-indigo-50 px-2 py-1 rounded-lg">
                                                <img :src="getProfileImageUrl(assignee)"
                                                    :alt="assignee.full_name || assignee.name"
                                                    class="w-4 h-4 sm:w-5 sm:h-5 rounded-full object-cover">
                                                <span class="text-xs font-bold text-indigo-800 truncate max-w-[120px] sm:max-w-none"
                                                    x-text="assignee.full_name || assignee.name"></span>
                                                <template x-if="canEditDue">
                                                    <button type="button" @click="removeEditAssignee(assignee.id)"
                                                        class="text-slate-500 hover:text-red-600 -mr-0.5">×</button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="canEditDue">
                                            <button type="button" @click="openAddPeopleModal()"
                                                class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 text-lg leading-none">+</button>
                                        </template>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tagged</h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <template x-for="user in editTagged" :key="user.id">
                                            <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-100 px-2 py-1 rounded-lg">
                                                <img :src="getProfileImageUrl(user)" :alt="user.full_name || user.name"
                                                    class="w-4 h-4 sm:w-5 sm:h-5 rounded-full object-cover">
                                                <span class="text-xs font-bold text-slate-700 truncate max-w-[120px] sm:max-w-none"
                                                    x-text="user.full_name || user.name"></span>
                                                <template x-if="canEditDue">
                                                    <button type="button" @click="removeEditTagged(user.id)"
                                                        class="text-slate-500 hover:text-red-600 -mr-0.5">×</button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="canEditDue">
                                            <button type="button" @click="openTagModal()"
                                                class="w-8 h-8 rounded-full bg-slate-500 text-white flex items-center justify-center hover:bg-slate-600 text-lg leading-none">+</button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Add people modal -->
                            <template x-if="canEditDue">
                                <div x-show="showAddPeopleModal" x-transition
                                    class="fixed inset-0 z-[99998] flex items-center justify-center p-4 bg-black/50"
                                    style="display: none;" @click.self="showAddPeopleModal = false">
                                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col" @click.stop>
                                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                            <h3 class="text-base font-bold text-slate-800">Add people</h3>
                                            <button type="button" @click="showAddPeopleModal = false" class="text-slate-400 hover:text-slate-600">×</button>
                                        </div>
                                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                                            <template x-for="emp in availableEmployees" :key="emp.id">
                                                <label class="flex items-center gap-3 p-2 rounded-lg cursor-pointer"
                                                    :class="isEditAssignee(emp.id) ? 'bg-indigo-50' : 'hover:bg-slate-50'">
                                                    <input type="checkbox" :checked="isEditAssignee(emp.id)" @change="toggleEditAssignee(emp)" class="rounded border-slate-300 text-indigo-600">
                                                    <img :src="getProfileImageUrl(emp)" class="w-8 h-8 rounded-full object-cover" :alt="emp.full_name">
                                                    <span class="text-sm font-medium text-slate-700" x-text="emp.full_name"></span>
                                                </label>
                                            </template>
                                        </div>
                                        <div class="px-4 py-3 border-t border-slate-200 flex justify-end">
                                            <button type="button" @click="showAddPeopleModal = false"
                                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Done</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <!-- Tag people modal -->
                            <template x-if="canEditDue">
                                <div x-show="showTagModal" x-transition
                                    class="fixed inset-0 z-[99998] flex items-center justify-center p-4 bg-black/50"
                                    style="display: none;" @click.self="showTagModal = false">
                                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col" @click.stop>
                                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                            <h3 class="text-base font-bold text-slate-800">Tag people</h3>
                                            <button type="button" @click="showTagModal = false" class="text-slate-400 hover:text-slate-600">×</button>
                                        </div>
                                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                                            <template x-for="emp in availableEmployees" :key="emp.id">
                                                <label class="flex items-center gap-3 p-2 rounded-lg cursor-pointer"
                                                    :class="isEditTagged(emp.id) ? 'bg-slate-100' : 'hover:bg-slate-50'">
                                                    <input type="checkbox" :checked="isEditTagged(emp.id)" @change="toggleEditTagged(emp)" class="rounded border-slate-300 text-indigo-600">
                                                    <img :src="getProfileImageUrl(emp)" class="w-8 h-8 rounded-full object-cover" :alt="emp.full_name">
                                                    <span class="text-sm font-medium text-slate-700" x-text="emp.full_name"></span>
                                                </label>
                                            </template>
                                        </div>
                                        <div class="px-4 py-3 border-t border-slate-200 flex justify-end">
                                            <button type="button" @click="showTagModal = false"
                                                class="px-4 py-2 text-sm font-medium text-white bg-slate-600 rounded-lg hover:bg-slate-700">Done</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

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

                                <form @submit.prevent="saveAndClose()" class="space-y-2">
                                    <textarea x-model="newComment"
                                        class="w-full text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 px-3 py-2"
                                        rows="2"
                                        placeholder="Add a comment..."></textarea>
                                    <div class="flex justify-end">
                                        <button type="button" @click="newComment = ''"
                                            class="text-[11px] font-medium text-slate-500 hover:text-slate-700">
                                            Clear
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div x-show="showDeleteConfirm" x-cloak x-transition
                                class="px-4 sm:px-6 pb-4">
                                <div class="bg-white border border-red-200 rounded-lg p-4 shadow-sm">
                                    <h3 class="text-sm font-bold text-slate-900 mb-1">Delete task?</h3>
                                    <p class="text-xs text-slate-600 mb-3" x-show="taskToDelete">
                                        Are you sure you want to delete this task? This action cannot be undone.
                                    </p>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" @click="showDeleteConfirm = false; taskToDelete = null"
                                            :disabled="deleteInProgress"
                                            class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 disabled:opacity-50">
                                            Cancel
                                        </button>
                                        <button type="button" @click="confirmDeleteTask()"
                                            :disabled="deleteInProgress"
                                            class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                                            style="background-color:#dc2626;color:#ffffff;">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between rounded-b-xl sm:rounded-b-2xl">
                                <button
                                    x-show="canEditDue && selectedTask"
                                    type="button"
                                    @click="showDeleteConfirm = true; taskToDelete = selectedTask"
                                    class="text-xs sm:text-sm font-semibold text-red-600 hover:text-red-700 hover:underline">
                                    Delete task
                                </button>
                                <button type="button" @click="saveAndClose()"
                                    class="px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="saving">
                                    Save & close
                                </button>
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
            Alpine.data('taskManager', (initialTasks, allStatuses, allStages, initialCounts, canEditDue, initialEmployees, showEmployeeFilter, currentUserId) => ({
                tasks: initialTasks,
                statuses: allStatuses,
                stages: allStages,
                counts: initialCounts,
                canEditDue: canEditDue,
                employees: initialEmployees || [],
                showEmployeeFilter: !!showEmployeeFilter,
                filterEmployeeIds: [],
                search: '',
                sidebarOpen: true,
                view: 'overview',
                filterStatus: 'all',
                selectedTask: null,
                showDeleteConfirm: false,
                taskToDelete: null,
                deleteInProgress: false,
                editEndDate: '',
                editEndTime: '',
                taskComments: [],
                commentsLoading: false,
                newComment: '',
                isPostingComment: false,
                saving: false,
                editAssignees: [],
                editTagged: [],
                initialAssigneeIds: [],
                initialTaggedIds: [],
                availableEmployees: [],
                showAddPeopleModal: false,
                showTagModal: false,
                currentUserId: currentUserId,
                toast: {
                    show: false,
                    type: 'success',
                    message: ''
                },
                toastTimer: null,

                showToast(message, type = 'success') {
                    console.log('showToast called:', message, type);
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    console.log('toast.show is now:', this.toast.show);
                    clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },

                getStageBadgeColor(stage) {
                    const colors = {
                        'overdue': 'bg-red-100 text-red-700',
                        'pending': 'bg-yellow-100 text-yellow-700',
                        'in_progress': 'bg-blue-100 text-blue-700',
                        'completed': 'bg-green-100 text-green-700',
                    };
                    return colors[stage] || 'bg-slate-100 text-slate-700';
                },

                get statusOptions() {
                    // Supervisors/Admins (who can edit due dates) see all statuses
                    if (this.canEditDue) {
                        return this.statuses;
                    }

                    // Employees are limited to these statuses
                    const allowedForEmployees = ['under_review', 'completed', 'wip', 'revision'];
                    return this.statuses.filter(status => allowedForEmployees.includes(status));
                },

                async saveAndClose() {
                    const taskId = this.selectedTask?.id;
                    if (!taskId) return;
                    this.saving = true;
                    try {
                        if (this.canEditDue && this.editEndDate) await this.saveDue(taskId);
                        if (this.newComment.trim()) await this.submitComment(taskId);
                        if (this.canEditDue && this.peopleChanged()) await this.savePeople(taskId);
                        this.selectedTask = null;
                    } finally {
                        this.saving = false;
                    }
                },

                recomputeCounts() {
                    this.counts = {
                        all: this.tasks.length,
                        pending: this.tasks.filter(t => t.stage === 'pending').length,
                        in_progress: this.tasks.filter(t => t.stage === 'in_progress').length,
                        completed: this.tasks.filter(t => t.stage === 'completed').length,
                        overdue: this.tasks.filter(t => t.stage === 'overdue').length,
                    };
                },

                confirmDeleteTask() {
                    const task = this.taskToDelete;
                    if (!task || !task.id) return;
                    this.deleteTask(task.id);
                },
                async deleteTask(taskId) {
                    if (!taskId) return;
                    this.deleteInProgress = true;
                    try {
                        const response = await fetch(`/tasks/${taskId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            const msg = data.message || (response.status === 403 ? 'You do not have permission to delete this task.' : 'Failed to delete task.');
                            throw new Error(msg);
                        }
                        this.tasks = this.tasks.filter(t => t.id !== taskId);
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask = null;
                        }
                        this.showDeleteConfirm = false;
                        this.taskToDelete = null;
                        this.recomputeCounts();
                    } catch (e) {
                        console.error('Delete failed:', e);
                    } finally {
                        this.deleteInProgress = false;
                    }
                },
                getStatusBadgeColor(status) {
                    const colors = {
                        'not_started': 'bg-slate-100 text-slate-700',
                        'wip': 'bg-blue-100 text-blue-700',
                        'correction': 'bg-amber-100 text-amber-700',
                        'completed': 'bg-green-100 text-green-700',
                        'revision': 'bg-orange-100 text-orange-700',
                        'closed': 'bg-slate-100 text-slate-700',
                        'hold': 'bg-purple-100 text-purple-700',
                        'under_review': 'bg-yellow-100 text-yellow-700',
                        'awaiting_resources': 'bg-amber-100 text-amber-700',
                    };
                    return colors[status] || 'bg-slate-100 text-slate-700';
                },

                getStatusSelectColor(status) {
                    const colors = {
                        'wip': 'bg-blue-100 text-blue-700',
                        'correction': 'bg-amber-100 text-amber-700',
                        'completed': 'bg-green-100 text-green-700',
                        'revision': 'bg-orange-100 text-orange-700',
                        'closed': 'bg-slate-100 text-slate-700',
                        'hold': 'bg-purple-100 text-purple-700',
                        'under_review': 'bg-yellow-100 text-yellow-700',
                        'not_started': 'bg-slate-100 text-slate-700',
                        'awaiting_resources': 'bg-amber-100 text-amber-700',
                    };
                    return colors[status] || 'bg-slate-100 text-slate-700';
                },

                getStageSelectColor(stage) {
                    const colors = {
                        'overdue': 'bg-red-100 text-red-700',
                        'pending': 'bg-yellow-100 text-yellow-700',
                        'in_progress': 'bg-blue-100 text-blue-700',
                        'completed': 'bg-green-100 text-green-700',
                    };
                    return colors[stage] || 'bg-slate-100 text-slate-700';
                },

                getPriorityColor(priority) {
                    const colors = {
                        'high': 'bg-red-100 text-red-700',
                        'medium': 'bg-yellow-100 text-yellow-700',
                        'low': 'bg-green-100 text-green-700',
                        'free': 'bg-purple-100 text-purple-700',
                    };
                    return colors[priority] || 'bg-slate-100 text-slate-700';
                },

                peopleChanged() {
                    const a = this.editAssignees.map(e => e.id).sort().join(',');
                    const b = this.initialAssigneeIds.slice().sort().join(',');
                    const c = this.editTagged.map(e => e.id).sort().join(',');
                    const d = this.initialTaggedIds.slice().sort().join(',');
                    return a !== b || c !== d;
                },
                async savePeople(taskId) {
                    try {
                        const response = await fetch(`/tasks/${taskId}/people`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                assignees: this.editAssignees.map(e => e.id),
                                tagged: this.editTagged.map(e => e.id)
                            })
                        });
                        if (!response.ok) throw new Error();
                        const data = await response.json();
                        const task = this.tasks.find(t => t.id === taskId);
                        if (task) {
                            task.assignees = data.assignees || task.assignees;
                            task.taggedUsers = data.tagged_users || task.taggedUsers;
                        }
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask.assignees = data.assignees || this.selectedTask.assignees;
                            this.selectedTask.taggedUsers = data.tagged_users || this.selectedTask.taggedUsers;
                        }
                    } catch (e) {
                        console.error('Failed to update people', e);
                    }
                },
                openAddPeopleModal() {
                    this.showAddPeopleModal = true;
                    if (this.availableEmployees.length === 0) this.loadEmployeesForModal();
                },
                openTagModal() {
                    this.showTagModal = true;
                    if (this.availableEmployees.length === 0) this.loadEmployeesForModal();
                },
                async loadEmployeesForModal() {
                    try {
                        const response = await fetch('{{ route("tasks.employees") }}');
                        const data = await response.json();
                        this.availableEmployees = data;
                    } catch (e) {
                        console.error('Failed to load employees', e);
                    }
                },
                isEditAssignee(employeeId) { return this.editAssignees.some(e => e.id === employeeId); },
                toggleEditAssignee(emp) {
                    if (this.isEditAssignee(emp.id)) this.editAssignees = this.editAssignees.filter(e => e.id !== emp.id);
                    else this.editAssignees = [...this.editAssignees, emp];
                },
                removeEditAssignee(employeeId) { this.editAssignees = this.editAssignees.filter(e => e.id !== employeeId); },
                isEditTagged(employeeId) { return this.editTagged.some(e => e.id === employeeId); },
                toggleEditTagged(emp) {
                    if (this.isEditTagged(emp.id)) this.editTagged = this.editTagged.filter(e => e.id !== emp.id);
                    else this.editTagged = [...this.editTagged, emp];
                },
                removeEditTagged(employeeId) { this.editTagged = this.editTagged.filter(e => e.id !== employeeId); },

                toggleEmployeeFilter(employeeId) {
                    const idx = this.filterEmployeeIds.indexOf(employeeId);
                    if (idx === -1) {
                        this.filterEmployeeIds = [...this.filterEmployeeIds, employeeId];
                    } else {
                        this.filterEmployeeIds = this.filterEmployeeIds.filter(id => id !== employeeId);
                    }
                },

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
                            (t.description && t.description.toLowerCase().includes(q)) ||
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

                    // Filter by Employee(s)
                    if (this.filterEmployeeIds && this.filterEmployeeIds.length > 0) {
                        filtered = filtered.filter(t =>
                            t.assignees && t.assignees.some(a => this.filterEmployeeIds.includes(a.id))
                        );
                    }

                    // Sort: overdue → pending → in_progress → completed, then by priority (high → medium → low → free)
                    const stageOrder = { overdue: 0, pending: 1, in_progress: 2, completed: 3 };
                    const priorityOrder = { high: 0, medium: 1, low: 2, free: 3 };
                    filtered = [...filtered].sort((a, b) => {
                        const stageA = stageOrder[a.stage] ?? 4;
                        const stageB = stageOrder[b.stage] ?? 4;
                        if (stageA !== stageB) return stageA - stageB;
                        const priA = priorityOrder[a.priority] ?? 4;
                        const priB = priorityOrder[b.priority] ?? 4;
                        return priA - priB;
                    });

                    return filtered;
                },

                tasksByStage(stage) {
                    return this.filteredTasks.filter(t => t.stage === stage);
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

                dueIn(task) {
                    if (!task || !task.end_date) return 'No due date';
                    const end = new Date(task.end_date);
                    const now = new Date();
                    if (end < now) return 'Overdue';
                    const ms = end - now;
                    const hours = Math.floor(ms / (1000 * 60 * 60));
                    const days = Math.floor(hours / 24);
                    if (days >= 1) return days + ' day' + (days !== 1 ? 's' : '') + ' left';
                    if (hours >= 1) return hours + ' hour' + (hours !== 1 ? 's' : '') + ' left';
                    const mins = Math.floor(ms / (1000 * 60));
                    return (mins <= 0 ? 'Due now' : mins + ' min left');
                },
                dueInClass(task) {
                    if (!task || !task.end_date) return 'text-slate-500';
                    const end = new Date(task.end_date);
                    if (end < new Date()) return 'text-red-600';
                    const ms = end - new Date();
                    const hours = ms / (1000 * 60 * 60);
                    if (hours <= 24) return 'text-amber-600';
                    return 'text-slate-600';
                },

                formatTime(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
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
                        const data = await response.json();
                        if (data.stage) {
                            task.stage = data.stage;
                            if (this.selectedTask && this.selectedTask.id === taskId) {
                                this.selectedTask.stage = data.stage;
                            }
                        }
                        this.recomputeCounts();
                    } catch (e) {
                        task.status = oldStatus;
                        if (this.selectedTask && this.selectedTask.id === taskId) {
                            this.selectedTask.status = oldStatus;
                        }
                        console.error('Failed to update status');
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
                    this.editAssignees = (task.assignees || []).map(a => ({ ...a }));
                    this.editTagged = (task.taggedUsers || []).map(t => ({ ...t }));
                    this.initialAssigneeIds = (task.assignees || []).map(a => a.id);
                    this.initialTaggedIds = (task.taggedUsers || []).map(t => t.id);

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
