@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Settings</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">Manage your application settings</p>
                        </div>
                        <a href="{{ route('dashboard') }}"
                            class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>

                    <div class="max-w-4xl mx-auto space-y-6">


                        @if(Auth::user()->isSupervisor())
                            <!-- Supervisor Settings -->
                            <div
                                class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6">
                                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    Project Management
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <a href="{{ route('projects.create') }}"
                                        class="group flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/50 transition-all duration-200">
                                        <div>
                                            <h4 class="font-bold text-slate-700 group-hover:text-indigo-700">Create New Project
                                            </h4>
                                            <p class="text-xs text-slate-400 mt-1">Initialize a new project within the system
                                            </p>
                                        </div>
                                        <div
                                            class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if(Auth::user()->isAdmin())
                        <!-- Admin Settings -->
                        <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6 mb-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                System Configuration
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <a href="{{ route('holidays.index') }}" class="group flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/50 transition-all duration-200">
                                    <div>
                                        <h4 class="font-bold text-slate-700 group-hover:text-indigo-700">Manage Holidays</h4>
                                        <p class="text-xs text-slate-400 mt-1">Add or remove system holidays</p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                </a>
                                <a href="{{ route('teams.index') }}" class="group flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/50 transition-all duration-200">
                                    <div>
                                        <h4 class="font-bold text-slate-700 group-hover:text-indigo-700">Teams</h4>
                                        <p class="text-xs text-slate-400 mt-1">View teams, supervisors, members; assign secondary supervisor</p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </a>
                                <a href="{{ route('users.manage') }}" class="group flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/50 transition-all duration-200">
                                    <div>
                                        <h4 class="font-bold text-slate-700 group-hover:text-indigo-700">Manage Users</h4>
                                        <p class="text-xs text-slate-400 mt-1">View all users, primary/secondary supervisor, delete</p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Password Settings --}}
                        <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Security Settings
                            </h3>
                            <button onclick="document.getElementById('changePasswordModal').classList.remove('hidden')" class="text-red-600 hover:text-red-700 font-medium flex items-center gap-2">
                                Change Password
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- Change Password Modal --}}
    <div id="changePasswordModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">Change Password</h3>
                <button onclick="document.getElementById('changePasswordModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form action="{{ route('settings.updatePassword') }}" method="POST" class="p-6 space-y-4">
                @csrf

                {{-- Email (Read-only) --}}
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-2">Email</label>
                    <input type="email" value="{{ Auth::user()->email }}" disabled class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 text-slate-600 cursor-not-allowed">
                </div>

                {{-- New Password --}}
                <div>
                    <label for="new_password" class="text-sm font-semibold text-slate-700 block mb-2">New Password</label>
                    <input type="password" id="new_password" name="new_password" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Enter new password">
                    @error('new_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="new_password_confirmation" class="text-sm font-semibold text-slate-700 block mb-2">Confirm Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Confirm new password">
                    @error('new_password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Success/Error Messages --}}
                @if(session('success'))
                    <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Modal Footer --}}
                <div class="flex gap-3 pt-4 border-t border-slate-200">
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection