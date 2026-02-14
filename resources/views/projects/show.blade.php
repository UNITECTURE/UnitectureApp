@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" >
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('projects.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                            </a>
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800">{{ $project->name }}</h2>
                                <p class="text-slate-400 text-sm mt-1 font-medium">Project details</p>
                            </div>
                        </div>
                        @if(Auth::user()->isAdmin() || Auth::id() === (int) $project->created_by)
                            <a href="{{ route('projects.edit', $project) }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Project
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Project Code</p>
                                        <p class="text-lg font-bold text-slate-800">{{ $project->project_code }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                        {{ $project->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $project->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $project->status === 'archived' ? 'bg-slate-100 text-slate-700' : '' }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="text-slate-600 font-medium">{{ $project->department }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-slate-600 font-medium">{{ $project->location }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-slate-600 font-medium">
                                            {{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}
                                            @if($project->end_date)
                                                - {{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}
                                            @else
                                                - Ongoing
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span class="text-slate-600 font-medium">Created by {{ $project->creator->name ?? 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6">
                                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Description</h3>
                                <p class="text-slate-700 text-sm leading-relaxed">{{ $project->description ?? 'No description provided.' }}</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6">
                                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Project ID</h3>
                                <p class="text-lg font-bold text-slate-800">{{ $project->project_custom_id ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6">
                                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Status</h3>
                                <p class="text-slate-700 text-sm">{{ ucfirst($project->status) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
