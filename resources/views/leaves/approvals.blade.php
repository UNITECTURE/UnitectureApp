@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Leave Approvals</h2>
            <p class="text-slate-400 text-sm mt-1">Review and manage leave requests from your team.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 border border-slate-100 shadow-sm">
            <div class="text-center">
                 <p class="text-3xl font-bold text-blue-600">{{ $counts['all'] }}</p>
                <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Visible Requests</p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg p-6 border border-slate-100 shadow-sm">
             <div class="text-center">
                 <p class="text-3xl font-bold text-yellow-500">{{ $counts['pending'] }}</p>
                <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Pending Here</p>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 border border-slate-100 shadow-sm">
             <div class="text-center">
                 <p class="text-3xl font-bold text-green-500">{{ $counts['approved'] }}</p>
                <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Approved Here</p>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 border border-slate-100 shadow-sm">
             <div class="text-center">
                 <p class="text-3xl font-bold text-red-500">{{ $counts['rejected'] }}</p>
                <p class="text-xs text-slate-500 uppercase font-bold mt-2 tracking-wider">Rejected Here</p>
            </div>
        </div>
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
                        <th class="px-6 py-4 font-semibold">From Date</th>
                        <th class="px-6 py-4 font-semibold">To Date</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
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
                        <td class="px-6 py-4 text-slate-800">{{ Str::limit($leave->reason, 30) }}</td>
                        <td class="px-6 py-4">{{ $leave->start_date->format('d-M-Y') }}</td>
                        <td class="px-6 py-4">{{ $leave->end_date->format('d-M-Y') }}</td>
                        <td class="px-6 py-4">
                            @if($leave->status === 'approved')
                                <span class="px-2.5 py-1 rounded-sm text-xs font-medium bg-green-100 text-green-700">Final Approved</span>
                             @elseif($leave->status === 'rejected')
                                <span class="px-2.5 py-1 rounded-sm text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                            @elseif($leave->status === 'approved_by_supervisor')
                                <span class="px-2.5 py-1 rounded-sm text-xs font-medium bg-blue-100 text-blue-700">Supervisor Approved (Waiting for Admin)</span>
                            @else
                                <span class="px-2.5 py-1 rounded-sm text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
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
                                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded text-xs font-medium transition-colors shadow-sm">
                                        {{ Auth::user()->isAdmin() ? 'Final Approve' : 'Approve' }}
                                    </button>
                                </form>
                                <form action="{{ route('leaves.status', $leave->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="px-3 py-1.5 bg-white border border-red-500 text-red-600 hover:bg-red-50 rounded text-xs font-medium transition-colors">Reject</button>
                                </form>
                            </div>
                            @else
                                <span class="text-xs text-slate-400">
                                    @if($leave->status === 'approved')
                                        Completed
                                    @elseif($leave->status === 'approved_by_supervisor' && Auth::user()->isSupervisor())
                                        Sent to Admin
                                    @else
                                        —
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
@endsection
