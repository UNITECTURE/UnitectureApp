@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Manage Users</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">View all users. Manage teams from the Teams page.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('teams.index') }}" class="px-4 py-2 rounded-lg border border-indigo-200 text-indigo-700 text-sm font-medium hover:bg-indigo-50">Teams</a>
                            <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Add User</a>
                            <a href="{{ route('settings.index') }}" class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Back
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" 
                             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-base font-medium">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)"
                             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-base font-medium">{{ session('error') }}</div>
                    @endif

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">User</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Role</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Primary Supervisor</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Secondary Supervisor</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $u)
                                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    @php
                                                        $avatarUrl = $u->profile_image && filter_var($u->profile_image, FILTER_VALIDATE_URL) ? $u->profile_image : ($u->profile_image ? asset('storage/' . $u->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($u->full_name) . '&background=94a3b8&color=fff&size=40');
                                                    @endphp
                                                    <img src="{{ $avatarUrl }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                                    <div>
                                                        <p class="font-medium text-slate-800">{{ $u->full_name }}</p>
                                                        <p class="text-xs text-slate-500">{{ $u->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ ucfirst($u->role->name ?? '—') }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $u->primarySupervisor ? $u->primarySupervisor->full_name : '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $u->secondarySupervisor ? $u->secondarySupervisor->full_name : '—' }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-4">
                                                    <a href="{{ route('users.edit', $u->id) }}"
                                                        class="px-3 py-1.5 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">Edit</a>
                                                    @if($u->id !== Auth::id())
                                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete {{ $u->full_name }}? This cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="px-3 py-1.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
