@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <!-- Header -->
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Projects Overview</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">View all projects created by supervisors</p>
                        </div>
                        @if(Auth::user()->isSupervisor())
                            <a href="{{ route('projects.create') }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Project
                            </a>
                        @endif
                    </div>

                    <!-- Projects Grid -->
                    @if($projects->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projects as $project)
                                <a href="{{ route('projects.show', $project) }}"
                                    class="block bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 p-6 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.1)] transition-all cursor-pointer group">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors">{{ $project->name }}</h3>
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $project->project_code }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                            {{ $project->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $project->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $project->status === 'archived' ? 'bg-slate-100 text-slate-700' : '' }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-3 mb-4">
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

                                    <!-- Description -->
                                    @if($project->description)
                                        <div class="mb-4">
                                            <p class="text-sm text-slate-600 line-clamp-2">{{ $project->description }}</p>
                                        </div>
                                    @endif

                                    <!-- Footer -->
                                    <div class="pt-4 border-t border-slate-100">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Project ID</span>
                                            <span class="text-xs font-bold text-slate-700">{{ $project->project_custom_id ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <p class="text-slate-500 font-medium mb-4">No projects found</p>
                            @if(Auth::user()->isSupervisor())
                                <a href="{{ route('projects.create') }}"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-indigo-200 transition-all">
                                    Create Your First Project
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
@endsection
