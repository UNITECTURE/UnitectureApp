@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->role->name ?? 'employee'" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">

                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Task Overview</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">Manage and track project tasks</p>
                        </div>
                        @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                            <a href="{{ route('tasks.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm shadow-indigo-200 transition-all gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Task
                            </a>
                        @endif
                    </div>

                    <!-- Stats/Filters (Optional placeholder) -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-50 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">Total Tasks</p>
                                <p class="text-xl font-bold text-slate-800">{{ $tasks->count() }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <!-- Add more stats if needed -->
                    </div>

                    <!-- Task List -->
                    <div
                        class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr
                                        class="bg-slate-50/50 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-400 font-bold">
                                        <th class="px-6 py-4">Task Name</th>
                                        <th class="px-6 py-4">Project</th>
                                        <th class="px-6 py-4">Priority</th>
                                        <th class="px-6 py-4">Assignees</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Due Date</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @forelse($tasks as $task)
                                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-700">{{ $task->title }}</div>
                                                <div class="text-xs text-slate-400 mt-1 line-clamp-1">
                                                    {{ Str::limit($task->description, 40) }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-slate-600">{{ $task->project->name }}</div>
                                                <div class="text-xs text-slate-400">{{ $task->project->project_code }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $priorityColors = [
                                                        'high' => 'text-red-600 bg-red-50',
                                                        'medium' => 'text-orange-600 bg-orange-50',
                                                        'low' => 'text-green-600 bg-green-50',
                                                        'free' => 'text-slate-600 bg-slate-50',
                                                    ];
                                                    $colorClass = $priorityColors[$task->priority] ?? 'text-slate-600 bg-slate-50';
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $colorClass }}">
                                                    {{ $task->priority }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex -space-x-2 overflow-hidden">
                                                    @foreach($task->assignees->take(3) as $assignee)
                                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600"
                                                            title="{{ $assignee->name }}">
                                                            {{ substr($assignee->name, 0, 1) }}
                                                        </div>
                                                    @endforeach
                                                    @if($task->assignees->count() > 3)
                                                        <div
                                                            class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">
                                                            +{{ $task->assignees->count() - 3 }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 capitalize">
                                                    {{ str_replace('_', ' ', $task->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                                                {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('M d, Y') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button class="text-slate-400 hover:text-indigo-600 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                                No tasks found.
                                                @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                                                    <a href="{{ route('tasks.create') }}"
                                                        class="text-indigo-600 hover:underline">Create one now</a>
                                                @endif
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
@endsection