@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-slate-50 overflow-hidden" x-data="{ sidebarOpen: true }">
    <x-sidebar role="admin" />

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-4">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Admin Leave Report</h1>
                    <p class="text-slate-400 text-sm mt-1">View monthly leave details for each employee.</p>
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('leaves.admin-report') }}" class="flex flex-row flex-wrap items-center justify-start gap-4 mt-2">
                    <!-- User Select -->
                    <div class="relative min-w-[240px]">
                         @php
                            $selectedUserObj = $users->find($selectedUserId);
                            $initials = $selectedUserObj ? substr($selectedUserObj->full_name, 0, 2) : 'EM';
                        @endphp
                        
                        <div class="relative flex items-center h-10">
                            <!-- Avatar Circle -->
                            <div class="absolute left-1.5 z-10 w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold pointer-events-none">
                                {{ strtoupper($initials) }}
                            </div>
                            
                            <!-- Select Input -->
                            <select name="user_id" onchange="this.form.submit()" 
                                    style="background-color: #F1F5F9; padding-left: 2.75rem; -webkit-appearance: none; -moz-appearance: none; appearance: none;"
                                    class="w-full h-full border-none text-slate-700 pr-10 rounded-lg focus:outline-none focus:ring-0 cursor-pointer font-medium text-sm">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Custom Arrow Icon -->
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Year Select -->
                    <div class="relative w-32">
                        <div class="relative flex items-center h-10">
                            <select name="year" onchange="this.form.submit()" 
                                    style="background-color: #F1F5F9; -webkit-appearance: none; -moz-appearance: none; appearance: none;"
                                    class="w-full h-full border-none text-slate-700 px-4 pr-10 rounded-lg focus:outline-none focus:ring-0 cursor-pointer font-medium text-sm">
                                @for($y = 2024; $y <= now()->addYear()->year; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            
                            <!-- Custom Arrow Icon -->
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </form>

                @if($selectedUserObj)
                <!-- Statistics Cards -->
                <div class="grid grid-cols-3 gap-6 w-full">
                    <!-- Total Working Days -->
                    <div style="background-color: #E0EAFF;" class="flex-1 rounded-xl p-6 flex items-center gap-5 border border-blue-100">
                        <div class="w-12 h-12 rounded-xl bg-blue-100/50 text-blue-600 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-blue-600 mb-0.5">{{ $totalWorked }}</div>
                            <p class="text-xs text-blue-600/70 font-medium">Total Working Days</p>
                        </div>
                    </div>

                    <!-- Days Present -->
                    <div style="background-color: #E0F2E9;" class="flex-1 rounded-xl p-6 flex items-center gap-5 border border-green-100">
                        <div class="w-12 h-12 rounded-xl bg-green-100/50 text-green-600 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-green-700 mb-0.5">{{ $totalPresent }}</div>
                            <p class="text-xs text-green-700/70 font-medium">Days Present</p>
                        </div>
                    </div>

                    <!-- Days on Leave -->
                    <div style="background-color: #FBF8EC;" class="flex-1 rounded-xl p-6 flex flex-col justify-center items-center text-center border border-yellow-100">
                        <div class="text-xl font-bold text-[#92825B] mb-1">
                            {{ $totalPaidLeave }}Paid - {{ $totalUnpaidLeave }}Unpaid
                        </div>
                        <p class="text-sm text-[#92825B]/70 font-medium pt-1">Days on Leave</p>
                    </div>
                </div>

                <!-- Monthly Details Table -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                     <div class="space-y-4 w-full">
                        <h3 class="text-lg font-bold text-slate-700">Monthly Leave Details</h3>
                        <!-- Using inline style for table container background to be safe -->
                        <div style="background-color: #F8F9FB;" class="rounded-xl overflow-hidden border border-slate-100">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead style="background-color: #EEEEF2;" class="text-slate-600 font-medium border-b border-slate-200">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold w-1/4 whitespace-nowrap">Month</th>
                                            <th class="px-6 py-4 font-semibold w-1/4 whitespace-nowrap">Worked Days</th>
                                            <th class="px-6 py-4 font-semibold w-1/4 whitespace-nowrap">Paid Leave</th>
                                            <th class="px-6 py-4 font-semibold w-1/4 whitespace-nowrap">Unpaid Leave</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($monthlyStats as $stat)
                                        <tr class="hover:bg-slate-100/50 transition-colors">
                                            <td class="px-6 py-4 text-slate-500 font-medium">{{ $stat['month_name'] }}</td>
                                            <td class="px-6 py-4 text-slate-500">{{ $stat['working_days'] }} Days</td>
                                            <td class="px-6 py-4 text-slate-500">{{ $stat['paid_leave'] > 0 ? $stat['paid_leave'] : '0' }}</td>
                                            <td class="px-6 py-4 text-slate-500">{{ $stat['unpaid_leave'] > 0 ? $stat['unpaid_leave'] : '0' }}</td>
                                        </tr>
                                        @endforeach
                                        
                                        <!-- Totals Row -->
                                        <tr class="bg-gray-50/50 font-semibold text-slate-700 border-t border-slate-200">
                                            <td class="px-6 py-4">Totals</td>
                                            <td class="px-6 py-4">{{ $totalWorked }} Days</td>
                                            <td class="px-6 py-4">{{ $totalPaidLeave }} Days</td>
                                            <td class="px-6 py-4">{{ $totalUnpaidLeave }} Day</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Mock -->
                            <div class="flex items-center justify-between text-xs text-slate-400 p-4 border-t border-slate-100">
                                <span>1 of {{ count($monthlyStats) }} row(s) selected.</span>
                                <div class="flex items-center gap-6">
                                    <span>Rows per page: 10</span>
                                    <div class="flex items-center gap-2">
                                        <span>page 1 of 1</span>
                                        <div class="flex gap-1 ml-2">
                                            <button class="p-1 hover:bg-slate-200 rounded"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                                            <span class="text-slate-300">1</span>
                                            <button class="p-1 hover:bg-slate-200 rounded"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    <div class="text-center py-12 text-slate-400">
                        Select a user to view report.
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection
