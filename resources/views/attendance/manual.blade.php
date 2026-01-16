@extends('layouts.app')

@section('content')
<div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">
    {{-- Sidebar --}}
    <x-sidebar :role="$role" />

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
        {{-- Top Header --}}
        <div class="flex items-center justify-between px-8 py-6 bg-white shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="history.back()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Manual Attendance</h1>
                    <p class="text-slate-500 text-sm mt-1">View status and apply for missed attendance.</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('logout') }}" class="text-slate-500 hover:text-red-600 font-medium text-sm transition-colors">
                    {{ 'Sign Out' }}
                </a>
            </div>
        </div>

        {{-- Content Scroll Area --}}
        <div class="flex-1 overflow-x-hidden overflow-y-auto px-8 pb-8">
            <div class="mt-8">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">My Requests</h3>
                            <p class="text-sm text-slate-500 mt-1">Track the status of your manual attendance applications</p>
                        </div>
                        <button onclick="window.dispatchEvent(new CustomEvent('open-manual-attendance-modal'))" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center shadow-lg shadow-blue-500/20 active:scale-95 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Apply Record
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        @if(isset($myRequests) && $myRequests->count() > 0)
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Requested On</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($myRequests as $req)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 font-medium">
                                            {{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ $req->duration }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate" title="{{ $req->reason }}">
                                            {{ $req->reason ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClass = match($req->status) {
                                                    'approved' => 'bg-green-100 text-green-700 border-green-200',
                                                    'rejected' => 'bg-red-50 text-red-600 border-red-100',
                                                    default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ $req->created_at->format('M d, Y h:i A') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-4">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <h3 class="text-sm font-medium text-slate-900">No requests found</h3>
                                <p class="mt-1 text-sm text-slate-500">Apply for manual attendance using the button above.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<x-manual-attendance-modal />
@endsection
