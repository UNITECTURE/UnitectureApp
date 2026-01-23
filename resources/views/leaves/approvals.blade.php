@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-slate-50 overflow-hidden" x-data="{ sidebarOpen: true }">
    <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">Leave Approvals</h2>
                        <p class="text-slate-400 text-sm mt-1">Review and manage leave requests from your team.</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
                       class="bg-white rounded-lg p-6 border {{ request('status', 'all') == 'all' ? 'border-blue-400 ring-2 ring-blue-100 shadow-md' : 'border-slate-100' }} shadow-sm cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-lg hover:border-blue-200 hover:bg-blue-50">
                        <div class="text-center">
                             <p class="text-3xl font-bold text-blue-600">{{ $counts['all'] }}</p>
                            <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Visible Requests</p>
                        </div>
                    </a>
                    
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" 
                       class="bg-white rounded-lg p-6 border {{ request('status') == 'pending' ? 'border-yellow-400 ring-2 ring-yellow-100 shadow-md' : 'border-slate-100' }} shadow-sm cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-lg hover:border-yellow-200 hover:bg-yellow-50">
                         <div class="text-center">
                             <p class="text-3xl font-bold text-yellow-500">{{ $counts['pending'] }}</p>
                            <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Pending Here</p>
                        </div>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}" 
                       class="bg-white rounded-lg p-6 border {{ request('status') == 'approved' ? 'border-green-400 ring-2 ring-green-100 shadow-md' : 'border-slate-100' }} shadow-sm cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-lg hover:border-green-200 hover:bg-green-50">
                         <div class="text-center">
                             <p class="text-3xl font-bold text-green-500">{{ $counts['approved'] }}</p>
                            <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Approved Here</p>
                        </div>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}" 
                       class="bg-white rounded-lg p-6 border {{ request('status') == 'rejected' ? 'border-red-400 ring-2 ring-red-100 shadow-md' : 'border-slate-100' }} shadow-sm cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-lg hover:border-red-200 hover:bg-red-50">
                         <div class="text-center">
                             <p class="text-3xl font-bold text-red-500">{{ $counts['rejected'] }}</p>
                            <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Rejected Here</p>
                        </div>
                    </a>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-4 bg-white p-4 rounded-lg border border-slate-200">
                    <select class="rounded-md border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                        <option>2025</option>
                        <option>2026</option>
                    </select>
                    <button class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filter
                    </button>
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <form action="{{ route('leaves.approvals') }}" method="GET">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="w-full pl-10 rounded-md border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </form>
                    </div>
                </div>

                <!-- Leaves List -->
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-500">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Name</th>
                                    <th class="px-6 py-4 font-semibold">Leave Type</th>
                                    <th class="px-6 py-4 font-semibold">Reason</th>
                                    <th class="px-6 py-4 font-semibold">Dates</th>
                                    <th class="px-6 py-4 font-semibold text-center">Progress</th>
                                    <th class="px-6 py-4 font-semibold text-center">Status</th>
                                    <th class="px-6 py-4 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($leaves as $leave)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $leave->user->name }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $leave->user->role->name ?? 'User' }}</div>
                                    </td>
                                    <td class="px-6 py-4 capitalize">{{ $leave->leave_type }}</td>
                                    <td class="px-6 py-4 text-slate-800">{{ Str::limit($leave->reason, 20) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</div>
                                        <div class="text-xs text-slate-400">{{ $leave->days }} Days</div>
                                    </td>
                                    
                                    {{-- Progress Tracker Column --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            @php
                                                $s = $leave->status;
                                                $isRejected = $s === 'rejected';
                                                
                                                // Step 1: Employee (Always Done)
                                                // Step 2: Supervisor (Done if partially_approved or approved)
                                                // Step 3: Admin (Done if approved)
                                                
                                                $step1State = 'done'; // Employee
                                                
                                                $step2State = 'pending';
                                                if ($s === 'approved_by_supervisor' || $s === 'approved') $step2State = 'done';
                                                elseif ($isRejected) $step2State = 'rejected';
                                                
                                                $step3State = 'pending';
                                                if ($s === 'approved') $step3State = 'done';
                                                elseif ($isRejected && $s !== 'rejected') $step3State = 'rejected'; // Logic for admin rejection if it reached here? 
                                                // Simplifying rejection: if rejected, show red on the current stopper? 
                                                // For now, let's follow the simple "All Red" or specific state logic.
                                                // Actually, if rejected, let's make the "stopper" red. 
                                                // But simpler: 
                                            @endphp
                                            
                                            <!-- Employee Circle -->
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $isRejected ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                                    @if($isRejected) 
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-medium {{ $isRejected ? 'text-red-600' : 'text-green-600' }}">Employee</span>
                                            </div>

                                            <!-- Connector 1 -->
                                            <div class="w-8 h-0.5 {{ ($s === 'approved_by_supervisor' || $s === 'approved') ? 'bg-green-500' : ($isRejected ? 'bg-red-300' : 'bg-slate-200') }}"></div>

                                            <!-- Supervisor Circle -->
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="w-6 h-6 rounded-full flex items-center justify-center {{ ($s === 'approved_by_supervisor' || $s === 'approved') ? 'bg-green-100 text-green-600' : ($isRejected ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-300') }}">
                                                    @if($s === 'approved_by_supervisor' || $s === 'approved')
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif($isRejected)
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @else
                                                        <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-medium {{ ($s === 'approved_by_supervisor' || $s === 'approved') ? 'text-green-600' : ($isRejected ? 'text-red-600' : 'text-slate-400') }}">Supervisor</span>
                                            </div>

                                            <!-- Connector 2 -->
                                            <div class="w-8 h-0.5 {{ $s === 'approved' ? 'bg-green-500' : (($isRejected && $s !== 'pending') ? 'bg-red-300' : 'bg-slate-200') }}"></div>

                                            <!-- Admin Circle -->
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $s === 'approved' ? 'bg-green-100 text-green-600' : ($isRejected ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-300') }}">
                                                    @if($s === 'approved')
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif($isRejected)
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @else
                                                        <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-medium {{ $s === 'approved' ? 'text-green-600' : ($isRejected ? 'text-red-600' : 'text-slate-400') }}">Admin</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Status Column --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($leave->status === 'approved')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                                Final Approved
                                            </span>
                                         @elseif($leave->status === 'rejected')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                                Rejected
                                            </span>
                                        @elseif($leave->status === 'approved_by_supervisor')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                                Supervisor Approved
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1">Waiting for Admin</div>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                                Pending
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1">Employee Request</div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        @php
                                            $canApprove = false;
                                            if (Auth::user()->isSupervisor() && $leave->status === 'pending') {
                                                $canApprove = true;
                                            } elseif (Auth::user()->isAdmin() && ($leave->status === 'pending' || $leave->status === 'approved_by_supervisor')) {
                                                $canApprove = true;
                                            }
                                        @endphp

                                        @if($canApprove)
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('leaves.status', $leave->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="flex items-center justify-center w-8 h-8 rounded-full bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 transition-colors" title="Approve">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('leaves.status', $leave->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition-colors" title="Reject">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">
                                                @if($leave->status === 'approved')
                                                    Complete
                                                @elseif($leave->status === 'approved_by_supervisor' && Auth::user()->isSupervisor())
                                                    Pending Admin
                                                @elseif($leave->status === 'rejected')
                                                    Closed
                                                @else
                                                    No Action
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">No leave requests found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $leaves->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
