@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-slate-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        {{-- Sidebar --}}
        <x-sidebar :role="$role ?? 'admin'" />

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            {{-- Top Navigation --}}
            <header class="bg-white border-b border-slate-200 z-10 transition-all duration-300">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-600 focus:outline-none mr-4 lg:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1 class="text-xl font-bold text-slate-800">Attendance Exception</h1>
                    </div>
                    {{-- User Profile Dropdown or Actions could go here --}}
                </div>
            </header>

            {{-- Content Area --}}
            <main class="flex-1 overflow-y-auto p-6 bg-[#F8F9FB]">
                <div class="max-w-2xl mx-auto">
                    
                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center shadow-sm">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded-xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/30">
                            <h2 class="text-lg font-bold text-slate-800">Mark Attendance Exception</h2>
                            <p class="text-sm text-slate-500 mt-1">Exempt an employee from biometric rules for a specific day. This will set their duration to 9 Hours.</p>
                        </div>

                        <form action="{{ route('admin.attendance.storeException') }}" method="POST" class="p-8 space-y-6">
                            @csrf
                            
                            {{-- Employee Selector --}}
                            <div>
                                <label for="user_id" class="block text-sm font-semibold text-slate-700 mb-2">Select Employee</label>
                                <div class="relative">
                                    <select name="user_id" id="user_id" required 
                                        class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-3 px-4 shadow-sm transition-colors">
                                        <option value="">-- Choose Employee --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Date Selector --}}
                            <div>
                                <label for="date" class="block text-sm font-semibold text-slate-700 mb-2">Select Date</label>
                                <input type="date" name="date" id="date" required
                                    max="{{ date('Y-m-d') }}"
                                    class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-3 px-4 shadow-sm transition-colors">
                            </div>

                            {{-- Actions --}}
                            <div class="pt-6 flex justify-end">
                                <button type="submit" 
                                    class="inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-blue-200/50">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Mark as Exempted
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
