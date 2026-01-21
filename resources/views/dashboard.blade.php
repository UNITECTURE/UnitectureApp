@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <div class="space-y-8">
                        <!-- Welcome Header -->
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Welcome Back, {{ Auth::user()->name }}</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">Logged In As
                                {{ ucfirst(Auth::user()->role->name ?? 'Admin') }}
                            </p>
                        </div>

                        <!-- Action Cards Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <!-- Card 1: Task Dashboard -->
                            <div
                                class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8 flex flex-col items-center text-center hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-[#eff6ff] text-[#3b82f6] flex items-center justify-center mb-6">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-900 text-lg mb-3">Task Dashboard</h3>
                                <p class="text-sm text-slate-500 mb-8 max-w-[220px] leading-relaxed font-medium">Clear view
                                    of performance anytime.</p>
                                <a href="{{ route('tasks.index') }}"
                                    class="w-full py-3 px-4 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-200 block">
                                    Open
                                </a>
                            </div>

                            <!-- Card 2: Automate Leaves -->
                            <div
                                class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8 flex flex-col items-center text-center hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-[#f0fdf4] text-[#22c55e] flex items-center justify-center mb-6">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-900 text-lg mb-3">Automate Leaves</h3>
                                <p class="text-sm text-slate-500 mb-8 max-w-[220px] leading-relaxed font-medium">Manage your
                                    employee leaves and holidays.</p>
                                <a href="{{ route('leaves.index') }}"
                                    class="w-full py-3 px-4 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-200 text-center">
                                    Open
                                </a>
                            </div>

                            <!-- Card 3: Automate Attendance -->
                            <div
                                class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8 flex flex-col items-center text-center hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-[#f3e8ff] text-[#a855f7] flex items-center justify-center mb-6">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-900 text-lg mb-3">Automate Attendance</h3>
                                <p class="text-sm text-slate-500 mb-8 max-w-[220px] leading-relaxed font-medium">Track your
                                    teams Attendance and break.</p>
                                @php
                                    $attendanceRoute = '#';
                                    if (Auth::user()->isAdmin())
                                        $attendanceRoute = route('admin.attendance.all');
                                    elseif (Auth::user()->isSupervisor())
                                        $attendanceRoute = route('supervisor.attendance.team');
                                    else
                                        $attendanceRoute = route('employee.attendance');
                                @endphp
                                <a href="{{ $attendanceRoute }}"
                                    class="w-full py-3 px-4 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-200 block">
                                    Open
                                </a>
                            </div>

                            @if(!Auth::user()->isEmployee())
                                <!-- Card 4: Team Management -->
                                <div
                                    class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8 flex flex-col items-center text-center hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-[#ffedd5] text-[#f97316] flex items-center justify-center mb-6">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="font-bold text-slate-900 text-lg mb-3">Team Management</h3>
                                    <p class="text-sm text-slate-500 mb-8 max-w-[220px] leading-relaxed font-medium">Manage your
                                        team members and groups.</p>
                                    <button
                                        class="w-full py-3 px-4 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-200">
                                        Open
                                    </button>
                                </div>

                                <!-- Card 5: Settings -->
                                <div
                                    class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-8 flex flex-col items-center text-center hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-[#eff6ff] text-[#3b82f6] flex items-center justify-center mb-6">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="font-bold text-slate-900 text-lg mb-3">Settings</h3>
                                    <p class="text-sm text-slate-500 mb-8 max-w-[220px] leading-relaxed font-medium">Configure
                                        system and roles.</p>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('users.create') }}"
                                            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-indigo-200 text-center block">
                                            Add New User
                                        </a>
                                    @else
                                        <a href="{{ route('settings.index') }}"
                                            class="w-full py-3 px-4 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-200 block">
                                            Open
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Stats Row -->
                        @if(!Auth::user()->isEmployee())
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-8">
                                <div
                                    class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6 flex flex-col justify-between h-32 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Pending Approvals</p>
                                    <p class="text-4xl font-extrabold text-[#F97316]">5</p>
                                </div>
                                <div
                                    class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6 flex flex-col justify-between h-32 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Present Today</p>
                                    <p class="text-4xl font-extrabold text-[#22C55E]">24</p>
                                </div>
                                <div
                                    class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6 flex flex-col justify-between h-32 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">On Leave</p>
                                    <p class="text-4xl font-extrabold text-[#3B82F6]">3</p>
                                </div>
                                <div
                                    class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6 flex flex-col justify-between h-32 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-shadow duration-300">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Active Tasks</p>
                                    <p class="text-4xl font-extrabold text-[#2563EB]">12</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection