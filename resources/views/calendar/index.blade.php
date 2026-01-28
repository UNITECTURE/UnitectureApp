@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
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

                    {{-- Filters --}}
                    <div
                        class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-100 px-4 py-3 flex flex-col md:flex-row md:items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold tracking-wide text-slate-400 uppercase">Filters</span>
                        </div>
                        <div class="flex-1 flex flex-wrap items-center gap-3">
                            {{-- Multi-select Employees --}}
                            <div class="flex items-center gap-2">
                                <label for="calendar-user-filter" class="text-xs font-medium text-slate-500">Employees</label>
                                <select id="calendar-user-filter" multiple
                                    class="min-w-[180px] text-sm rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 bg-slate-50 text-slate-700 px-3 py-1.5">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-[11px] text-slate-400">
                                Select one or more employees to show their tasks, leaves, holidays and attendance.
                            </p>
                        </div>
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

    {{-- FullCalendar CDN (no build step required) --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl || !window.FullCalendar) return;

            const userFilterEl = document.getElementById('calendar-user-filter');

            const calendar = new FullCalendar.Calendar(calendarEl, {
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

                    // Collect selected employee IDs (multi-select)
                    if (userFilterEl) {
                        const selected = Array.from(userFilterEl.selectedOptions || [])
                            .map(opt => opt.value)
                            .filter(Boolean);
                        if (selected.length) {
                            selected.forEach(id => params.append('user_ids[]', id));
                        }
                    }

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

            calendar.render();

            if (userFilterEl) {
                userFilterEl.addEventListener('change', function () {
                    calendar.refetchEvents();
                });
            }
        });
    </script>
@endsection

