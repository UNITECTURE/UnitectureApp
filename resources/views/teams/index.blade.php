@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Teams</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">View teams, supervisors, and members. Assign secondary supervisor or remove members.</p>
                        </div>
                        <a href="{{ route('settings.index') }}" class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Back to Settings
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
                    @endif

                    @forelse($supervisors as $sup)
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
                            <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    @php
                                        $avatarUrl = $sup->profile_image && filter_var($sup->profile_image, FILTER_VALIDATE_URL)
                                            ? $sup->profile_image
                                            : ($sup->profile_image ? asset('storage/' . $sup->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($sup->full_name) . '&background=6366f1&color=fff&size=64');
                                    @endphp
                                    <img src="{{ $avatarUrl }}" alt="{{ $sup->full_name }}" class="w-12 h-12 rounded-full border border-slate-200 object-cover">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800">{{ $sup->full_name }}</h3>
                                        <p class="text-sm text-slate-500">Supervisor · {{ $sup->subordinates->count() }} primary member(s), {{ $sup->secondarySubordinates->count() }} secondary</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 sm:p-6">
                                <h4 class="text-sm font-semibold text-slate-600 mb-3">Team members (primary)</h4>
                                @if($sup->subordinates->isEmpty())
                                    <p class="text-slate-400 text-sm">No primary members.</p>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($sup->subordinates as $member)
                                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/30">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    @php
                                                        $mUrl = $member->profile_image && filter_var($member->profile_image, FILTER_VALIDATE_URL) ? $member->profile_image : ($member->profile_image ? asset('storage/' . $member->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($member->full_name) . '&background=94a3b8&color=fff&size=48');
                                                    @endphp
                                                    <img src="{{ $mUrl }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                                                    <div class="min-w-0">
                                                        <p class="font-medium text-slate-800 truncate">{{ $member->full_name }}</p>
                                                        <p class="text-xs text-slate-500 truncate">{{ $member->email }}</p>
                                                        @if($member->secondarySupervisor)
                                                            <p class="text-xs text-indigo-600 mt-0.5">Secondary: {{ $member->secondarySupervisor->full_name }}</p>
                                                        @else
                                                            <p class="text-xs text-slate-400 mt-0.5">No secondary supervisor</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0 ml-2">
                                                    <form action="{{ route('teams.update-secondary-supervisor', $member) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="secondary_supervisor_id" onchange="this.form.submit()" class="text-xs rounded border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="">— None —</option>
                                                            @foreach($supervisorsList as $s)
                                                                @if($s->id !== $member->id && $s->id !== $member->reporting_to)
                                                                    <option value="{{ $s->id }}" {{ (int) $member->secondary_supervisor_id === (int) $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                    <form action="{{ route('teams.remove-member', $member) }}" method="POST" class="inline" onsubmit="return confirm('Remove {{ $member->full_name }} from their team(s)?');">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium" title="Remove from team">Remove</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($sup->secondarySubordinates->isNotEmpty())
                                    <h4 class="text-sm font-semibold text-slate-600 mt-6 mb-3">Team members (secondary only)</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($sup->secondarySubordinates as $member)
                                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-indigo-50/30">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    @php
                                                        $mUrl = $member->profile_image && filter_var($member->profile_image, FILTER_VALIDATE_URL) ? $member->profile_image : ($member->profile_image ? asset('storage/' . $member->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($member->full_name) . '&background=6366f1&color=fff&size=48');
                                                    @endphp
                                                    <img src="{{ $mUrl }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                                                    <div class="min-w-0">
                                                        <p class="font-medium text-slate-800 truncate">{{ $member->full_name }}</p>
                                                        <p class="text-xs text-slate-500 truncate">Primary: {{ $member->primarySupervisor ? $member->primarySupervisor->full_name : '—' }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0 ml-2">
                                                    <form action="{{ route('teams.update-secondary-supervisor', $member) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="secondary_supervisor_id" onchange="this.form.submit()" class="text-xs rounded border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="">— None —</option>
                                                            @foreach($supervisorsList as $s)
                                                                @if($s->id !== $member->id && $s->id !== $member->reporting_to)
                                                                    <option value="{{ $s->id }}" {{ (int) $member->secondary_supervisor_id === (int) $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                    <form action="{{ route('teams.remove-member', $member) }}" method="POST" class="inline" onsubmit="return confirm('Remove {{ $member->full_name }} from their team(s)?');">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Remove</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                            <p class="text-slate-500">No supervisors found. Add users with the Supervisor role to see teams here.</p>
                        </div>
                    @endforelse
                </div>
            </main>
        </div>
    </div>
@endsection
