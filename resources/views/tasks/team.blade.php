@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="teamTasks({{ json_encode($tasks) }}, {{ json_encode($statuses) }}, {{ json_encode($stages) }}, {{ auth()->user()->isAdmin() || auth()->user()->isSupervisor() ? 'true' : 'false' }})">
        <x-sidebar :role="auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isSupervisor() ? 'supervisor' : 'employee')" />
        
        <div class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <!-- Header -->
            <header class="bg-white border-b border-slate-200 py-5 px-6 shrink-0 z-10">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-900">My Team Tasks</h1>
                            <p class="text-slate-500 text-sm font-medium mt-1">Track and manage tasks assigned to your team members</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- View Toggle -->
                            <div class="flex bg-white border border-slate-200 shadow-sm p-0.5 rounded-lg gap-0.5">
                                <button @click="view = 'vertical'" 
                                    class="p-2 rounded-md transition-all duration-200 relative"
                                    :class="view === 'vertical' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V3zM5 13a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                </button>
                                <button @click="view = 'horizontal'" 
                                    class="p-2 rounded-md transition-all duration-200 relative"
                                    :class="view === 'horizontal' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>

                            <a href="{{ route('tasks.index') }}"
                                class="text-slate-600 hover:text-indigo-600 p-2 rounded-lg hover:bg-indigo-50 transition-all duration-200 border border-transparent hover:border-indigo-200 shadow-sm hover:shadow">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path>
                                </svg>
                            </a>

                            <a href="{{ route('tasks.create') }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 shadow-md transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Add Task</span>
                            </a>
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <button @click="selectedStage = null"
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all border whitespace-nowrap"
                            :class="selectedStage === null ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            All Tasks (<span x-text="tasks.length"></span>)
                        </button>

                        <button @click="selectedStage = 'overdue'"
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="selectedStage === 'overdue' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                            Overdue
                        </button>

                        <button @click="selectedStage = 'pending'"
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="selectedStage === 'pending' ? 'bg-yellow-100 border-yellow-500 text-yellow-700' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            <span class="w-2 h-2 rounded-full bg-yellow-500 shrink-0"></span>
                            Pending
                        </button>

                        <button @click="selectedStage = 'in_progress'"
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="selectedStage === 'in_progress' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                            In Progress
                        </button>

                        <button @click="selectedStage = 'completed'"
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all border flex items-center gap-2 whitespace-nowrap"
                            :class="selectedStage === 'completed' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-white border-slate-300 text-slate-700 hover:border-slate-400'">
                            <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                            Completed
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-auto">
                <!-- Vertical Kanban View -->
                <div x-show="view === 'vertical'" class="h-full overflow-x-auto overflow-y-hidden p-3 sm:p-4 md:p-6">
                    <div class="flex h-full gap-3 sm:gap-4 md:gap-6 items-start pb-4 w-full" style="min-width: max-content;">
                        <template x-for="stage in stages" :key="stage">
                            <div class="flex-1 min-w-[280px] sm:min-w-[20rem] flex flex-col h-full bg-slate-50 rounded-lg sm:rounded-xl border border-slate-200 max-h-full"
                                @dragover.prevent="dragOverStage = stage" @dragleave="dragOverStage = null"
                                @drop="drop($event, stage); dragOverStage = null"
                                :class="{ 'ring-2 ring-indigo-400 ring-inset bg-indigo-50': dragOverStage === stage }">
                                
                                <!-- Column Header -->
                                <div class="p-4 border-b border-slate-200 flex items-center justify-between shrink-0 bg-white rounded-t-xl">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full"
                                            :class="{
                                                'bg-red-500': stage === 'overdue',
                                                'bg-yellow-500': stage === 'pending',
                                                'bg-blue-500': stage === 'in_progress',
                                                'bg-green-500': stage === 'completed'
                                            }"></div>
                                        <span class="text-sm font-bold text-slate-700 uppercase"
                                            x-text="formatStage(stage)"></span>
                                        <span class="bg-slate-200 text-slate-600 px-2 py-0.5 rounded text-xs font-bold"
                                            x-text="tasksByStage(stage).length"></span>
                                    </div>
                                </div>

                                <!-- Cards Container -->
                                <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar" style="min-height: 100px;">
                                    <template x-for="task in tasksByStage(stage)" :key="task.id">
                                        <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 cursor-grab hover:shadow-md transition-all active:cursor-grabbing group relative"
                                            draggable="true" @dragstart="dragStart($event, task)" @click="openModal(task)">
                                            
                                            <!-- Header: time left (left), project + priority (right) -->
                                            <div class="flex items-start justify-between gap-2 mb-2">
                                                <span class="text-[10px] font-bold shrink-0" :class="dueInClass(task)" x-text="dueIn(task)"></span>
                                                <div class="flex flex-col items-end gap-0.5 min-w-0">
                                                    <span class="text-[10px] text-slate-500 truncate max-w-full" x-text="task.project?.name || 'No Project'"></span>
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border shrink-0" :class="{
                                                          'text-red-600 bg-red-50 border-red-100': task.priority === 'high',
                                                          'text-orange-600 bg-orange-50 border-orange-100': task.priority === 'medium',
                                                          'text-green-600 bg-green-50 border-green-100': task.priority === 'low',
                                                          'text-slate-600 bg-slate-50 border-slate-100': task.priority === 'free'
                                                      }" x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'"></span>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <p class="text-sm font-bold text-slate-800 leading-tight mb-2 line-clamp-2" x-text="(task.description || '').substring(0, 60) + ((task.description || '').length > 60 ? '...' : '')"></p>

                                            <!-- Due time -->
                                            <div class="flex items-center gap-1 text-xs text-slate-500 mb-3">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span x-text="formatTime(task.end_date)"></span>
                                            </div>

                                            <!-- Footer -->
                                            <div class="flex items-center justify-between border-t border-slate-50 pt-3 mt-2">
                                                <!-- Assignees -->
                                                <div class="flex -space-x-2 overflow-hidden">
                                                    <template x-for="(assignee, index) in task.assignees.slice(0, 3)" :key="assignee.id">
                                                        <img :src="getProfileImageUrl(assignee)"
                                                            :alt="assignee.full_name || assignee.name"
                                                            :title="assignee.full_name || assignee.name"
                                                            class="w-6 h-6 rounded-full border-2 border-white object-cover shadow-sm">
                                                    </template>
                                                    <template x-if="task.assignees.length > 3">
                                                        <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600"
                                                            :title="'+' + (task.assignees.length - 3)"
                                                            x-text="'+' + (task.assignees.length - 3)">
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Due Date -->
                                                <div class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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

                <!-- Horizontal Table View -->
                <div x-show="view === 'horizontal'" class="p-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50">
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Sr No</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Project Code</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Task</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Status</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Stage</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Assigned To</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Start Date</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">End Date</th>
                                        <th class="px-6 py-3 text-left font-bold text-slate-700">Priority</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(task, index) in filteredTasks()" :key="task.id">
                                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer"
                                            @click="openModal(task)">
                                            <td class="px-6 py-3 font-bold text-slate-900" x-text="String(index + 1).padStart(2, '0')"></td>
                                            <td class="px-6 py-3 font-bold text-slate-900" x-text="task.project?.project_code || 'N/A'"></td>
                                            <td class="px-6 py-3">
                                                <div class="font-medium text-slate-900 line-clamp-2" x-text="(task.description || '').substring(0, 80) + ((task.description || '').length > 80 ? '...' : '')"></div>
                                            </td>
                                            <td class="px-6 py-3" @click.stop>
                                                <select @change="updateStatus(task.id, $event.target.value)" 
                                                    class="text-xs font-bold px-2 py-1 rounded-full border-0 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                                    :class="getStatusSelectColor(task.status)"
                                                    :value="task.status">
                                                    <template x-for="status in statuses" :key="status">
                                                        <option :value="status" x-text="formatStatus(status)"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-6 py-3" @click.stop>
                                                <select @change="updateStage(task.id, $event.target.value)" 
                                                    class="text-xs font-bold px-2 py-1 rounded-full border-0 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                                    :class="getStageSelectColor(task.stage)"
                                                    :value="task.stage">
                                                    <template x-for="stage in stages" :key="stage">
                                                        <option :value="stage" x-text="formatStage(stage)"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-6 py-3">
                                                <div class="flex -space-x-2">
                                                    <template x-for="assignee in task.assignees.slice(0, 3)" :key="assignee.id">
                                                        <img :src="getProfileImageUrl(assignee)"
                                                            :alt="assignee.full_name || assignee.name"
                                                            :title="assignee.full_name || assignee.name"
                                                            class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm">
                                                    </template>
                                                    <template x-if="task.assignees.length > 3">
                                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600"
                                                            :title="'+' + (task.assignees.length - 3)"
                                                            x-text="'+' + (task.assignees.length - 3)">
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 text-slate-600" x-text="formatDate(task.start_date)"></td>
                                            <td class="px-6 py-3 text-slate-600" x-text="formatDate(task.end_date)"></td>
                                            <td class="px-6 py-3">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
                                                    :class="getPriorityColor(task.priority)"
                                                    x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Normal'">
                                                </span>
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

        <!-- Task Detail Modal -->
        <template x-teleport="body">
            <div x-show="selectedTask"
                class="fixed inset-0 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                style="z-index: 99999; display: none;"
                x-transition.opacity
            @click.self="selectedTask = null">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto"
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
                                <h2 class="text-xl font-bold text-slate-900" x-text="(selectedTask.description || '').substring(0, 120) + ((selectedTask.description || '').length > 120 ? '...' : '')"></h2>
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
                                    <template x-if="!canEditDue">
                                        <p class="text-sm font-bold text-slate-700"
                                            x-text="formatDate(selectedTask.end_date, true)"></p>
                                    </template>
                                    <template x-if="canEditDue">
                                        <div class="space-y-2">
                                            <p class="text-[11px] text-slate-400">
                                                Current:
                                                <span class="font-semibold text-slate-600"
                                                    x-text="formatDate(selectedTask.end_date, true)"></span>
                                            </p>
                                            <div class="flex gap-2">
                                                <input type="date"
                                                    x-model="editEndDate"
                                                    class="flex-1 rounded-lg border-slate-200 text-xs px-2 py-1.5 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500">
                                                <input type="time"
                                                    x-model="editEndTime"
                                                    :disabled="selectedTask.priority === 'free'"
                                                    class="w-24 rounded-lg border-slate-200 text-xs px-2 py-1.5 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-400">
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assignees</h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <template x-for="assignee in editAssignees" :key="assignee.id">
                                            <div class="flex items-center gap-2 bg-indigo-50 px-2 py-1 rounded-lg">
                                                <img :src="getProfileImageUrl(assignee)"
                                                    :alt="assignee.full_name || assignee.name"
                                                    class="w-5 h-5 rounded-full object-cover">
                                                <span class="text-xs font-bold text-indigo-800"
                                                    x-text="assignee.full_name || assignee.name"></span>
                                                <template x-if="canEditDue">
                                                    <button type="button" @click="removeEditAssignee(assignee.id)"
                                                        class="text-slate-500 hover:text-red-600 -mr-0.5">×</button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="canEditDue">
                                            <button type="button" @click="openAddPeopleModal()"
                                                class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 text-lg leading-none">
                                                +
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tagged</h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <template x-for="user in editTagged" :key="user.id">
                                            <div class="flex items-center gap-2 bg-slate-100 px-2 py-1 rounded-lg">
                                                <img :src="getProfileImageUrl(user)"
                                                    :alt="user.full_name || user.name"
                                                    class="w-5 h-5 rounded-full object-cover">
                                                <span class="text-xs font-bold text-slate-700"
                                                    x-text="user.full_name || user.name"></span>
                                                <template x-if="canEditDue">
                                                    <button type="button" @click="removeEditTagged(user.id)"
                                                        class="text-slate-500 hover:text-red-600 -mr-0.5">×</button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="canEditDue">
                                            <button type="button" @click="openTagModal()"
                                                class="w-8 h-8 rounded-full bg-slate-500 text-white flex items-center justify-center hover:bg-slate-600 text-lg leading-none">
                                                +
                                            </button>
                                        </template>
                                    </div>
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
                        <div class="px-6 pb-6 space-y-3 border-t border-slate-100">
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

                        <div class="bg-slate-50 px-6 py-4 flex justify-end rounded-b-2xl">
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('teamTasks', (initialTasks, statuses, stages, canEditDue) => ({
                tasks: initialTasks,
                statuses: statuses,
                stages: stages,
                sidebarOpen: true,
                view: 'vertical',
                selectedStage: null,
                selectedTask: null,
                dragOverStage: null,
                canEditDue: canEditDue,
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
                isEditAssignee(employeeId) {
                    return this.editAssignees.some(e => e.id === employeeId);
                },
                toggleEditAssignee(emp) {
                    if (this.isEditAssignee(emp.id)) {
                        this.editAssignees = this.editAssignees.filter(e => e.id !== emp.id);
                    } else {
                        this.editAssignees = [...this.editAssignees, emp];
                    }
                },
                removeEditAssignee(employeeId) {
                    this.editAssignees = this.editAssignees.filter(e => e.id !== employeeId);
                },
                isEditTagged(employeeId) {
                    return this.editTagged.some(e => e.id === employeeId);
                },
                toggleEditTagged(emp) {
                    if (this.isEditTagged(emp.id)) {
                        this.editTagged = this.editTagged.filter(e => e.id !== emp.id);
                    } else {
                        this.editTagged = [...this.editTagged, emp];
                    }
                },
                removeEditTagged(employeeId) {
                    this.editTagged = this.editTagged.filter(e => e.id !== employeeId);
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

                tasksByStage(stage) {
                    return this.filteredTasks().filter(t => t.stage === stage);
                },

                filteredTasks() {
                    return this.selectedStage 
                        ? this.tasks.filter(t => t.stage === this.selectedStage)
                        : this.tasks;
                },

                dragStart(event, task) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', JSON.stringify(task));
                    event.target.classList.add('opacity-50');
                },

                async drop(event, newStage) {
                    const data = event.dataTransfer.getData('text/plain');
                    if (!data) return;
                    
                    const task = JSON.parse(data);
                    const validTask = this.tasks.find(t => t.id === task.id);
                    
                    if (validTask && validTask.stage !== newStage) {
                        await this.updateStage(validTask.id, newStage);
                    }
                    
                    document.querySelectorAll('.opacity-50').forEach(el => el.classList.remove('opacity-50'));
                },

                formatStatus(status) {
                    return status.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                },

                formatStage(stage) {
                    return stage ? stage.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Pending';
                },

                formatDate(dateString, full = false) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    if (full) {
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    }
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                },

                formatTime(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
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

                getPriorityColor(priority) {
                    const colors = {
                        'high': 'bg-red-100 text-red-700',
                        'medium': 'bg-orange-100 text-orange-700',
                        'low': 'bg-green-100 text-green-700',
                        'free': 'bg-slate-100 text-slate-700'
                    };
                    return colors[priority] || 'bg-slate-100 text-slate-700';
                },

                getStatusSelectColor(status) {
                    const colors = {
                        'wip': 'bg-blue-100 text-blue-700',
                        'completed': 'bg-green-100 text-green-700',
                        'revision': 'bg-orange-100 text-orange-700',
                        'closed': 'bg-slate-100 text-slate-700',
                        'hold': 'bg-purple-100 text-purple-700',
                        'under_review': 'bg-yellow-100 text-yellow-700',
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
                        alert('Failed to update status');
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
                        alert('Failed to update stage');
                    }
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
