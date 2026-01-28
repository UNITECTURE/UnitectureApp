@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300 min-w-0">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-4 sm:px-6 py-4 sm:py-6 md:py-8">
                    <!-- Header -->
                    <div class="mb-4 sm:mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="min-w-0">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Create Project</h2>
                            <p class="text-slate-400 text-xs sm:text-sm mt-1 font-medium hidden sm:block">Add a new project to the system</p>
                        </div>
                        <a href="{{ route('projects.index') }}"
                            class="text-slate-500 hover:text-slate-700 font-medium text-xs sm:text-sm flex items-center gap-1.5 sm:gap-2 whitespace-nowrap shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span class="hidden sm:inline">Back to Projects</span>
                            <span class="sm:hidden">Back</span>
                        </a>
                    </div>

                    <div class="max-w-4xl mx-auto">
                        <form action="{{ route('projects.store') }}" method="POST"
                            class="bg-white rounded-xl sm:rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
                            @csrf

                            <!-- Form Header -->
                            <div class="px-4 sm:px-6 md:px-8 py-4 sm:py-5 md:py-6 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-blue-50">
                                <h3 class="text-lg sm:text-xl font-bold text-slate-800">Project Information</h3>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">Fill in the details to create a new project</p>
                            </div>

                            <!-- Form Body -->
                            <div class="px-4 sm:px-6 md:px-8 py-4 sm:py-5 md:py-6 space-y-4 sm:space-y-5 md:space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6">
                                    <!-- Dept -->
                                    <div>
                                        <label for="department"
                                            class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Department</label>
                                        <input type="text" name="department" id="department" value="{{ old('department') }}"
                                            class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                            placeholder="e.g. Architecture" required>
                                        @error('department') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Location -->
                                    <div>
                                        <label for="location"
                                            class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Location</label>
                                        <input type="text" name="location" id="location" value="{{ old('location') }}"
                                            class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                            placeholder="e.g. New York Office" required>
                                        @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Project ID -->
                                    <div>
                                        <label for="project_custom_id"
                                            class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Project ID</label>
                                        <input type="text" name="project_custom_id" id="project_custom_id"
                                            value="{{ old('project_custom_id') }}"
                                            class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                            placeholder="e.g. PID-001" required>
                                        @error('project_custom_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Project Code -->
                                    <div>
                                        <label for="project_code" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Project Code</label>
                                        <input type="text" name="project_code" id="project_code"
                                            value="{{ old('project_code') }}"
                                            class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                            placeholder="e.g. PRJ-2024-A" required>
                                        @error('project_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Project Name -->
                                    <div class="sm:col-span-2">
                                        <label for="name" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Project Name</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                                            class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                            placeholder="Enter full project name" required>
                                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Start Date -->
                                    <div>
                                        <label for="start_date" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                                        <div class="relative">
                                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                                                class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 pl-9 sm:pl-10 text-slate-800 bg-slate-50 transition-all duration-200"
                                                required>
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 sm:pl-3">
                                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- End Date -->
                                    <div>
                                        <label for="end_date" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">
                                            End Date
                                            <span class="text-slate-400 font-normal">(Optional)</span>
                                        </label>
                                        <div class="relative">
                                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                                class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 pl-9 sm:pl-10 text-slate-800 bg-slate-50 transition-all duration-200">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 sm:pl-3">
                                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="sm:col-span-2">
                                        <label for="description"
                                            class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">Description</label>
                                        <textarea name="description" id="description" rows="4"
                                            class="block w-full rounded-lg sm:rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200 resize-none"
                                            placeholder="Detailed description of the project..."
                                            required>{{ old('description') }}</textarea>
                                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Form Footer -->
                            <div class="px-4 sm:px-6 md:px-8 py-4 sm:py-5 md:py-6 border-t border-slate-100 bg-slate-50 flex justify-end">
                                <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 sm:py-3 px-6 sm:px-8 rounded-lg shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 text-sm sm:text-base">
                                    Create Project
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection