@extends('layouts.app')

@section('content')
    @php
        $role = $role ?? 'admin';
        // $summary and $requests array are passed from controller
    @endphp

    <div  class="flex h-screen overflow-hidden bg-[#F8F9FB] font-sans">

        {{-- Sidebar Component --}}
        <x-sidebar :role="$role" />

        {{-- Main Content --}}
        <main class="flex-1 flex flex-col h-full overflow-hidden relative z-0">
            {{-- Top Header --}}
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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    {{-- All --}}
                    {{-- All --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'All']) }}"
                        class="block w-full text-left bg-white rounded-lg shadow border border-slate-100 overflow-hidden hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="h-1 bg-blue-500"></div>
                        <div class="p-5 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Total Requests
                                </p>
                                <p class="text-3xl font-bold text-blue-600">{{ $summary['all'] }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    {{-- Pending --}}
                    {{-- Pending --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'Pending']) }}"
                        class="block w-full text-left bg-white rounded-lg shadow border border-slate-100 overflow-hidden hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="h-1 bg-orange-500"></div>
                        <div class="p-5 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Pending</p>
                                <p class="text-3xl font-bold text-orange-500">{{ $summary['pending'] }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    {{-- Approved --}}
                    {{-- Approved --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'Approved']) }}"
                        class="block w-full text-left bg-white rounded-lg shadow border border-slate-100 overflow-hidden hover:border-green-300 hover:shadow-md transition-all">
                        <div class="h-1 bg-green-500"></div>
                        <div class="p-5 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Approved</p>
                                <p class="text-3xl font-bold text-green-600">{{ $summary['approved'] }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    {{-- Rejected --}}
                    {{-- Rejected --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'Rejected']) }}"
                        class="block w-full text-left bg-white rounded-lg shadow border border-slate-100 overflow-hidden hover:border-red-300 hover:shadow-md transition-all">
                        <div class="h-1 bg-red-500"></div>
                        <div class="p-5 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-2">Rejected</p>
                                <p class="text-3xl font-bold text-red-600">{{ $summary['rejected'] }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m2-2l2 2"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Filter & Search Bar --}}
                <form action="" method="GET" class="flex items-center gap-4 mb-8">
                    <input type="hidden" name="status" value="{{ request('status', 'All') }}">

                    {{-- Search Bar --}}
                    <div class="relative flex-1 max-w-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by Employee name"
                            class="block w-full pl-11 pr-3 py-2.5 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:placeholder-slate-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm shadow-sm transition-shadow duration-200">
                    </div>
                </form>

                {{-- Approvals Table --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Name</th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        {{ 'Applied Date' }}
                                    </th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        {{ 'Requested Date' }}
                                    </th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Time Range</th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Duration</th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/4">
                                        Reason</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse($requests as $request)
                                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                                            <td class="px-3 py-3 whitespace-nowrap">
                                                                <div class="flex items-center">
                                                                    <div
                                                                        class="flex-shrink-0 h-9 w-9 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 text-xs font-bold border border-slate-200">
                                                                        {{ substr($request->user->name, 0, 2) }}
                                                                    </div>
                                                                    <div class="ml-3">
                                                                        <div class="text-sm font-medium text-slate-900">{{ $request->user->name }}
                                                                        </div>
                                                                        <div class="text-xs text-slate-500">
                                                                            {{ucfirst($request->user->role?->name ?? 'Employee') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-600">
                                                                {{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}
                                                                <div class="text-xs text-slate-400">
                                                                    {{ \Carbon\Carbon::parse($request->created_at)->format('h:i A') }}
                                                                </div>
                                                            </td>
                                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-600">
                                                                {{ \Carbon\Carbon::parse($request->date)->format('M d, Y') }}
                                                            </td>
                                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-600">
                                                                {{ $request->start_time ? \Carbon\Carbon::parse($request->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($request->end_time)->format('h:i A') : '-' }}
                                                            </td>
                                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-600 font-medium">
                                                                {{ $request->duration }}
                                                            </td>
                                                            <td class="px-3 py-3 text-sm text-slate-600 min-w-[200px] max-w-[300px]">
                                                                <p class="whitespace-normal break-words w-full">
                                                                    {{ $request->reason ?? '-' }}
                                                                </p>
                                                            </td>
                                                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                                                <span
                                                                    class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium
                                                                                                                                                        {{ $request->status === 'approved' ? 'bg-green-100 text-green-600' :
                                    ($request->status === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-yellow-50 text-yellow-700') }}">
                                                                    {{ ucfirst($request->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-3 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                                @php
                                                                    // $role passed from controller is string 'admin'. 
                                                                    // But we need strict logic: If Auth User is Admin (Role 2) AND Requester is Admin (Role 2) OR Requester is SELF, then Disable.
                                                                    $authUser = \Illuminate\Support\Facades\Auth::user();
                                                                    $isSelf = $request->user_id === $authUser->id;
                                                                    $isRequesterAdmin = $request->user->role_id === 2;
                                                                    $isAuthAdmin = $authUser->role_id === 2;

                                                                    $canAction = !($isSelf || ($isAuthAdmin && $isRequesterAdmin)); 
                                                                @endphp

                                                                @if($request->status === 'pending')
                                                                    @if($canAction)
                                                                        <div class="flex items-center justify-end gap-2">
                                                                            <form action="{{ route('attendance.manual.approve', $request->id) }}"
                                                                                method="POST" class="inline">
                                                                                @csrf
                                                                                <button type="submit"
                                                                                    class="px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded text-xs font-medium transition-colors shadow-sm">
                                                                                    Approve
                                                                                </button>
                                                                            </form>
                                                                            <form action="{{ route('attendance.manual.reject', $request->id) }}"
                                                                                method="POST" class="inline">
                                                                                @csrf
                                                                                <button type="submit"
                                                                                    class="px-3 py-1.5 bg-white border border-red-500 text-red-600 hover:bg-red-50 rounded text-xs font-medium transition-colors">
                                                                                    Reject
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-xs text-slate-400 italic">
                                                                            {{ $isSelf ? 'Cannot approve own request' : 'Requires Super Admin' }}
                                                                        </span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-slate-400 text-xs text-right block">Completed</span>
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
                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
    </div>
    </main>
    </div>
@endsection