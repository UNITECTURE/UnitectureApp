@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <!-- Header -->
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Projects Overview</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">View all projects created by supervisors</p>
                        </div>
                        @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                            <div class="flex items-center gap-3">
                                <a href="{{ route('projects.index', ['view' => request('view') === 'parked' ? 'active' : 'parked']) }}"
                                    class="text-slate-600 hover:text-indigo-600 font-bold py-2.5 px-6 rounded-lg border border-slate-200 bg-white shadow-sm transition-all flex items-center justify-center gap-2">
                                    @if(request('view') === 'parked')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Show Active
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                        </svg>
                                        Show Parked
                                    @endif
                                </a>
                                <a href="{{ route('projects.create') }}"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Project
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="mb-8">
                        <form id="projectSearchForm" action="{{ route('projects.index') }}" method="GET"
                            class="w-full sm:w-80">
                            <div class="relative">
                                <input type="text" id="projectSearchInput" name="q" value="{{ $search ?? '' }}"
                                    placeholder="Search by title, code, or ID"
                                    class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none">
                                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </form>
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
                                            <h3
                                                class="text-lg font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors">
                                                {{ $project->name }}
                                            </h3>
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                                {{ $project->project_code }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                                                                    {{ $project->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                                                                    {{ $project->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                                                                                    {{ $project->status === 'archived' ? 'bg-slate-100 text-slate-700' : '' }}">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                            @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                                                @if(request('view') === 'parked')
                                                    <form action="{{ route('projects.unpark', $project->id) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Are you sure you want to unpark this project?');">
                                                        @csrf
                                                        <button type="submit" @click.stop
                                                            class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all"
                                                            title="Unpark Project">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('projects.park', $project) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Are you sure you want to park this project? It will be hidden from all lists and views.');">
                                                        @csrf
                                                        <button type="submit" @click.stop
                                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                                            title="Park Project">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-3 mb-4">
                                        <div class="flex items-center gap-2 text-sm">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                </path>
                                            </svg>
                                            <span class="text-slate-600 font-medium">{{ $project->department }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="text-slate-600 font-medium">{{ $project->location }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
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
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="text-slate-600 font-medium">Created by
                                                {{ $project->creator->name ?? 'Unknown' }}</span>
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
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Project
                                                ID</span>
                                            <span
                                                class="text-xs font-bold text-slate-700">{{ $project->project_custom_id ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <div
                                class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-slate-500 font-medium mb-4">No projects found</p>
                            @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
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

@section('scripts')
    <script>
        // Live search functionality (debounced)
        let projectSearchTimeout;
        const projectSearchInput = document.getElementById('projectSearchInput');
        const projectSearchForm = document.getElementById('projectSearchForm');

        if (projectSearchInput && projectSearchForm) {
            projectSearchInput.addEventListener('input', function () {
                clearTimeout(projectSearchTimeout);
                projectSearchTimeout = setTimeout(() => {
                    projectSearchForm.submit();
                }, 500);
            });
        }
    </script>
@endsection