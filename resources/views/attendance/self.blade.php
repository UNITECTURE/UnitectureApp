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
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $role === 'admin' ? 'Attendance' : 'My Attendance' }}</h1>
                <p class="text-slate-500 text-sm mt-1">{{ $role === 'admin' ? 'Track daily and cumulative attendance records.' : 'Track your daily and cumulative attendance records.' }}</p>
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
                                    date: new Date('{{ request('date') ? request('date').'T00:00:00' : now()->format('Y-m-d').'T00:00:00' }}'),
                                    formattedDate: '',
                                    inputDate: '{{ request('date') ?? now()->format('Y-m-d') }}',
                                    init() {
                                        this.updateFormattedDate(this.date);
                                        const picker = flatpickr(this.$refs.pickerTrigger, {
                                            defaultDate: this.date,
                                            dateFormat: 'Y-m-d',
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
                            <a href="{{ route('attendance.export', ['type' => 'self', 'filter' => request('filter')]) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
                                {{ 'Download Attendance' }}
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
                            <form action="" method="GET" id="cumulative-filter-form">
                                <input type="hidden" name="filter" id="cumulative-filter-input" value="this_month">
                                <div class="mb-6 relative w-40" x-data="{ 
                                    open: false, 
                                    selected: 'This Month',
                                    init() {
                                        const urlParams = new URLSearchParams(window.location.search);
                                        const filter = urlParams.get('filter');
                                        if(filter === 'last_month') { 
                                            this.selected = 'Last Month'; 
                                            document.getElementById('cumulative-filter-input').value = 'last_month'; 
                                        }
                                    },
                                    select(value, label) {
                                        this.selected = label;
                                        this.open = false;
                                        document.getElementById('cumulative-filter-input').value = value;
                                        document.getElementById('cumulative-filter-form').submit();
                                    }
                                }">
                                    <button @click="open = !open" @click.outside="open = false" type="button" class="flex items-center justify-between w-full text-slate-700 border border-slate-300 hover:border-slate-400 px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm bg-white transition-all duration-200">
                                        <span x-text="selected">This Month</span>
                                        <svg class="h-4 w-4 text-slate-500 ml-2 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg py-1" style="display: none;">
                                        
                                        <div @click="select('this_month', 'This Month')" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">{{ 'This Month' }}</div>
                                        <div @click="select('last_month', 'Last Month')" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">{{ 'Last Month' }}</div>
                                    </div>
                                </div>
                            </form>

                            {{-- Summary Cards --}}
                            <div class="grid grid-cols-3 gap-4 mb-8">
                                <div class="bg-blue-50 rounded-lg p-4 text-center border border-blue-100">
                                    <div class="text-2xl font-bold text-blue-600">{{ $cumulative_summary['total_days'] }}</div>
                                    <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Total Days' }}</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4 text-center border border-green-100">
                                    <div class="text-2xl font-bold text-green-600">{{ $cumulative_summary['working'] }}</div>
                                    <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Working' }}</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 text-center border border-slate-200 shadow-sm">
                                    <div class="text-2xl font-bold text-slate-800">{{ $cumulative_summary['holidays'] }}</div>
                                    <div class="text-xs font-medium text-slate-600 mt-1">{{ 'Holidays' }}</div>
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
                            <a href="{{ route('attendance.export', ['type' => 'self', 'filter' => request('filter')]) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg text-sm transition-colors shadow-sm hover:shadow-md text-center">
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

