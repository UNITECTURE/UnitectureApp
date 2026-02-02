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
                            <h1 class="text-3xl font-bold text-slate-800">My Leaves</h1>
                            <p class="text-slate-500 text-sm mt-1">Apply for time off and track your leave requests.</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('apply-leave-modal').classList.remove('hidden')" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Apply Leave
                    </button>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    {{-- Available Balance Card --}}
                    <div style="background-color: #2563EB;" class="rounded-lg p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p style="color: #FFFFFF;" class="text-sm font-bold mb-2">Available Balance</p>
                                <p style="color: #FFFFFF;" class="text-6xl font-black">{{ number_format($earnedLeaves - $usedLeaves, 1) }}</p>
                            </div>
                            <div style="background-color: rgba(255,255,255,0.3);" class="w-16 h-16 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Total Requests Card --}}
                    <div style="background-color: #60A5FA;" class="rounded-lg p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p style="color: #FFFFFF;" class="text-sm font-bold mb-2">Total Requests</p>
                                <p style="color: #FFFFFF;" class="text-6xl font-black">{{ $leaves->count() }}</p>
                            </div>
                            <div style="background-color: rgba(255,255,255,0.3);" class="w-16 h-16 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Card --}}
                    <div style="background-color: #FB923C;" class="rounded-lg p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p style="color: #FFFFFF;" class="text-sm font-bold mb-2">Pending</p>
                                <p style="color: #FFFFFF;" class="text-6xl font-black">{{ $leaves->where('status', 'pending')->count() }}</p>
                            </div>
                            <div style="background-color: rgba(255,255,255,0.3);" class="w-16 h-16 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Approved Card --}}
                    <div style="background-color: #4ADE80;" class="rounded-lg p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p style="color: #FFFFFF;" class="text-sm font-bold mb-2">Approved</p>
                                <p style="color: #FFFFFF;" class="text-6xl font-black">{{ $leaves->where('status', 'approved')->count() }}</p>
                            </div>
                            <div style="background-color: rgba(255,255,255,0.3);" class="w-16 h-16 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                        {!! session('success') !!}
                    </div>
                @endif

                <!-- Leaves List -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 font-semibold text-slate-700">Leave Type / Reason</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700">Dates & Duration</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700">Approval Progress</th>
                                    <th class="px-6 py-4 font-semibold text-slate-700 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($leaves as $leave)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    {{-- Leave Type / Reason --}}
                                    <td class="px-6 py-5">
                                        <p class="font-medium text-slate-900 capitalize">{{ $leave->leave_type }} Leave</p>
                                        <p class="text-xs text-slate-500 italic max-w-xs truncate" title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 35, '...') }}</p>
                                    </td>

                                    {{-- Dates & Duration --}}
                                    <td class="px-6 py-5">
                                        <p class="text-slate-900 font-medium">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</p>
                                        <p class="text-xs text-slate-500">{{ $leave->days }} Days</p>
                                    </td>

                                    {{-- Approval Progress --}}
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center">
                                            <div class="inline-flex items-center justify-center gap-2">
                                            @php
                                                $s = $leave->status;
                                                $isRejected = ($s === 'rejected');
                                                $selfDone = true;
                                                $leadDone = in_array($s, ['approved_by_supervisor', 'approved']);
                                                $leadRejected = $isRejected && !$leadDone;
                                                $adminDone = ($s === 'approved');
                                                $adminRejected = $isRejected && $leadDone;
                                            @endphp

                                            {{-- Self --}}
                                            <div class="flex flex-col items-center gap-0.5">
                                                <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                <span class="text-[10px] font-semibold text-slate-700">Self</span>
                                            </div>

                                            {{-- Connector --}}
                                            <div class="w-5 h-0.5 {{ $leadDone ? 'bg-green-500' : ($leadRejected ? 'bg-red-500' : 'bg-slate-200') }}"></div>

                                            {{-- Lead --}}
                                            <div class="flex flex-col items-center gap-0.5">
                                                <div class="w-7 h-7 rounded-full {{ $leadDone ? 'bg-green-500' : ($leadRejected ? 'bg-red-500' : 'bg-slate-200') }} flex items-center justify-center">
                                                    @if($leadDone)
                                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif($leadRejected)
                                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @else
                                                        <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-semibold text-slate-700">Lead</span>
                                            </div>

                                            {{-- Connector --}}
                                            <div class="w-5 h-0.5 {{ $adminDone ? 'bg-green-500' : ($adminRejected ? 'bg-red-500' : 'bg-slate-200') }}"></div>

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
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Status Column with Cancel Button --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center justify-center gap-4">
                                            <div>
                                                @if($leave->status === 'pending')
                                                    <span style="background-color: #FB923C;" class="text-white text-xs font-bold py-1.5 px-3 rounded-full">Pending</span>
                                                @elseif($leave->status === 'approved')
                                                    <span style="background-color: #4ADE80;" class="text-white text-xs font-bold py-1.5 px-3 rounded-full">Approved</span>
                                                @elseif($leave->status === 'approved_by_supervisor')
                                                    <span style="background-color: #60A5FA;" class="text-white text-xs font-bold py-1.5 px-3 rounded-full">Pending Admin</span>
                                                @elseif($leave->status === 'rejected')
                                                    <span style="background-color: #F87171;" class="text-white text-xs font-bold py-1.5 px-3 rounded-full">Rejected</span>
                                                @elseif($leave->status === 'cancelled')
                                                    <span class="bg-slate-300 text-slate-800 text-xs font-bold py-1.5 px-3 rounded-full">Cancelled</span>
                                                @else
                                                    <span class="bg-slate-200 text-slate-800 text-xs font-bold py-1.5 px-3 rounded-full">{{ ucfirst($leave->status) }}</span>
                                                @endif
                                            </div>
                                            
                                            {{-- Cancel Cross Button for All Rows --}}
                                            @php
                                                $showCancel = in_array($leave->status, ['pending', 'approved', 'approved_by_supervisor']);
                                            @endphp
                                            @if($showCancel)
                                                <form action="{{ route('leaves.cancel', $leave) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-full shadow-sm hover:opacity-90 transition-opacity" title="Cancel Leave" aria-label="Cancel Leave">
                                                        <svg class="w-9 h-9" viewBox="0 0 64 64" role="img" aria-hidden="true">
                                                            <circle cx="32" cy="32" r="30" fill="#EF4444" />
                                                            <path d="M20 20 L44 44 M44 20 L20 44" stroke="#FFFFFF" stroke-width="8" stroke-linecap="round" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            <p class="text-slate-500 font-medium">No leave requests yet</p>
                                            <p class="text-slate-400 text-sm mt-1">Start by applying for a leave using the button above.</p>
                                        </div>
                                    </td>
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
    
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl z-10 mx-4 overflow-hidden">
        <div style="background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%);" class="px-6 py-5 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-white">Apply For Leave</h3>
                <p class="text-blue-100 text-sm mt-1">Submit your leave request for approval</p>
            </div>
            <button onclick="document.getElementById('apply-leave-modal').classList.add('hidden')" class="text-blue-100 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form action="{{ route('leaves.store') }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                     <label class="block text-sm font-semibold text-slate-700 mb-2">From Date</label>
                     <input type="date" name="start_date" id="start_date" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm focus:outline-none transition-colors">
                </div>
                <div>
                     <label class="block text-sm font-semibold text-slate-700 mb-2">To Date</label>
                     <input type="date" name="end_date" id="end_date" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm focus:outline-none transition-colors">
                </div>
            </div>

            <!-- System Info Message -->
             <div style="background-color: #EFF6FF; border-color: #BFDBFE;" class="border rounded-lg p-4">
                <p class="text-blue-900 text-sm"><strong>Note:</strong> Leave type (Paid/Unpaid) will be determined automatically based on your leave balance.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">Leave Category</label>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="leave_category" value="planned" required class="w-4 h-4 text-blue-600 cursor-pointer">
                        <div>
                            <p class="font-semibold text-slate-700">Planned Leave</p>
                            <p class="text-xs text-slate-500 mt-0.5">Requires 7 days notice</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="leave_category" value="emergency" required class="w-4 h-4 text-blue-600 cursor-pointer">
                        <div>
                            <p class="font-semibold text-slate-700">Emergency Leave</p>
                            <p class="text-xs text-slate-500 mt-0.5">Only for today or tomorrow</p>
                        </div>
                    </label>
                </div>
            </div>

             <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Reason for Leave</label>
                <textarea name="reason" rows="3" required placeholder="e.g. Emergency, Medical, Family function" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm focus:outline-none transition-colors resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('apply-leave-modal').classList.add('hidden')" class="px-6 py-2.5 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Update end_date min value when start_date changes
    document.getElementById('start_date').addEventListener('change', function() {
        const startDate = this.value;
        const endDateInput = document.getElementById('end_date');
        
        if (startDate) {
            endDateInput.min = startDate;
            
            // If end_date is before start_date, clear it
            if (endDateInput.value && endDateInput.value < startDate) {
                endDateInput.value = '';
            }
        }
    });
</script>
@endsection
