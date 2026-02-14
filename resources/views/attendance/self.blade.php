@extends('layouts.app')

@section('content')
    @php
        $role = $role ?? 'admin';
    @endphp

    <div  class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">
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
                        <h1 class="text-2xl font-bold text-slate-800">
                            {{ $role === 'admin' ? 'Attendance' : 'My Attendance' }}
                        </h1>
                        <p class="text-slate-500 text-sm mt-1">
                            {{ $role === 'admin' ? 'Track daily and cumulative attendance records.' : 'Track your daily and cumulative attendance records.' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('logout') }}"
                        class="text-slate-500 hover:text-red-600 font-medium text-sm transition-colors">
                        {{ 'Sign Out' }}
                    </a>

                </div>
            </div>

            {{-- Content Scroll Area --}}
            <div class="flex-1 flex flex-col overflow-hidden px-8 pb-8">
                <form action="" method="GET" id="month-filter-form" class="mt-4 mb-4 flex justify-end gap-2">
                    <div class="relative w-32">
                        <select name="month"
                            onchange="document.getElementById('month-filter-form').submit()"
                            class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (request('month') == $m || (!request('month') && now()->month == $m)) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative w-32">
                        <select name="year"
                            onchange="document.getElementById('month-filter-form').submit()"
                            class="block w-full pl-3 pr-8 py-2 text-sm border border-slate-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg text-slate-600 bg-white shadow-sm cursor-pointer">
                            @for($y = now()->year - 2; $y <= now()->year + 4; $y++)
                                <option value="{{ $y }}" {{ (request('year') == $y || (!request('year') && now()->year == $y)) ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </form>

                <div class="flex-1 flex flex-col lg:flex-row gap-8 min-h-0">

                    {{-- LEFT CARD: Daily Report --}}
                    <div class="flex-1 flex flex-col min-w-0">
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full relative">
                            <div class="p-6 pb-24 flex flex-col flex-1 min-h-0">
                                <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ 'Monthly Report' }}</h2>

                                {{-- Date Selector --}}
                                <!-- Backend Ready: Ensure 'name="date"' allows this to be submitted as a form filter -->
                                {{-- Month/Year Filter --}}


                                    {{-- Summary Cards --}}


                                    {{-- Attendance Table --}}
                                    <div class="flex-1 overflow-auto min-h-0 rounded-lg border border-slate-100 mb-6">
                                        <table class="min-w-full divide-y divide-slate-100">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th
                                                        class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                        {{ 'Date' }}</th>
                                                    <th
                                                        class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                        {{ 'Status' }}</th>
                                                    <th
                                                        class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                        {{ 'Login Time' }}</th>
                                                    <th
                                                        class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                        {{ 'Logout Time' }}</th>
                                                    <th
                                                        class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                        {{ 'Duration' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-slate-100">
                                                @forelse($daily_records as $record)
                                                    <tr
                                                        class="{{ str_contains($record['status'], '(Manual)') ? 'bg-blue-50' : '' }}">
                                                        <td class="px-2 py-4 text-sm font-medium text-slate-900">
                                                            {{ $record['date'] }}</td>
                                                        <td class="px-2 py-4">
                                                            <span
                                                                class="px-2.5 py-1 inline-block text-xs leading-4 font-semibold rounded-full text-center {{ $record['class'] }}">
                                                                @php
                                                                    $displayStatus = $record['status'];
                                                                    $subLabel = '';
                                                                    
                                                                    if (str_contains($displayStatus, '(Manual)')) {
                                                                        $displayStatus = 'Present';
                                                                        $subLabel = 'Manual';
                                                                    } elseif (str_contains($displayStatus, '(Hybrid)')) {
                                                                        $displayStatus = 'Present';
                                                                        $subLabel = 'Hybrid';
                                                                    } elseif (str_contains($displayStatus, 'Exempted')) {
                                                                        $displayStatus = 'Exempted';
                                                                        $subLabel = '9 Hrs';
                                                                    }
                                                                @endphp
                                                                {{ $displayStatus }}
                                                                @if($subLabel)
                                                                    <br><span class="text-[10px] opacity-80">{{ $subLabel }}</span>
                                                                @endif
                                                            </span>
                                                        </td>
                                                        <td class="px-2 py-4 text-sm text-slate-600">
                                                            {{ $record['login_time'] }}</td>
                                                        <td class="px-2 py-4 text-sm text-slate-600">
                                                            {{ $record['logout_time'] }}</td>
                                                        <td class="px-2 py-4 text-sm text-slate-600">
                                                            {{ $record['duration'] }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="px-4 py-4 text-center text-sm text-slate-500">
                                                            {{ 'No records found for this month.' }}</td>
                                                    </tr>
                                                @endforelse

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-auto absolute bottom-0 left-0 right-0 p-6 pt-0 bg-white rounded-b-xl">
                                    <a href="{{ route('attendance.export', ['type' => 'self', 'month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}"
                                        class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                        {{ 'Download Monthly Report' }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT CARD: Cumulative Report --}}
                        <div class="flex-1 flex flex-col min-w-0">
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full relative">
                                <div class="p-6 pb-24 flex flex-col flex-1 min-h-0">
                                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ 'Cumulative Report' }}</h2>

                                    {{-- Unified Filter Form (Controls whole page now) --}}
                                    {{-- Filters moved to Daily Report --}}

                                    {{-- Summary Cards --}}
                                    {{-- Summary Cards --}}
                                    <div class="flex gap-2 mb-8">
                                        <div
                                            class="flex-1 min-w-0 bg-blue-50 rounded-lg p-2 text-center border border-blue-100 flex flex-col justify-center h-24">
                                            <div class="text-xl font-bold text-blue-600">
                                                {{ $cumulative_summary['total_working'] }}</div>
                                            <div class="text-[10px] uppercase font-bold text-blue-600 mt-1 leading-tight">Total
                                                Working<br></div>
                                        </div>
                                        <div
                                            class="flex-1 min-w-0 bg-green-50 rounded-lg p-2 text-center border border-green-100 flex flex-col justify-center h-24">
                                            <div class="text-xl font-bold text-green-600">
                                                {{ $cumulative_summary['my_working'] }}</div>
                                            <div class="text-[10px] uppercase font-bold text-green-600 mt-1 leading-tight">My
                                                Working<br>(Present)</div>
                                        </div>
                                        <div
                                            class="flex-1 min-w-0 bg-yellow-50 rounded-lg p-2 text-center border border-yellow-100 flex flex-col justify-center h-24">
                                            <div class="text-xl font-bold text-yellow-600">{{ $cumulative_summary['leaves'] }}
                                            </div>
                                            <div class="text-[10px] uppercase font-bold text-yellow-600 mt-1 leading-tight">
                                                Leaves<br>(Approved)</div>
                                        </div>
                                        <div
                                            class="flex-1 min-w-0 bg-red-50 rounded-lg p-2 text-center border border-red-100 flex flex-col justify-center h-24">
                                            <div class="text-xl font-bold text-red-600">{{ $cumulative_summary['absent'] }}
                                            </div>
                                            <div class="text-[10px] uppercase font-bold text-red-600 mt-1 leading-tight">
                                                Absent<br></div>
                                        </div>
                                    </div>

                                    {{-- Cumulative Table --}}
                                    <div class="mb-6 flex-1 overflow-auto min-h-0">
                                        <table class="w-full text-xs font-semibold text-slate-500 text-center mb-4 tracking-wider">
                                            <thead class="uppercase">
                                                <tr class="border-b border-slate-100">
                                                    <th class="py-2 text-left pl-2">{{ 'Name' }}</th>
                                                    <th class="py-2">{{ 'Present' }}</th>
                                                    <th class="py-2">{{ 'Leave' }}</th>
                                                    <th class="py-2">{{ 'Late Marks' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm text-slate-600 font-normal tracking-normal">
                                                @forelse($cumulative_records as $record)
                                                    <tr class="border-b border-transparent hover:bg-slate-50 transition-colors">
                                                        <td class="py-3 text-left pl-2 font-medium text-slate-900">
                                                            {{ $record['name'] }}</td>
                                                        <td class="py-3">{{ $record['present'] }}</td>
                                                        <td class="py-3">{{ $record['leave'] }}</td>
                                                        <td class="py-3">{{ $record['late_marks'] }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4 text-slate-500">
                                                            {{ 'No cumulative records found.' }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Spacer --}}


                                {{-- Footer Action --}}
                                {{-- Footer Action --}}
                                <div class="absolute bottom-0 left-0 right-0 p-6 pt-0 bg-white rounded-b-xl">
                                    <hr class="border-slate-300 w-full mb-6">
                                    <a href="{{ route('attendance.export', ['type' => 'self_cumulative', 'month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}"
                                        class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
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
        <div
            class="hidden bg-purple-100 text-purple-800 bg-green-100 text-green-800 bg-red-100 text-red-800 bg-yellow-100 text-yellow-800 bg-gray-100 text-gray-800">
        </div>
@endsection