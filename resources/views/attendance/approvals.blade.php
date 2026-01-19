@extends('layouts.app')

@section('content')
@php
    $role = $role ?? 'admin';
    // $summary and $requests array are passed from controller
@endphp

<div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">
    
    {{-- Sidebar Component --}}
    <x-sidebar :role="$role" />

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
        {{-- Top Header --}}
        {{-- Top Header --}}
        <div class="flex items-center justify-between px-8 py-6 bg-white shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="history.back()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ 'Attendance Approvals' }}</h1>
                    <p class="text-slate-500 text-sm mt-1">
                        {{ $role === 'admin' ? 'Review and manage attendance requests across the organization.' : 'Review and manage attendance requests for your team.' }}
                    </p>
                </div>
            </div>
            
             {{-- Quick Stats / Info or Action --}}
            <div class="flex items-center space-x-4">
                 <!-- Removed Pending Requests Count -->
            </div>
        </div>

    <div class="flex-1 overflow-y-auto p-8">
            {{-- Summary Cards --}}
            <div class="flex w-full gap-6 mb-8">
                {{-- All --}}
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
                   class="flex-1 bg-blue-50 rounded-xl border {{ request('status', 'all') == 'all' ? 'border-blue-400 ring-2 ring-blue-200 shadow-md' : 'border-blue-100' }} p-4 flex flex-col items-center justify-center h-26 transition-all duration-200 hover:scale-105 cursor-pointer hover:bg-blue-100 hover:shadow-lg hover:border-blue-300">
                    <div class="text-2xl font-bold text-blue-600">{{ $summary['all'] }}</div>
                    <div class="text-xs font-medium text-blue-600 mt-1">All</div>
                </a>

                {{-- Pending --}}
                <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" 
                   class="flex-1 bg-yellow-50 rounded-xl border {{ request('status') == 'pending' ? 'border-yellow-400 ring-2 ring-yellow-200 shadow-md' : 'border-yellow-100' }} p-4 flex flex-col items-center justify-center h-26 transition-all duration-200 hover:scale-105 cursor-pointer hover:bg-yellow-100 hover:shadow-lg hover:border-yellow-300">
                    <div class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</div>
                    <div class="text-xs font-medium text-yellow-600 mt-1">Pending</div>
                </a>

                {{-- Approved --}}
                <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}" 
                   class="flex-1 bg-green-50 rounded-xl border {{ request('status') == 'approved' ? 'border-green-400 ring-2 ring-green-200 shadow-md' : 'border-green-100' }} p-4 flex flex-col items-center justify-center h-26 transition-all duration-200 hover:scale-105 cursor-pointer hover:bg-green-100 hover:shadow-lg hover:border-green-300">
                    <div class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</div>
                    <div class="text-xs font-medium text-green-600 mt-1">Approved</div>
                </a>

                {{-- Rejected --}}
                <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}" 
                   class="flex-1 bg-red-50 rounded-xl border {{ request('status') == 'rejected' ? 'border-red-400 ring-2 ring-red-200 shadow-md' : 'border-red-100' }} p-4 flex flex-col items-center justify-center h-26 transition-all duration-200 hover:scale-105 cursor-pointer hover:bg-red-100 hover:shadow-lg hover:border-red-300">
                    <div class="text-2xl font-bold text-red-500">{{ $summary['rejected'] }}</div>
                    <div class="text-xs font-medium text-red-500 mt-1">Rejected</div>
                </a>
            </div>

            {{-- Filter & Search Bar --}}
            <form action="" method="GET" class="flex items-center gap-4 mb-8" x-data="{ 
                filterOpen: false, 
                selectedStatus: '{{ request('status', 'All') }}',
                dateRange: '{{ request('date') }}' 
            }" @click.outside="filterOpen = false">
                
                {{-- Filter Button & Dropdown --}}
                <div class="relative">
                    <button type="button" @click="filterOpen = !filterOpen" :class="{'bg-slate-50 border-blue-300 ring-4 ring-blue-50': filterOpen}" class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2.5 rounded-lg shadow-sm hover:bg-slate-50 transition-all duration-200 select-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span class="text-sm font-medium">Filter</span>
                        <svg class="w-4 h-4 text-slate-400 ml-1 transform transition-transform duration-200" :class="{'rotate-180': filterOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="filterOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute top-full left-0 z-20 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden" 
                         style="display: none;">
                        
                        <div class="p-4 border-b border-slate-50 bg-slate-50/50">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</span>
                            <div class="mt-2 space-y-1">
                                <label class="flex items-center px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer transition-colors">
                                    <input type="radio" name="status" value="All" x-model="selectedStatus" class="form-radio text-blue-600 focus:ring-blue-500 h-4 w-4 border-slate-300">
                                    <span class="ml-2 text-sm text-slate-700">All</span>
                                </label>
                                <label class="flex items-center px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer transition-colors">
                                    <input type="radio" name="status" value="Pending" x-model="selectedStatus" class="form-radio text-orange-400 focus:ring-orange-400 h-4 w-4 border-slate-300">
                                    <span class="ml-2 text-sm text-slate-700">Pending</span>
                                </label>
                                <label class="flex items-center px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer transition-colors">
                                    <input type="radio" name="status" value="Approved" x-model="selectedStatus" class="form-radio text-green-500 focus:ring-green-500 h-4 w-4 border-slate-300">
                                    <span class="ml-2 text-sm text-slate-700">Approved</span>
                                </label>
                                <label class="flex items-center px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer transition-colors">
                                    <input type="radio" name="status" value="Rejected" x-model="selectedStatus" class="form-radio text-red-500 focus:ring-red-500 h-4 w-4 border-slate-300">
                                    <span class="ml-2 text-sm text-slate-700">Rejected</span>
                                </label>
                            </div>
                        </div>
                        
                        {{-- Date Range (Mock) --}}
                        <div class="p-4">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</span>
                             <div class="mt-2 relative">
                                <input type="date" name="date" x-model="dateRange" class="block w-full text-sm border-slate-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-600">
                             </div>
                        </div>

                        <div class="p-4 bg-slate-50 border-t border-slate-200">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition-colors shadow-sm text-center">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="relative flex-1 max-w-lg">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="block w-full pl-11 pr-3 py-2.5 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:placeholder-slate-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm shadow-sm transition-shadow duration-200">
                </div>
            </form>

            {{-- Approvals Table --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/4">Reason</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                             @forelse($requests as $request)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-9 w-9 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 text-xs font-bold border border-slate-200">
                                            {{ substr($request->user->name, 0, 2) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-slate-900">{{ $request->user->name }}</div>
                                            <div class="text-xs text-slate-500">{{ucfirst($request->user->role?->name ?? 'Employee') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ \Carbon\Carbon::parse($request->date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">
                                    {{ $request->duration }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 min-w-[250px]">
                                    <p class="truncate w-full" title="{{ $request->reason }}">
                                        {{ $request->reason ?? '-' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                     <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium
                                        {{ $request->status === 'approved' ? 'bg-green-100 text-green-600' : 
                                           ($request->status === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-yellow-50 text-yellow-700') }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($request->status === 'pending')
                                    <div class="flex items-center justify-end space-x-2">
                                        <form action="{{ route('attendance.manual.approve', $request->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-1.5 rounded-lg transition-colors border border-transparent hover:border-green-200" title="Approve">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('attendance.manual.reject', $request->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg transition-colors border border-transparent hover:border-red-200" title="Reject">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">No actions</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <p class="text-base font-medium text-slate-900">No requests found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination Feedback --}}
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                     <div class="flex items-center space-x-4">
                        <span>Showing {{ $requests->count() }} entries</span>
                     </div>
                </div>
            </div>
    </div>
        </div>
    </main>
</div>
@endsection
