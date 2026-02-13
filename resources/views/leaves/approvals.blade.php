@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-slate-50 overflow-hidden" x-data="{ sidebarOpen: true }">
    <x-sidebar :role="Auth::user()->isSuperAdmin() ? 'superadmin' : (Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee'))" />

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="space-y-6">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button onclick="history.back()"
                            class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800">Leave Approvals</h2>
                            <p class="text-slate-400 text-sm mt-1">Review and manage leave requests from your team.</p>
                        </div>
                    </div>
                    @if(Auth::user()->isAdmin())
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Apply Leave
                    </button>
                    @endif
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    {{-- Total Requests Card --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="block hover:shadow-lg transition-shadow">
                        <div class="bg-white rounded-lg shadow border border-slate-100 cursor-pointer overflow-hidden">
                            <div class="h-1 bg-blue-500"></div>
                            <div class="p-5 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Total Requests</p>
                                    <p class="text-3xl font-bold text-blue-600">{{ $counts['all'] ?? 0 }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- Pending Actions Card --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="block hover:shadow-lg transition-shadow">
                        <div class="bg-white rounded-lg shadow border border-slate-100 cursor-pointer overflow-hidden">
                            <div class="h-1 bg-orange-500"></div>
                            <div class="p-5 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Pending</p>
                                    <p class="text-3xl font-bold text-orange-500">{{ $counts['pending'] ?? 0 }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- Approved Card --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}" class="block hover:shadow-lg transition-shadow">
                        <div class="bg-white rounded-lg shadow border border-slate-100 cursor-pointer overflow-hidden">
                            <div class="h-1 bg-green-500"></div>
                            <div class="p-5 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Approved</p>
                                    <p class="text-3xl font-bold text-green-600">{{ $counts['approved'] ?? 0 }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- Rejected Card --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}" class="block hover:shadow-lg transition-shadow">
                        <div class="bg-white rounded-lg shadow border border-slate-100 cursor-pointer overflow-hidden">
                            <div class="h-1 bg-red-500"></div>
                            <div class="p-5 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Rejected</p>
                                    <p class="text-3xl font-bold text-red-600">{{ $counts['rejected'] ?? 0 }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Search & Filters --}}
                <div class="bg-white rounded-lg border border-slate-200 p-4 flex items-center gap-4">
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <form id="searchForm" action="{{ route('leaves.approvals') }}" method="GET" class="flex gap-2">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search by name or leave type (paid, unpaid, etc)..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <select name="year" class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ (request('year') == $y || $y == now()->year) ? 'selected' : '' }}>Year {{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 font-semibold text-slate-700">Employee</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700">Leave Type / Reason</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700">Dates & Duration</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700 text-center">Approval Progress</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700 text-center">Status</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($leaves as $leave)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    {{-- Employee Column --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-base">
                                                {{ strtoupper(substr($leave->user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $leave->user->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $leave->user->employee_id ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Leave Type / Reason --}}
                                    <td class="px-6 py-5">
                                        <p class="font-semibold text-slate-900" title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 35, '...') }}</p>
                                        <p class="text-xs text-slate-400 capitalize">{{ $leave->leave_type }} Leave</p>
                                    </td>

                                    {{-- Dates & Duration --}}
                                    <td class="px-6 py-5">
                                        <p class="text-slate-900 font-medium">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</p>
                                        <p class="text-xs text-slate-500">{{ $leave->days }} Days</p>
                                    </td>

                                    {{-- Approval Progress --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center justify-center gap-2 {{ $leave->status === 'cancelled' ? 'line-through opacity-50' : '' }}">
                                            @php
                                                $s = $leave->status;
                                                $requesterRoleId = $leave->requester_role_id ?? 0;
                                                $isEmployee = !in_array($requesterRoleId, [1, 2, 3]);
                                                $isStaff = in_array($requesterRoleId, [1, 2, 3]);
                                                
                                                // Employee Flow: Self -> Supervisor -> Admin
                                                if ($isEmployee) {
                                                    $selfDone = true;
                                                    $supervisorDone = in_array($s, ['approved_by_supervisor', 'approved']);
                                                    $supervisorRejected = ($s === 'rejected') && $leave->rejected_by === 'supervisor';
                                                    $adminDone = ($s === 'approved');
                                                    $adminRejected = ($s === 'rejected') && $leave->rejected_by === 'admin';
                                                }
                                                // Staff Flow: Self -> Super Admin
                                                else if ($isStaff) {
                                                    $selfDone = true;
                                                    // Super Admin approves = 'approved' status (or 'approved_by_superadmin' in some cases)
                                                    $superadminDone = ($s === 'approved' || $s === 'approved_by_superadmin');
                                                    $superadminRejected = ($s === 'rejected');
                                                }
                                            @endphp

                                            @if($isEmployee)
                                                {{-- EMPLOYEE FLOW: Self -> Supervisor -> Admin --}}
                                                {{-- Self --}}
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <div class="w-7 h-7 rounded-full {{ $selfDone ? 'bg-green-500' : 'bg-slate-200' }} flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-slate-700">Self</span>
                                                </div>

                                                {{-- Supervisor --}}
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <div class="w-7 h-7 rounded-full {{ $supervisorDone ? 'bg-green-500' : ($supervisorRejected ? 'bg-red-500' : 'bg-slate-200') }} flex items-center justify-center">
                                                        @if($supervisorDone)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        @elseif($supervisorRejected)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        @else
                                                            <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-slate-700">Supervisor</span>
                                                </div>

                                                {{-- Admin --}}
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <div class="w-7 h-7 rounded-full {{ $adminDone ? 'bg-green-500' : ($adminRejected ? 'bg-red-500' : 'bg-slate-200') }} flex items-center justify-center">
                                                        @if($adminDone)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        @elseif($adminRejected)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        @else
                                                            <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-slate-700">Admin</span>
                                                </div>

                                            @elseif($isStaff)
                                                {{-- STAFF FLOW: Self -> Super Admin --}}
                                                {{-- Self --}}
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <div class="w-7 h-7 rounded-full {{ $selfDone ? 'bg-green-500' : 'bg-slate-200' }} flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-slate-700">Self</span>
                                                </div>

                                                {{-- Super Admin --}}
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <div class="w-7 h-7 rounded-full {{ $superadminDone ? 'bg-green-500' : ($superadminRejected ? 'bg-red-500' : 'bg-slate-200') }} flex items-center justify-center">
                                                        @if($superadminDone)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        @elseif($superadminRejected)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        @else
                                                            <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-slate-700">Super Admin</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex justify-center">
                                            @if($leave->status === 'approved' || $leave->status === 'approved_by_superadmin')
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-300">Approved</span>
                                            @elseif($leave->status === 'rejected')
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-300">Rejected</span>
                                            @elseif($leave->status === 'cancelled')
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">Cancelled</span>
                                            @elseif($leave->status === 'approved_by_supervisor')
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-300">Pending Review</span>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700 border border-orange-300">Pending</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Action --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center justify-center gap-3">
                                            @php
                                                $currentUser = Auth::user();
                                                $isSupervisor = $currentUser->isSupervisor();
                                                $isAdmin = $currentUser->isAdmin();
                                                $isSuperAdmin = $currentUser->isSuperAdmin();
                                                
                                                $requesterRoleId = $leave->requester_role_id ?? 0;
                                                $isEmployee = !in_array($requesterRoleId, [1, 2, 3]);
                                                $isStaff = in_array($requesterRoleId, [1, 2, 3]);
                                                
                                                // NEW FLOW: Super Admin has DIRECT AUTHORITY
                                                $canTakeAction = false;
                                                
                                                if ($isSuperAdmin) {
                                                    // Super Admin can act on ANY leave that is still PENDING (has priority)
                                                    $canTakeAction = ($leave->status === 'pending');
                                                } elseif ($isSupervisor) {
                                                    // Supervisor can only act on EMPLOYEE leaves that are PENDING (if Super Admin hasn't acted yet)
                                                    if ($isEmployee && $leave->status === 'pending') {
                                                        $canTakeAction = true;
                                                    }
                                                } elseif ($isAdmin) {
                                                    // Admin cannot approve in new flow - only Super Admin can
                                                    $canTakeAction = false;
                                                }
                                            @endphp

                                            {{-- Approve Icon --}}
                                            <button 
                                                onclick="handleQuickAction({{ $leave->id }}, '{{ $leave->user->name }}', '{{ $leave->leave_type }}', '{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}', '{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}', '{{ $leave->reason }}', {{ $leave->days }}, 'approved')"
                                                class="group relative transition-all duration-200 {{ $canTakeAction ? 'cursor-pointer hover:scale-110' : 'cursor-not-allowed opacity-30' }}"
                                                {{ !$canTakeAction ? 'disabled' : '' }}>
                                                <div class="w-8 h-8 rounded-full {{ $canTakeAction ? 'bg-green-100 hover:bg-green-200 text-green-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                @if($canTakeAction)
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">Approve</span>
                                                @endif
                                            </button>

                                            {{-- Reject Icon --}}
                                            <button 
                                                onclick="handleQuickAction({{ $leave->id }}, '{{ $leave->user->name }}', '{{ $leave->leave_type }}', '{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}', '{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}', '{{ $leave->reason }}', {{ $leave->days }}, 'rejected')"
                                                class="group relative transition-all duration-200 {{ $canTakeAction ? 'cursor-pointer hover:scale-110' : 'cursor-not-allowed opacity-30' }}"
                                                {{ !$canTakeAction ? 'disabled' : '' }}>
                                                <div class="w-8 h-8 rounded-full {{ $canTakeAction ? 'bg-red-100 hover:bg-red-200 text-red-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </div>
                                                @if($canTakeAction)
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">Reject</span>
                                                @endif
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <p class="text-sm">No leave requests found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-600">
                                Showing {{ $leaves->firstItem() ?? 0 }} to {{ $leaves->lastItem() ?? 0 }} of {{ $leaves->total() }} entries
                            </p>
                            {{ $leaves->links() }}
                        </div>
                    </div>
                </div>

                {{-- Policy Update Box --}}
            </div>
        </main>
    </div>
</div>

{{-- Review Modal --}}
<div id="reviewModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">Review Leave Request</h3>
                <button onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="px-6 py-5 space-y-4">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Employee</p>
                <p id="modalEmployeeName" class="text-sm font-semibold text-slate-800"></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Leave Type</p>
                    <p id="modalLeaveType" class="text-sm font-medium text-slate-700 capitalize"></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Duration</p>
                    <p id="modalDuration" class="text-sm font-medium text-slate-700"></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Start Date</p>
                    <p id="modalStartDate" class="text-sm font-medium text-slate-700"></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">End Date</p>
                    <p id="modalEndDate" class="text-sm font-medium text-slate-700"></p>
                </div>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Reason</p>
                <p id="modalReason" class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg"></p>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-5 bg-slate-50 rounded-b-2xl border-t border-slate-200">
            <div class="flex items-center justify-end gap-3">
                <button onclick="handleReject()" id="rejectBtn" class="px-6 py-2.5 text-sm font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors flex items-center gap-2">
                    <span id="rejectText">Reject</span>
                    <svg id="rejectSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                <button onclick="handleApprove()" id="approveBtn" class="px-6 py-2.5 text-sm font-semibold text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                    <span id="approveText">Approve</span>
                    <svg id="approveSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success Toast - Professional Large Modal --}}
<div id="successToast" class="hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 sm:p-12 max-w-lg border-l-8 border-emerald-500 transform transition-all">
        <div class="flex items-start justify-between gap-6">
            <div class="flex-1">
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-2">Success!</h2>
                <p class="text-lg sm:text-xl text-slate-700 font-semibold leading-relaxed" id="toastMessage">Leave status updated successfully.</p>
                <p class="text-sm sm:text-base text-slate-500 font-medium mt-3">Your action has been completed successfully</p>
            </div>
        </div>
    </div>
</div>

{{-- Error Toast - Professional Large Modal --}}
<div id="errorToast" class="hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 sm:p-12 max-w-lg border-l-8 border-red-500 transform transition-all">
        <div class="flex items-start justify-between gap-6">
            <div class="flex-1">
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-2">Error</h2>
                <p class="text-lg sm:text-xl text-slate-700 font-semibold leading-relaxed" id="errorToastMessage">An error occurred. Please try again.</p>
                <p class="text-sm sm:text-base text-slate-500 font-medium mt-3">Please check your input and try again, or contact support</p>
            </div>
            <button type="button" onclick="document.getElementById('errorToast').classList.add('hidden')" class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    let currentLeaveId = null;
    let currentAction = null;

    function handleQuickAction(leaveId, employeeName, leaveType, startDate, endDate, reason, days, action) {
        currentLeaveId = leaveId;
        currentAction = action;
        
        const actionText = action === 'approved' ? 'approve' : 'reject';
        const confirmMessage = action === 'approved' 
            ? 'Are you sure you want to approve this leave request?' 
            : 'Are you sure you want to reject this leave request?';
        
        if (confirm(confirmMessage)) {
            updateLeaveStatus(leaveId, action);
        }
    }

    function openReviewModal(leaveId, employeeName, leaveType, startDate, endDate, reason, days) {
        currentLeaveId = leaveId;
        document.getElementById('modalEmployeeName').textContent = employeeName;
        document.getElementById('modalLeaveType').textContent = leaveType;
        document.getElementById('modalDuration').textContent = days + ' day' + (days > 1 ? 's' : '');
        document.getElementById('modalStartDate').textContent = startDate;
        document.getElementById('modalEndDate').textContent = endDate;
        document.getElementById('modalReason').textContent = reason || 'No reason provided';
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
        currentLeaveId = null;
    }

    function showSuccessToast(message) {
        const toast = document.getElementById('successToast');
        const toastMessage = document.getElementById('toastMessage');
        toastMessage.textContent = message;
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
            location.reload();
        }, 1500);
    }

    function showErrorToast(message) {
        const toast = document.getElementById('errorToast');
        const toastMessage = document.getElementById('errorToastMessage');
        toastMessage.textContent = message || 'An error occurred. Please try again.';
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4000);
    }

    function setLoadingState(action, isLoading) {
        if (action === 'approve') {
            const btn = document.getElementById('approveBtn');
            const text = document.getElementById('approveText');
            const spinner = document.getElementById('approveSpinner');
            const rejectBtn = document.getElementById('rejectBtn');
            
            if (isLoading) {
                text.textContent = 'Processing...';
                spinner.classList.remove('hidden');
                btn.disabled = true;
                rejectBtn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                text.textContent = 'Approve';
                spinner.classList.add('hidden');
                btn.disabled = false;
                rejectBtn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        } else if (action === 'reject') {
            const btn = document.getElementById('rejectBtn');
            const text = document.getElementById('rejectText');
            const spinner = document.getElementById('rejectSpinner');
            const approveBtn = document.getElementById('approveBtn');
            
            if (isLoading) {
                text.textContent = 'Processing...';
                spinner.classList.remove('hidden');
                btn.disabled = true;
                approveBtn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                text.textContent = 'Reject';
                spinner.classList.add('hidden');
                btn.disabled = false;
                approveBtn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }
    }

    function handleApprove() {
        if (!currentLeaveId) return;
        
        if (confirm('Are you sure you want to approve this leave request?')) {
            setLoadingState('approve', true);
            updateLeaveStatus(currentLeaveId, 'approved');
        }
    }

    function handleReject() {
        if (!currentLeaveId) return;
        
        if (confirm('Are you sure you want to reject this leave request?')) {
            setLoadingState('reject', true);
            updateLeaveStatus(currentLeaveId, 'rejected');
        }
    }

    function updateLeaveStatus(leaveId, status) {
        const action = status === 'approved' ? 'approve' : 'reject';
        
        fetch(`/leaves/${leaveId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => {
            // Parse JSON for both successful and error responses
            return response.json().then(data => ({
                ok: response.ok,
                httpStatus: response.status,
                data: data
            }));
        })
        .then(({ ok, httpStatus, data }) => {
            setLoadingState(action, false);
            if (ok && data.success) {
                closeReviewModal();
                const actionText = status === 'approved' ? 'approved' : 'rejected';
                showSuccessToast(`Leave request ${actionText} successfully.`);
                // Reload page after short delay to show updated status
                setTimeout(() => window.location.reload(), 1500);
            } else {
                // Show actual error message from server
                showErrorToast(data.message || 'An error occurred while processing the request.');
            }
        })
        .catch(error => {
            setLoadingState(action, false);
            console.error('Error:', error);
            showErrorToast('An error occurred while processing the request. Please try again.');
        });
    }
</script>

<script>
    // Live search functionality
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const yearSelect = document.querySelector('select[name="year"]');
    const searchForm = document.getElementById('searchForm');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            // Debounce - wait 500ms after user stops typing before submitting
            searchTimeout = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }
</script>
@endsection
