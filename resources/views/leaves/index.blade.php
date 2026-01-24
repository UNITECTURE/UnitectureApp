@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-slate-50 overflow-hidden" x-data="{ sidebarOpen: true }">
    <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button onclick="history.back()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </button>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">My Leaves</h2>
                            <p class="text-slate-400 text-sm mt-1">Apply for time off and track your leave requests.</p>
                        </div>
                    </div>
                    @if(!Auth::user()->isAdmin())
                    <button onclick="document.getElementById('apply-leave-modal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Apply Leave
                    </button>
                    @endif
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 items-start">
                    <!-- New Leave Balance Card (Detailed) -->
                    <div class="bg-white rounded-lg p-6 border border-slate-100 shadow-sm relative overflow-hidden group">
                         <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-slate-700 font-semibold">Leave Balance</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-50">
                                <span class="text-slate-500 text-sm">Earned Leave</span>
                                <span class="text-slate-700 font-medium">{{ number_format(Auth::user()->leave_balance + $usedLeaves, 2) }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between pb-4 border-b border-slate-50">
                                <span class="text-slate-500 text-sm">Leave Balance</span>
                                <span class="text-slate-700 font-bold text-lg">{{ number_format(Auth::user()->leave_balance, 2) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 text-sm">Used</span>
                                <span class="text-yellow-600 font-medium">{{ number_format($usedLeaves, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-100 shadow-sm">
                        <div class="flex items-center gap-4">
                             <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                 <p class="text-xl font-bold text-slate-800">{{ $leaves->count() }}</p>
                                <p class="text-xs text-slate-500 uppercase font-semibold">Total Requests</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-100 shadow-sm">
                         <div class="flex items-center gap-4">
                             <div class="w-12 h-12 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                 <p class="text-xl font-bold text-yellow-600">{{ $leaves->where('status', 'pending')->count() }}</p>
                                <p class="text-xs text-slate-500 uppercase font-semibold">Pending</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-6 border border-green-100 shadow-sm">
                         <div class="flex items-center gap-4">
                             <div class="w-12 h-12 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                 <p class="text-xl font-bold text-green-600">{{ $leaves->where('status', 'approved')->count() }}</p>
                                <p class="text-xs text-slate-500 uppercase font-semibold">Approved</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 rounded-lg p-6 border border-red-100 shadow-sm">
                         <div class="flex items-center gap-4">
                             <div class="w-12 h-12 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                 <p class="text-xl font-bold text-red-600">{{ $leaves->where('status', 'rejected')->count() }}</p>
                                <p class="text-xs text-slate-500 uppercase font-semibold">Rejected</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                 @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-md text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Leaves List -->
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-500">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-5 font-semibold">Leave Type</th>
                                    <th class="px-6 py-5 font-semibold">Reason</th>
                                    <th class="px-6 py-5 font-semibold">From Date</th>
                                    <th class="px-6 py-5 font-semibold">To Date</th>
                                    <th class="px-6 py-5 font-semibold">Days</th>
                                    <th class="px-6 py-5 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($leaves as $leave)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-5 capitalize">{{ $leave->leave_type }}</td>
                                    <td class="px-6 py-5 text-slate-800 font-medium">{{ $leave->reason }}</td>
                                    <td class="px-6 py-5">{{ $leave->start_date->format('d-M-Y') }}</td>
                                    <td class="px-6 py-5">{{ $leave->end_date->format('d-M-Y') }}</td>
                                     <td class="px-6 py-5">{{ $leave->days }}</td>
                                    <td class="px-6 py-5">
                                        <x-leave-stepper :status="$leave->status" />
                                        <div class="mt-2 text-center">
                                            @if($leave->status === 'approved')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Final Approved</span>
                                            @elseif($leave->status === 'rejected')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Rejected</span>
                                            @elseif($leave->status === 'approved_by_supervisor')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Supervisor Approved (Pending Admin)</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Pending</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400">No leave requests found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Apply Leave Modal -->
<div id="apply-leave-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('apply-leave-modal').classList.add('hidden')"></div>
    
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg z-10 mx-4 overflow-hidden animate-fade-in-up">
        <div class="px-6 py-4 bg-blue-600 border-b border-blue-500 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">Apply Leave</h3>
            <button onclick="document.getElementById('apply-leave-modal').classList.add('hidden')" class="text-blue-100 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form action="{{ route('leaves.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                     <label class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                     <input type="date" name="start_date" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                     <label class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                     <input type="date" name="end_date" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>

            <!-- System Info Message -->
             <div class="bg-blue-50 text-blue-700 p-3 rounded-md text-sm">
                <p><strong>Note:</strong> Leave type (Paid/Unpaid) will be determined automatically based on your leave balance.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Leave Category</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-md cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="leave_category" value="planned" required class="w-4 h-4 text-blue-600">
                        <div>
                            <p class="font-medium text-slate-700">Planned Leave</p>
                            <p class="text-xs text-slate-500">Requires 7 days notice</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-md cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="leave_category" value="emergency" required class="w-4 h-4 text-blue-600">
                        <div>
                            <p class="font-medium text-slate-700">Emergency Leave</p>
                            <p class="text-xs text-slate-500">Only for today or tomorrow</p>
                        </div>
                    </label>
                </div>
            </div>

             <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reason for leave</label>
                <textarea name="reason" rows="3" required placeholder="e.g. Emergency, Medical, Family function" class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-6">
                <div class="text-sm">
                    <span class="text-slate-500">Current Month Balance:</span>
                    <span class="font-bold text-slate-800">{{ Auth::user()->leave_balance }} Days</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('apply-leave-modal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
