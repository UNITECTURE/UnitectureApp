@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <!-- Header -->
                    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('projects.index') }}"
                                class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Projects
                            </a>
                        </div>
                        @if(Auth::id() == $project->created_by || Auth::user()->isAdmin())
                            <a href="{{ route('projects.edit', $project) }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Project
                            </a>
                        @endif
                    </div>

                    <!-- Project Details Card -->
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
                            <!-- Title & Status -->
                            <div class="p-6 sm:p-8 border-b border-slate-100">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-2">{{ $project->name }}</h1>
                                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ $project->project_code }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold
                                        {{ $project->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $project->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $project->status === 'archived' ? 'bg-slate-100 text-slate-700' : '' }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="p-6 sm:p-8 space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Project ID</p>
                                        <p class="text-slate-800 font-medium">{{ $project->project_custom_id ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Department</p>
                                        <p class="text-slate-800 font-medium">{{ $project->department }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Location</p>
                                        <p class="text-slate-800 font-medium">{{ $project->location }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Start Date</p>
                                        <p class="text-slate-800 font-medium">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">End Date</p>
                                        <p class="text-slate-800 font-medium">
                                            @if($project->end_date)
                                                {{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}
                                            @else
                                                Ongoing
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Created by (Supervisor)</p>
                                        <p class="text-slate-800 font-medium">{{ $project->creator->name ?? 'Unknown' }}</p>
                                        @if($project->creator && $project->creator->email)
                                            <p class="text-sm text-slate-500 mt-0.5">{{ $project->creator->email }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if($project->description)
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description</p>
                                        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $project->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
