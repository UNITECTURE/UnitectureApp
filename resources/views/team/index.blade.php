@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-slate-800">My Team</h2>
                        <p class="text-slate-400 text-sm mt-1 font-medium">Overview of your team members</p>
                    </div>

                    @if($team->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-slate-100 shadow-sm">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700">No Team Members Found</h3>
                            <p class="text-slate-400 text-sm mt-1">You don't have any team members assigned to you yet.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($team as $member)
                                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-200 p-6 group">
                                    <div class="flex items-start gap-4">
                                        <!-- Profile Image -->
                                        <div class="shrink-0">
                                            <img src="{{ $member->profile_image ? asset('storage/' . $member->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($member->full_name) . '&background=6366f1&color=fff&size=128' }}" 
                                                alt="{{ $member->full_name }}" 
                                                class="w-16 h-16 rounded-full border border-slate-200 object-cover shadow-sm">
                                        </div>

                                        <!-- Member Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-1">
                                                <div>
                                                    <h3 class="text-lg font-bold text-slate-900 leading-tight truncate pr-2" title="{{ $member->full_name }}">
                                                        {{ $member->full_name }}
                                                    </h3>
                                                    <p class="text-sm text-indigo-600 font-medium">{{ $member->role->name ?? 'Employee' }}</p>
                                                </div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded textxs font-bold capitalize shrink-0
                                                    {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ $member->status }}
                                                </span>
                                            </div>

                                            <div class="space-y-2 mt-3">
                                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="truncate block" title="{{ $member->email }}">{{ $member->email }}</span>
                                                </div>
                                                
                                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="truncate">Joined {{ \Carbon\Carbon::parse($member->joining_date)->format('M d, Y') }}</span>
                                                </div>

                                                @if($member->telegram_chat_id)
                                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                                    <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                    </svg>
                                                    <span class="truncate">Telegram Linked</span>
                                                </div>
                                                @endif
                                            </div>

                                            <div class="mt-4 pt-3 border-t border-slate-50 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                                <span>ID: #{{ $member->id }}</span>
                                                <span>BioID: {{ $member->biometric_id ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
@endsection
