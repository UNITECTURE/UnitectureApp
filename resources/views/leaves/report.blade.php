@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="{ sidebarOpen: true }">
    <x-sidebar :role="'admin'" />

    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
        {{-- Header --}}
        <div class="px-8 py-6 bg-white shrink-0 border-b border-slate-100">
            <h1 class="text-2xl font-bold text-slate-800">Admin Leave Report</h1>
            <p class="text-slate-500 text-sm mt-1">View monthly leave details for each employee.</p>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto space-y-8">
                
                {{-- Filters --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <form method="GET" action="{{ route('leaves.report') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="w-full">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Employee</label>
                            <select name="user_id" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="this.form.submit()">
                                <option value="" disabled {{ !$selectedUser ? 'selected' : '' }}>Select Employee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ ($selectedUser && $selectedUser->id == $user->id) ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Year</label>
                            <select name="year" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="this.form.submit()">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                         <div class="w-full">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Month</label>
                            <select name="month" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="this.form.submit()">
                                <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>All Months</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="w-full pb-0.5">
                            @if($selectedUser)
                                <a href="{{ route('leaves.export', ['user_id' => $selectedUser->id, 'year' => $selectedYear, 'month' => $selectedMonth]) }}" 
                                   class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                   style="background-color: #2563eb !important; color: #ffffff !important;">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Report
                                </a>
                            @else
                                <div class="w-full h-[38px]"></div> {{-- Spacer to keep alignment --}}
                            @endif
                        </div>
                    </form>
                </div>

                @if($selectedUser)
                    {{-- Summary Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Total Working Days --}}
                        <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 flex flex-col justify-center">
                             <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 text-blue-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <span class="text-xs font-bold text-blue-500 uppercase tracking-wider">Working</span>
                            </div>
                            <div class="text-3xl font-bold text-blue-900">{{ $totals['working_days'] }}</div>
                            <div class="text-xs text-blue-600 mt-1">Total Working Days</div>
                        </div>

                        {{-- Days Present --}}
                        <div class="bg-green-50 rounded-xl p-6 border border-green-100 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0 text-green-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-xs font-bold text-green-500 uppercase tracking-wider">Present</span>
                            </div>
                            <div class="text-3xl font-bold text-green-900">{{ $totals['present'] }}</div>
                             <div class="text-xs text-green-600 mt-1">Total Days Present</div>
                        </div>

                        {{-- Paid Leave --}}
                         <div class="bg-purple-50 rounded-xl p-6 border border-purple-100 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center shrink-0 text-purple-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Paid Leave</span>
                            </div>
                            <div class="text-3xl font-bold text-purple-900">{{ $totals['paid_leave'] }}</div>
                             <div class="text-xs text-purple-600 mt-1">Total Paid Leaves</div>
                        </div>

                        {{-- Unpaid Leave --}}
                        <div class="bg-red-50 rounded-xl p-6 border border-red-100 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0 text-red-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <span class="text-xs font-bold text-red-600 uppercase tracking-wider">Unpaid Leave</span>
                            </div>
                            <div class="text-3xl font-bold text-red-900">{{ $totals['unpaid_leave'] }}</div>
                             <div class="text-xs text-red-600 mt-1">Total Unpaid Leaves</div>
                        </div>
                    </div>

                    {{-- Monthly Details Table --}}
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-lg font-bold text-slate-800">Monthly Leave Details</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold">Month</th>
                                        <th class="px-6 py-4 font-semibold">Worked Days</th>
                                        <th class="px-6 py-4 font-semibold">Paid Leave</th>
                                        <th class="px-6 py-4 font-semibold">Unpaid Leave</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($monthlyData as $data)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $data['month'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $data['present'] }} Days</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $data['paid_leave'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $data['unpaid_leave'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-20 bg-white rounded-xl border border-slate-200 border-dashed">
                        <div class="text-slate-400 mb-4">
                            <svg class="w-16 h-16 mx-auto opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900">Select an Employee</h3>
                        <p class="text-slate-500 mt-1">Please select an employee and year to view the leave report.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection
