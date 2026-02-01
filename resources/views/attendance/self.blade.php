@extends('layouts.app')

@section('content')
@php
    $role = $role ?? 'admin';
@endphp

<div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">
    {{-- Sidebar --}}
    <x-sidebar :role="$role" />

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
        {{-- Top Header --}}
        <div class="flex items-center justify-between px-8 py-6 bg-white shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="history.back()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $role === 'admin' ? 'Attendance' : 'My Attendance' }}</h1>
                    <p class="text-slate-500 text-sm mt-1">{{ $role === 'admin' ? 'Track daily and cumulative attendance records.' : 'Track your daily and cumulative attendance records.' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('logout') }}" class="text-slate-500 hover:text-red-600 font-medium text-sm transition-colors">
                    {{ 'Sign Out' }}
                </a>

            </div>
        </div>

        {{-- Content Scroll Area --}}
        <div class="flex-1 flex flex-col overflow-hidden px-8 pb-8">
            <div class="flex-1 flex flex-col lg:flex-row gap-8 min-h-0">
                
                {{-- LEFT CARD: Daily Report --}}
                <div class="flex-1 flex flex-col min-w-0">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full relative">
                        <div class="p-6 pb-24 flex flex-col flex-1 min-h-0">
                            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ 'Daily Report' }}</h2>
                            
                            {{-- Date Selector --}}
                            <!-- Backend Ready: Ensure 'name="date"' allows this to be submitted as a form filter -->
                            <form action="" method="GET" id="daily-date-form">
                                <div class="relative mb-6" x-data="{ 
                                    date: new Date('{{ $currentViewDate }}' + 'T00:00:00'),
                                    formattedDate: '',
                                    inputDate: '{{ $currentViewDate }}',
                                    init() {
                                        this.updateFormattedDate(this.date);
                                        const picker = flatpickr(this.$refs.pickerTrigger, {
                                            defaultDate: this.date,
                                            dateFormat: 'Y-m-d',
                                            maxDate: '{{ $role === 'employee' ? now()->subDay()->format('Y-m-d') : now()->format('Y-m-d') }}',
                                            onChange: (selectedDates, dateStr) => {
                                                this.date = selectedDates[0];
                                                this.inputDate = dateStr;
                                                this.updateFormattedDate(this.date);
                                                // Submit form after short delay to ensure model updates
                                                setTimeout(() => document.getElementById('daily-date-form').submit(), 50);
                                            }
                                        });
                                    },
                                    updateFormattedDate(d) {
                                        this.formattedDate = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                                    }
                                }">
                                    <div x-ref="pickerTrigger" class="relative flex items-center w-full sm:w-auto border border-slate-200 rounded-lg px-4 py-2.5 bg-slate-50/50 cursor-pointer hover:bg-slate-100 transition-colors">
                                        <svg class="h-5 w-5 text-slate-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-slate-700 font-medium text-sm whitespace-nowrap">Date: <span class="text-slate-900" x-text="formattedDate"></span></span>
                                        {{-- Hidden input for form submission --}}
                                        <input type="hidden" name="date" x-model="inputDate">
                                    </div>
                                </div>
                            </form>

                            {{-- Summary Cards --}}
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-green-50 rounded-lg p-4 text-center border border-green-100">
                                    <div class="text-2xl font-bold text-green-600">{{ $daily_summary['present'] }}</div>
                                    <div class="text-xs font-medium text-green-600 mt-1">{{ 'Present' }}</div>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-4 text-center border border-yellow-100">
                                    <div class="text-2xl font-bold text-yellow-500">{{ $daily_summary['leave'] }}</div>
                                    <div class="text-xs font-medium text-yellow-500 mt-1">{{ 'On Leave' }}</div>
                                </div>
                                <div class="bg-red-50 rounded-lg p-4 text-center border border-red-100">
                                    <div class="text-2xl font-bold text-red-500">{{ $daily_summary['absent'] }}</div>
                                    <div class="text-xs font-medium text-red-500 mt-1">{{ 'Absent' }}</div>
                                </div>
                            </div>

                            {{-- Attendance Table --}}
                            <div class="flex-1 overflow-auto min-h-0 rounded-lg border border-slate-100 mb-6">
                                <table class="min-w-full divide-y divide-slate-100">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ 'Name' }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ 'Status' }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ 'Login Time' }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ 'Logout Time' }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ 'Duration' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        @forelse($daily_records as $record)
                                        <tr class="{{ str_contains($record['status'], '(Manual)') ? 'bg-blue-50' : '' }}">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $record['name'] }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $record['class'] }}">
                                                    {{ $record['status'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600">{{ $record['login_time'] }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600">{{ $record['logout_time'] }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600">{{ $record['duration'] }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-center text-sm text-slate-500">{{ 'No records found for this date.' }}</td>
                                        </tr>
                                        @endforelse
                                        
                                        {{-- Spacer Rows only if we have records to maintain height consistency if desired --}}

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-auto absolute bottom-0 left-0 right-0 p-6 pt-0 bg-white rounded-b-xl">
                            <a href="{{ route('attendance.export', ['type' => 'self_daily', 'date' => request('date') ?? now()->format('Y-m-d')]) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                {{ 'Download Daily Report' }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- RIGHT CARD: Cumulative Report --}}
                <div class="flex-1 flex flex-col min-w-0">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full relative">
                        <div class="p-6 pb-24 flex flex-col flex-1 min-h-0">
                            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ 'Cumulative Report' }}</h2>
                            
                            {{-- Dropdown --}}
                            <!-- Backend Ready: 'name="filter"' for selection -->
                            {{-- Dropdown --}}
                            <!-- Month/Year Filter -->
                            <form action="" method="GET" id="cumulative-filter-form" class="mb-6 flex gap-2">
                                {{-- Preserve daily date if needed or let it reset --}}
                                @if(request('date'))
                                <input type="hidden" name="date" value="{{ request('date') }}">
                                @endif

                                <div class="relative w-32">
                                    <select name="month" onchange="document.getElementById('cumulative-filter-form').submit()" 
                                        class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ (request('month') == $m || (!request('month') && now()->month == $m)) ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="relative w-24">
                                     <select name="year" onchange="document.getElementById('cumulative-filter-form').submit()" 
                                        class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                                        @for($y = now()->year - 2; $y <= now()->year + 4; $y++)
                                            <option value="{{ $y }}" {{ (request('year') == $y || (!request('year') && now()->year == $y)) ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </form>

                            {{-- Summary Cards --}}
                            {{-- Summary Cards --}}
                            <div class="flex gap-4 mb-8 overflow-x-auto pb-2">
                                <div class="flex-1 min-w-[120px] bg-blue-50 rounded-lg p-3 text-center border border-blue-100 flex flex-col justify-center h-24">
                                    <div class="text-xl font-bold text-blue-600">{{ $cumulative_summary['total_working'] }}</div>
                                    <div class="text-[10px] uppercase font-bold text-blue-600 mt-1 leading-tight">Total Working<br>(Excl. Sun/Holidays)</div>
                                </div>
                                <div class="flex-1 min-w-[120px] bg-green-50 rounded-lg p-3 text-center border border-green-100 flex flex-col justify-center h-24">
                                    <div class="text-xl font-bold text-green-600">{{ $cumulative_summary['my_working'] }}</div>
                                    <div class="text-[10px] uppercase font-bold text-green-600 mt-1 leading-tight">My Working<br>(Present)</div>
                                </div>
                                <div class="flex-1 min-w-[120px] bg-yellow-50 rounded-lg p-3 text-center border border-yellow-100 flex flex-col justify-center h-24">
                                    <div class="text-xl font-bold text-yellow-600">{{ $cumulative_summary['leaves'] }}</div>
                                    <div class="text-[10px] uppercase font-bold text-yellow-600 mt-1 leading-tight">Leaves<br>(Approved)</div>
                                </div>
                                <div class="flex-1 min-w-[120px] bg-purple-50 rounded-lg p-3 text-center border border-slate-200 shadow-sm flex flex-col justify-center h-24">
                                    <div class="text-xl font-bold text-slate-800">{{ $cumulative_summary['holidays'] }}</div>
                                    <div class="text-[10px] uppercase font-bold text-slate-600 mt-1 leading-tight">Holidays<br>(INCL Sundays)</div>
                                </div>
                            </div>

                            {{-- Cumulative Table --}}
                            <div class="mb-6 flex-1 overflow-auto min-h-0">
                                <div class="grid grid-cols-5 text-xs font-semibold text-slate-500 text-center mb-4 uppercase tracking-wider">
                                    <div class="text-left pl-2">{{ 'Name' }}</div>
                                    <div>{{ 'Present' }}</div>
                                    <div>{{ 'Leave' }}</div>
                                    <div>{{ 'Absent' }}</div>
                                    <div class="text-right pr-2">{{ 'Working Duration' }}</div>
                                </div>
                                <div class="space-y-4">
                                    @forelse($cumulative_records as $record)
                                    <div class="grid grid-cols-5 text-sm text-slate-600 text-center items-center py-2 border-b border-transparent hover:bg-slate-50 rounded-lg transition-colors">
                                        <div class="font-medium text-slate-900 text-left pl-2">{{ $record['name'] }}</div>
                                        <div>{{ $record['present'] }}</div>
                                        <div>{{ $record['leave'] }}</div>
                                        <div>{{ $record['absent'] }}</div>
                                        <div class="text-right pr-2">{{ $record['working_duration'] }}</div>
                                    </div>
                                    @empty
                                    <div class="text-center text-sm text-slate-500 py-4">{{ 'No cumulative records found.' }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Spacer --}}


                        {{-- Footer Action --}}
                        {{-- Footer Action --}}
                        <div class="absolute bottom-0 left-0 right-0 p-6 pt-0 bg-white rounded-b-xl">
                            <hr class="border-slate-300 w-full mb-6">
                            <a href="{{ route('attendance.export', ['type' => 'self', 'month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                {{ 'Download Attendance' }}
                            </a>
                        </div>
                </div>
            </div>
            </div> {{-- Close Flex Row --}}


        </div>
    </main>
</div>
    {{-- Force Tailwind to include these classes used dynamically in Controller --}}
     <div class="hidden bg-purple-100 text-purple-800 bg-green-100 text-green-800 bg-red-100 text-red-800 bg-yellow-100 text-yellow-800 bg-gray-100 text-gray-800"></div>
@endsection

