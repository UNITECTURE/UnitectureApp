@extends('layouts.app')

@section('content')
    <script>
        window.__calendarAllUserIds = @json($users->pluck('id')->values());
    </script>
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="calendarFilterData()">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8 space-y-6">
                    {{-- Page Header --}}
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Team Calendar</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">
                                View team leaves, holidays, tasks, and absences in one place.
                            </p>
                        </div>

                        {{-- Legend --}}
                        <div class="hidden md:flex items-center gap-4 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#22c55e]"></span>
                                <span class="text-slate-500 font-medium">Approved Leave</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#eab308]"></span>
                                <span class="text-slate-500 font-medium">Pending Leave</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#3b82f6]"></span>
                                <span class="text-slate-500 font-medium">Holiday</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#f97316]"></span>
                                <span class="text-slate-500 font-medium">Task</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#ef4444]"></span>
                                <span class="text-slate-500 font-medium">Absent</span>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Button --}}
                    <div class="flex items-center justify-end gap-2">
                        <span x-show="hasActiveFilters()" class="text-xs text-slate-500 font-medium" x-text="filterSummary()"></span>
                        <button @click="openFilterModal()"
                            class="p-2.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                            <span class="text-sm font-medium text-slate-600">Filters</span>
                        </button>
                    </div>

                    {{-- Calendar Card --}}
                    <div
                        class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-100 p-4 md:p-6">
                        <div id="calendar"
                            class="fc-theme-standard text-sm [&_.fc-toolbar-title]:text-lg [&_.fc-toolbar-title]:font-semibold [&_.fc-toolbar-title]:text-slate-800 [&_.fc-button]:!bg-slate-100 [&_.fc-button]:!border-slate-200 [&_.fc-button]:!text-slate-700 [&_.fc-button-active]:!bg-blue-600 [&_.fc-button-active]:!text-white [&_.fc-daygrid-day-number]:text-xs">
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- Filter Modal --}}
    <template x-teleport="body">
        <div x-show="showFilterModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            style="display: none;"
            @click.self="showFilterModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[85vh] overflow-hidden flex flex-col"
                @click.stop>
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Filter Calendar</h3>
                    <button type="button" @click="showFilterModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    {{-- Event Type Filters (multiselect) --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Event types</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors hover:bg-slate-50 border border-slate-200"
                                :class="{ 'bg-blue-50 border-blue-300': eventTypes.includes('task') }">
                                <input type="checkbox"
                                    :checked="eventTypes.includes('task')"
                                    @change="eventTypes.includes('task') ? eventTypes = eventTypes.filter(t => t !== 'task') : eventTypes.push('task')"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="w-3 h-3 rounded-full bg-[#f97316]"></span>
                                    <span class="text-sm font-medium text-slate-700">Task</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors hover:bg-slate-50 border border-slate-200"
                                :class="{ 'bg-blue-50 border-blue-300': eventTypes.includes('leave') }">
                                <input type="checkbox"
                                    :checked="eventTypes.includes('leave')"
                                    @change="eventTypes.includes('leave') ? eventTypes = eventTypes.filter(t => t !== 'leave') : eventTypes.push('leave')"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="w-3 h-3 rounded-full bg-[#22c55e]"></span>
                                    <span class="text-sm font-medium text-slate-700">Leave</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors hover:bg-slate-50 border border-slate-200"
                                :class="{ 'bg-blue-50 border-blue-300': eventTypes.includes('attendance') }">
                                <input type="checkbox"
                                    :checked="eventTypes.includes('attendance')"
                                    @change="eventTypes.includes('attendance') ? eventTypes = eventTypes.filter(t => t !== 'attendance') : eventTypes.push('attendance')"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="w-3 h-3 rounded-full bg-[#ef4444]"></span>
                                    <span class="text-sm font-medium text-slate-700">Attendance</span>
                                </div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Select one or more event types</p>
                    </div>

                    {{-- Employees Multi-select --}}
                    <div>
                        <label for="calendar-user-filter" class="block text-sm font-semibold text-slate-700 mb-2">
                            Employees
                        </label>
                        <div class="flex gap-2 mb-2">
                            <button type="button" @click="selectedUserIds = allUserIds.slice()"
                                class="text-xs font-medium text-blue-600 hover:text-blue-700">Select all</button>
                            <button type="button" @click="selectedUserIds = []"
                                class="text-xs font-medium text-slate-500 hover:text-slate-700">Clear all</button>
                        </div>
                        <select id="calendar-user-filter" multiple x-model="selectedUserIds"
                            class="w-full text-sm rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 bg-slate-50 text-slate-700 px-3 py-2 min-h-[140px]">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name ?? $user->name ?? 'User' }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1.5">Select one or more employees (empty = all)</p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" @click="showFilterModal = false"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                        Close
                    </button>
                    <button type="button" @click="applyFilters()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- FullCalendar CDN (no build step required) --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <script>
        // Global filter state used by calendar events fetch (set before first render)
        window.calendarFilterState = {
            eventTypes: ['task', 'leave', 'attendance'],
            userIds: []
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarFilterData', () => {
                const allUserIds = (window.__calendarAllUserIds || []).map(String);
                return {
                    sidebarOpen: true,
                    showFilterModal: false,
                    eventTypes: ['task', 'leave', 'attendance'],
                    selectedUserIds: [],
                    allUserIds: allUserIds,
                    openFilterModal() {
                        if (window.calendarFilterState) {
                            this.eventTypes = [...(window.calendarFilterState.eventTypes || ['task', 'leave', 'attendance'])];
                            this.selectedUserIds = [...(window.calendarFilterState.userIds || [])];
                        }
                        this.showFilterModal = true;
                    },
                    applyFilters() {
                        if (typeof window.calendarFilterState === 'undefined') window.calendarFilterState = {};
                        window.calendarFilterState.eventTypes = [...this.eventTypes];
                        window.calendarFilterState.userIds = this.selectedUserIds.map(String).filter(Boolean);
                        if (window.calendarInstance) window.calendarInstance.refetchEvents();
                        this.showFilterModal = false;
                    },
                    hasActiveFilters() {
                        if (!window.calendarFilterState) return false;
                        const types = window.calendarFilterState.eventTypes || [];
                        const userIds = window.calendarFilterState.userIds || [];
                        return userIds.length > 0 || (types.length > 0 && types.length < 3);
                    },
                    filterSummary() {
                        if (!window.calendarFilterState) return '';
                        const t = (window.calendarFilterState.eventTypes || []).length;
                        const u = (window.calendarFilterState.userIds || []).length;
                        const typeStr = t === 3 ? 'All types' : t + ' type(s)';
                        const userStr = u === 0 ? 'All employees' : u + ' employee(s)';
                        return typeStr + ' · ' + userStr;
                    }
                };
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl || !window.FullCalendar) return;

            let calendarInstance;

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                firstDay: 1, // Monday
                navLinks: true,
                selectable: false,
                dayMaxEvents: 3,
                eventDisplay: 'block',
                displayEventEnd: true,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                },
                events: function (fetchInfo, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    });

                    const state = window.calendarFilterState || {};
                    const userIds = state.userIds || [];
                    const eventTypes = state.eventTypes || ['task', 'leave', 'attendance'];

                    if (userIds.length > 0) {
                        userIds.forEach(id => params.append('user_ids[]', id));
                    }
                    eventTypes.forEach(type => params.append('event_types[]', type));

                    fetch('{{ route('calendar.events') }}?' + params.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            successCallback(data.events || []);
                        })
                        .catch(error => {
                            console.error('Error loading events:', error);
                            failureCallback(error);
                        });
                },
                eventDidMount: function (info) {
                    const { event, el } = info;
                    const props = event.extendedProps || {};

                    let tooltip = event.title;

                    if (event.extendedProps.type === 'leave') {
                        tooltip += `\nStatus: ${props.status ?? 'N/A'}`;
                        tooltip += `\nType: ${props.leave_type ?? 'N/A'}`;
                        if (props.reason) {
                            tooltip += `\nReason: ${props.reason}`;
                        }
                    } else if (event.extendedProps.type === 'holiday') {
                        if (props.description) {
                            tooltip += `\n${props.description}`;
                        }
                    } else if (event.extendedProps.type === 'task') {
                        if (props.project) {
                            tooltip += `\nProject: ${props.project}`;
                        }
                        if (props.assignees) {
                            tooltip += `\nAssignees: ${props.assignees}`;
                        }
                        if (props.priority) {
                            tooltip += `\nPriority: ${props.priority}`;
                        }
                        if (props.status) {
                            tooltip += `\nStatus: ${props.status}`;
                        }
                    }

                    el.setAttribute('title', tooltip);
                }
            });

            calendarInstance.render();
            window.calendarInstance = calendarInstance;
        });
    </script>
@endsection

