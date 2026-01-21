@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
    <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

    <div class="flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB] p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Manage Holidays</h2>
                    <p class="text-slate-500 text-sm">Add or remove holidays for the organization.</p>
                </div>
                <a href="{{ route('settings.index') }}" class="text-slate-500 hover:text-slate-700 text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Add Holiday Form -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Add Holiday</h3>
                    <form action="{{ route('holidays.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Holiday Name</label>
                            <input type="text" name="name" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. New Year">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" name="date" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description (Optional)</label>
                            <textarea name="description" rows="2" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition-colors">
                            Add Holiday
                        </button>
                    </form>
                </div>

                <!-- Holidays List -->
                <div class="md:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Upcoming Holidays</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-slate-500 uppercase font-medium text-xs">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Description</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($holidays as $holiday)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-800">
                                        {{ $holiday->date->format('M d, Y') }} <span class="text-xs text-slate-400 ml-1">({{ $holiday->date->format('l') }})</span>
                                    </td>
                                    <td class="px-6 py-4">{{ $holiday->name }}</td>
                                    <td class="px-6 py-4 text-slate-400 text-xs">{{ $holiday->description ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">No holidays added yet.</td>
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
@endsection
