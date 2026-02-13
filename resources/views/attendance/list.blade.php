@extends('layouts.app')

@section('content')
    @php
        $role = $role ?? 'admin';
        $scope = $scope ?? 'all';
        $isMonthlyReport = $isMonthlyReport ?? false;
        $selectedUserId = $selectedUserId ?? 'all';
        $reqMonth = $reqMonth ?? now()->month;
        $reqYear = $reqYear ?? now()->year;
    @endphp


    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">

        {{-- Sidebar --}}
        <x-sidebar :role="$role" />

        {{-- Main Content --}}
        <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
            {{-- Top Header --}}
            <div class="flex items-center justify-between px-8 py-6 bg-white shrink-0">
                <div class="flex items-center gap-4">
                    <button onclick="history.back()"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">{{ 'Attendance' }}</h1>
                        <p class="text-slate-500 text-sm mt-1">
                            {{ 'Track daily and cumulative attendance records.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Content Scroll Area --}}
            <div class="flex-1 flex flex-col overflow-hidden px-8 pb-8">
                <div class="flex-1 flex flex-col lg:flex-row gap-6 min-h-0">

                    {{-- LEFT CARD: Daily Report --}}
                    <div class="flex-1 flex flex-col min-w-0">
                        <div
                            class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden">
                            {{-- Fixed Header Section --}}
                            <div class="p-6 pb-0 shrink-0">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-lg font-bold text-slate-800">
                                        {{ $isMonthlyReport ? 'Monthly Report' : 'Daily Report' }}
                                    </h2>
                                </div>

                                {{-- Unified Filter Form --}}
                                <form method="GET" action="{{ route(Route::currentRouteName()) }}" id="attendance-filter-form" class="mb-6 space-y-3">
                                    
                                    {{-- Row 1: Employee & Date (Conditiona) --}}
                                    <div class="flex gap-3">
                                        {{-- Employee Selector --}}
                                        <div class="w-1/2">
                                            <select name="user_id" onchange="document.getElementById('attendance-filter-form').submit()"
                                                class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                                <option value="all" {{ $selectedUserId === 'all' ? 'selected' : '' }}>All Employees</option>
                                                @foreach($users as $u)
                                                    <option value="{{ $u->id }}" {{ $selectedUserId == $u->id ? 'selected' : '' }}>
                                                        {{ $u->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Date Picker (Only for Daily View) --}}
                                        @if(!$isMonthlyReport)
                                            <div class="w-1/2 relative">
                                                 <div x-data x-init="
                                                    $refs.picker.value = '{{ request('date', date('D, M j')) }}'; 
                                                    flatpickr($refs.picker, { 
                                                        dateFormat: 'D, M j', 
                                                        defaultDate: '{{ request('date', date('Y-m-d')) }}',
                                                        onChange: function(selectedDates, dateStr, instance) {
                                                            const date = instance.formatDate(selectedDates[0], 'Y-m-d');
                                                            // We must submit the form to persist other filters if any (though for Daily, it's just date mostly)
                                                            // But here we insert input and submit form
                                                            const form = document.getElementById('attendance-filter-form');
                                                            let input = form.querySelector('input[name=date]');
                                                            if(!input) {
                                                                input = document.createElement('input');
                                                                input.type = 'hidden';
                                                                input.name = 'date';
                                                                form.appendChild(input);
                                                            }
                                                            input.value = date;
                                                            form.submit();
                                                        }
                                                    })"
                                                    class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 flex items-center w-full cursor-pointer hover:bg-slate-100 transition-colors">
                                                    <svg class="h-4 w-4 text-slate-500 mr-3" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    <input x-ref="picker" type="text"
                                                        class="bg-transparent border-none text-slate-600 font-medium text-xs p-0 focus:ring-0 w-full cursor-pointer"
                                                        readonly>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Row 2: Month & Year (Always Visible or Conditional? User said "when supervisor selects... month year") --}}
                                    {{-- Let's make it always visible but perhaps mainly relevant for Monthly view. 
                                         However, the Cumulative Sidebar uses Month/Year too. 
                                         Let's sync them or keep them separate? 
                                         The prompt implies the supervisor selects these for the main view.
                                         The Cumulative sidebar had its own form before. We should consolidate IF we want the whole page to reflect one context. 
                                         The prompt says: "when selected month year and employee he should be able to see the whole month attendance... 
                                         and again when selected all he should be able to see todays attendance" --}}
                                    
                                    @if($isMonthlyReport)
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <select name="month" onchange="document.getElementById('attendance-filter-form').submit()"
                                                    class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                                    @foreach(range(1, 12) as $m)
                                                        <option value="{{ $m }}" {{ $reqMonth == $m ? 'selected' : '' }}>
                                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="w-1/2">
                                                 <select name="year" onchange="document.getElementById('attendance-filter-form').submit()"
                                                    class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                                    @for($y = now()->year - 2; $y <= now()->year + 4; $y++)
                                                        <option value="{{ $y }}" {{ $reqYear == $y ? 'selected' : '' }}>
                                                            {{ $y }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                </form>

                                {{-- Summary Cards (4 Columns) --}}
                                <div class="flex flex-row items-stretch gap-3 mb-6 w-full">
                                    <div class="w-1/4 bg-blue-50 rounded-lg p-4 text-center border border-blue-100">
                                        <div class="text-2xl font-bold text-blue-600">{{ $daily_summary['total'] }}</div>
                                        <div class="text-xs font-medium text-blue-600 mt-1">{{ 'Total' }}</div>
                                    </div>
                                    <div class="w-1/4 bg-green-50 rounded-lg p-4 text-center border border-green-100">
                                        <div class="text-2xl font-bold text-green-600">{{ $daily_summary['present'] }}</div>
                                        <div class="text-xs font-medium text-green-600 mt-1">{{ 'Present' }}</div>
                                    </div>
                                    <div class="w-1/4 bg-yellow-50 rounded-lg p-4 text-center border border-yellow-100">
                                        <div class="text-2xl font-bold text-yellow-500">{{ $daily_summary['leave'] }}</div>
                                        <div class="text-xs font-medium text-yellow-600 mt-1">{{ 'On Leave' }}</div>
                                    </div>
                                    <div class="w-1/4 bg-red-50 rounded-lg p-4 text-center border border-red-100">
                                        <div class="text-2xl font-bold text-red-500">{{ $daily_summary['absent'] }}</div>
                                        <div class="text-xs font-medium text-red-500 mt-1">{{ 'Absent' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Scrollable Employees Table --}}
                            <div class="flex-1 overflow-y-auto px-6 relative">
                                <table class="min-w-full border-separate border-spacing-0">
                                    <thead class="bg-slate-50 sticky top-0 z-10">
                                        <tr class="border-b border-slate-100">
                                            <th
                                                class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ $isMonthlyReport ? 'Date' : 'Name' }}</th>
                                            <th
                                                class="px-2 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Status' }}</th>
                                            <th
                                                class="px-2 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Login Time' }}</th>
                                            <th
                                                class="px-2 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Logout Time' }}</th>
                                            <th
                                                class="px-2 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Duration' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        @foreach($daily_records as $rec)
                                            <tr class="{{ str_contains($rec['status'], 'Manual') ? 'bg-blue-50' : '' }}">
                                                <td class="px-2 py-4 text-sm font-medium text-slate-900">
                                                    {{ $isMonthlyReport ? $rec['date'] : $rec['name'] }}</td>
                                                <td class="px-2 py-4 text-center">
                                                    <span
                                                        class="inline-block px-2.5 py-1 text-xs leading-4 font-semibold rounded-full {{ $rec['class'] }}">
                                                        @php
                                                            $displayStatus = $rec['status'] == 'leave' ? 'On Leave' : $rec['status'];
                                                            $subLabel = '';
                                                            
                                                            if (str_contains($displayStatus, '(Manual)')) {
                                                                $displayStatus = 'Present';
                                                                $subLabel = 'Manual';
                                                            } elseif (str_contains($displayStatus, '(Hybrid)')) {
                                                                $displayStatus = 'Present';
                                                                $subLabel = 'Hybrid';
                                                            } elseif ($displayStatus === 'Manual Attendance') {
                                                                $displayStatus = 'Present';
                                                                $subLabel = 'Manual';
                                                            } elseif (str_contains($displayStatus, 'Exempted')) {
                                                                $displayStatus = 'Exempted';
                                                                $subLabel = '9 Hrs';
                                                            }
                                                        @endphp
                                                        {{ $displayStatus }}
                                                        @if($subLabel)
                                                            <br><span class="text-[10px] opacity-75">{{ $subLabel }}</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="px-2 py-4 text-center text-sm text-slate-600">
                                                    @if($rec['login'] !== '-')
                                                        {{ $rec['login'] }}
                                                    @else
                                                        <span class="text-slate-300">&mdash;</span>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-4 text-center text-sm text-slate-600">
                                                    @if($rec['logout'] !== '-')
                                                        {{ $rec['logout'] }}
                                                    @else
                                                        <span class="text-slate-300">&mdash;</span>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-4 text-right text-sm text-slate-600">
                                                    {{ $rec['duration'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Fixed Footer --}}
                            <div class="p-6 pt-4 shrink-0 mt-auto border-t border-slate-50">
                                <a href="{{ route('attendance.export', ['type' => $isMonthlyReport ? 'employee_monthly' : 'team', 'date' => request('date'), 'user_id' => $selectedUserId, 'month' => $reqMonth, 'year' => $reqYear]) }}"
                                    class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                    {{ $isMonthlyReport ? 'Download Monthly Report' : 'Download Daily Report' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT CARD: Cumulative Report --}}
                    <div class="flex-1 flex flex-col min-w-0">
                        <div
                            class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden">
                            <div class="p-6 pb-0 shrink-0">
                                <h2 class="text-lg font-bold text-slate-800 mb-4">{{ 'Cumulative Report' }}</h2>

                                {{-- Dropdown --}}
                                {{-- Dropdown --}}
                                {{-- Dropdown --}}
                                <form action="{{ route(Route::currentRouteName()) }}" method="GET" id="team-cumulative-filter-form" class="mb-6 flex gap-2">
                                    {{-- Preserve daily date and user --}}
                                    @if(request('date'))
                                        <input type="hidden" name="date" value="{{ request('date') }}">
                                    @endif
                                    @if(request('user_id'))
                                        <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                    @endif
                                    
                                    {{-- We should only show these if NOT in monthly report mode, 
                                         OR we sync them. If in monthly report mode, specific month/year is already active. 
                                         Let's allow changing it here too, which updates both views. --}}

                                    <div class="relative w-32">
                                        <select name="month"
                                            onchange="document.getElementById('team-cumulative-filter-form').submit()"
                                            class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ $reqMonth == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="relative w-32">
                                        <select name="year"
                                            onchange="document.getElementById('team-cumulative-filter-form').submit()"
                                            class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                            @for($y = now()->year - 2; $y <= now()->year + 4; $y++)
                                                <option value="{{ $y }}" {{ $reqYear == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </form>

                                {{-- Summary Cards (3 Columns) --}}
                                {{-- Summary Cards (2 Columns) --}}
                                <div class="flex flex-row items-stretch gap-3 mb-8 w-full">
                                    <div class="w-1/2 bg-blue-50 rounded-lg p-4 text-center border border-blue-100">
                                        <div class="text-2xl font-bold text-blue-600">{{ $cumulative_summary['working'] }}
                                        </div>
                                        <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Total Working' }}</div>
                                    </div>
                                    <div
                                        class="w-1/2 bg-white rounded-lg p-4 text-center border border-slate-200 shadow-sm">
                                        <div class="text-2xl font-bold text-slate-800">{{ $cumulative_summary['holidays'] }}
                                        </div>
                                        <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Holidays' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Scrollable Cumulative Table --}}
                            <div class="flex-1 overflow-y-auto px-6 relative">
                                <table class="min-w-full border-separate border-spacing-0">
                                    <thead class="bg-slate-50 sticky top-0 z-10">
                                        <tr class="border-b border-slate-100">
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Name' }}</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Present' }}</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Leave' }}</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Absent' }}</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                {{ 'Late Marks' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        @foreach($cumulative_records as $rec)
                                            <tr>
                                                <td class="px-4 py-4 text-sm font-medium text-slate-900 whitespace-nowrap">
                                                    {{ $rec['name'] }}</td>
                                                <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                                                    {{ $rec['present'] }}</td>
                                                <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                                                    {{ $rec['leave'] }}</td>
                                                <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                                                    {{ $rec['absent'] }}</td>
                                                <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                                                    {{ $rec['late_marks'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Fixed Footer --}}
                            <div class="p-6 pt-4 shrink-0 mt-auto border-t border-slate-50">
                                <a href="{{ route('attendance.export', ['type' => 'team_cumulative', 'month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}"
                                    class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                    {{ 'Download Attendance' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <x-manual-attendance-modal />
    <x-detailed-attendance-modal :users="$users" />
@endsection