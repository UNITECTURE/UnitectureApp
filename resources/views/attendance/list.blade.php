@extends('layouts.app')

@section('content')
@php
    $role = $role ?? 'admin';
    $scope = $scope ?? 'all';
@endphp


<div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">
    
    {{-- Sidebar --}}
    <x-sidebar :role="$role" />

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
        {{-- Top Header --}}
        <div class="flex items-center justify-between px-8 py-6 bg-white shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ 'Attendance' }}</h1>
                <p class="text-slate-500 text-sm mt-1">
                    {{ 'Track daily and cumulative attendance records.' }}
                </p>
            </div>
            
            {{-- Optional: Action Buttons if needed by design, though image doesn't show them explicitly --}}
        </div>

        {{-- Content Scroll Area --}}
        <div class="flex-1 flex flex-col overflow-hidden px-8 pb-8">
            <div class="flex-1 flex flex-col lg:flex-row gap-6 min-h-0">
                
                {{-- LEFT CARD: Daily Report --}}
                <div class="flex-1 flex flex-col min-w-0">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden">
                        {{-- Fixed Header Section --}}
                        <div class="p-6 pb-0 shrink-0">
                            <h2 class="text-lg font-bold text-slate-800 mb-4">{{ 'Daily Report' }}</h2>
                            
                            {{-- Date Selector --}}
                            <div class="relative mb-6">
                                <div x-data x-init="
                                    $refs.picker.value = '{{ request('date', date('D, M j')) }}'; 
                                    flatpickr($refs.picker, { 
                                        dateFormat: 'D, M j', 
                                        defaultDate: '{{ request('date', date('Y-m-d')) }}',
                                        onChange: function(selectedDates, dateStr, instance) {
                                            const date = instance.formatDate(selectedDates[0], 'Y-m-d');
                                            const params = new URLSearchParams(window.location.search);
                                            params.set('date', date);
                                            window.location.search = params.toString();
                                        }
                                    })" class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 flex items-center w-full cursor-pointer hover:bg-slate-100 transition-colors">
                                    <svg class="h-4 w-4 text-slate-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <input x-ref="picker" type="text" class="bg-transparent border-none text-slate-600 font-medium text-xs p-0 focus:ring-0 w-full cursor-pointer" readonly>
                                </div>
                            </div>

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
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Name' }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Status' }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Login Time' }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Logout Time' }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Duration' }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($daily_records as $rec)
                                    <tr class="{{ str_contains($rec['status'], '(Manual)') ? 'bg-blue-50' : '' }}">
                                        <td class="px-4 py-4 text-sm font-medium text-slate-900 whitespace-nowrap">{{ $rec['name'] }}</td>
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-1 text-xs leading-5 font-semibold rounded-full {{ $rec['class'] }}">
                                                {{ strtolower($rec['status']) == 'leave' ? 'On Leave' : $rec['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                                            @if($rec['login'] !== '-')
                                             {{ $rec['login'] }}
                                            @else
                                             <span class="text-slate-300">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                                            @if($rec['logout'] !== '-')
                                             {{ $rec['logout'] }}
                                            @else
                                             <span class="text-slate-300">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-right text-sm text-slate-600 whitespace-nowrap">{{ $rec['duration'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Fixed Footer --}}
                        <div class="p-6 pt-4 shrink-0 mt-auto border-t border-slate-50">
                             <a href="{{ route('attendance.export', ['type' => 'team', 'date' => request('date')]) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                {{ 'Download Attendance' }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- RIGHT CARD: Cumulative Report --}}
                <div class="flex-1 flex flex-col min-w-0">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden">
                        <div class="p-6 pb-0 shrink-0">
                            <h2 class="text-lg font-bold text-slate-800 mb-4">{{ 'Cumulative Report' }}</h2>
                            
                            {{-- Dropdown --}}
                            {{-- Dropdown --}}
                            <form action="" method="GET" id="team-cumulative-filter-form" class="mb-6 flex gap-2">
                                {{-- Preserve daily date --}}
                                @if(request('date'))
                                <input type="hidden" name="date" value="{{ request('date') }}">
                                @endif

                                <div class="relative w-32">
                                    <select name="month" onchange="document.getElementById('team-cumulative-filter-form').submit()" 
                                        class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ (request('month') == $m || (!request('month') && now()->month == $m)) ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="relative w-24">
                                     <select name="year" onchange="document.getElementById('team-cumulative-filter-form').submit()" 
                                        class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                        @for($y = now()->year - 2; $y <= now()->year + 4; $y++)
                                            <option value="{{ $y }}" {{ (request('year') == $y || (!request('year') && now()->year == $y)) ? 'selected' : '' }}>
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
                                    <div class="text-2xl font-bold text-blue-600">{{ $cumulative_summary['working'] }}</div>
                                    <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Total Working' }}</div>
                                </div>
                                <div class="w-1/2 bg-white rounded-lg p-4 text-center border border-slate-200 shadow-sm">
                                    <div class="text-2xl font-bold text-slate-800">{{ $cumulative_summary['holidays'] }}</div>
                                    <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Holidays' }}</div>
                                </div>
                            </div>
                        </div>

                            {{-- Scrollable Cumulative Table --}}
                            <div class="flex-1 overflow-y-auto px-6 relative">
                                <table class="min-w-full border-separate border-spacing-0">
                                    <thead class="bg-slate-50 sticky top-0 z-10">
                                        <tr class="border-b border-slate-100">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Name' }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Present' }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Leave' }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Absent' }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">{{ 'Working Duration' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        @foreach($cumulative_records as $rec)
                                        <tr>
                                            <td class="px-4 py-4 text-sm font-medium text-slate-900 whitespace-nowrap">{{ $rec['name'] }}</td>
                                            <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">{{ $rec['present'] }}</td>
                                            <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">{{ $rec['leave'] }}</td>
                                            <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">{{ $rec['absent'] }}</td>
                                            <td class="px-4 py-4 text-right text-sm text-slate-600 whitespace-nowrap">{{ $rec['duration'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        
                        {{-- Fixed Footer --}}
                        <div class="p-6 pt-4 shrink-0 mt-auto border-t border-slate-50">
                             <a href="{{ route('attendance.export', ['type' => 'team_cumulative', 'month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
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
@endsection
