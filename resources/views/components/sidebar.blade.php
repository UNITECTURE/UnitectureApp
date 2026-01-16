@props(['role' => 'admin'])

@php
    $pendingAttendanceCount = 0;
    if ($role === 'admin') {
        $pendingAttendanceCount = \App\Models\ManualAttendanceRequest::where('status', 'pending')->count();
    } elseif ($role === 'supervisor') {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
             $teamIds = \App\Models\User::where('reporting_to', $user->id)->pluck('id');
             $pendingAttendanceCount = \App\Models\ManualAttendanceRequest::whereIn('user_id', $teamIds)->where('status', 'pending')->count();
        }
    }
@endphp

<aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="shrink-0 h-full bg-[#0B1221] text-white transition-all duration-300 ease-in-out flex flex-col shadow-2xl z-50 overflow-hidden">
    {{-- Sidebar Header --}}
    <div class="flex items-center justify-between h-16 px-6 border-b border-slate-700/50 shrink-0">
        <span x-show="sidebarOpen" x-transition class="text-lg font-semibold tracking-wide whitespace-nowrap">{{ 'MenuBar' }}</span>
        
        {{-- Toggle Button --}}
        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white focus:outline-none ml-auto p-1 rounded-md hover:bg-slate-800 transition-colors">
             <svg x-show="sidebarOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
             <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    {{-- Navigation --}}
    <div class="flex-1 overflow-y-auto py-4 overflow-x-hidden">
        <nav class="px-3 space-y-1">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md hover:bg-slate-800 hover:text-white group transition-colors relative"
               :class="!sidebarOpen ? 'justify-center' : ''">
                <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span x-show="sidebarOpen" x-transition class="truncate whitespace-nowrap">{{ 'Dashboard' }}</span>
                
                {{-- Tooltip for collapsed state --}}
                <div x-show="!sidebarOpen" class="absolute left-full ml-2 bg-slate-900 text-white text-xs px-2 py-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 whitespace-nowrap pointer-events-none shadow-lg border border-slate-700 font-medium">Dashboard</div>
            </a>

            {{-- Tasks (Admin Only) --}}
            @if($role === 'admin')
            <div x-data="{ open: false }" class="space-y-1">
                <div @click="sidebarOpen ? (open = !open) : (sidebarOpen = true)" 
                     class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-slate-300 rounded-md hover:bg-slate-800 hover:text-white group transition-colors duration-200 cursor-pointer relative"
                     :class="!sidebarOpen ? 'justify-center' : ''">
                    <div class="flex items-center flex-1 min-w-0" :class="!sidebarOpen ? 'justify-center' : ''">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span x-show="sidebarOpen" x-transition class="truncate whitespace-nowrap">{{ 'Tasks' }}</span>
                    </div>
                    <svg x-show="sidebarOpen" class="w-4 h-4 text-slate-500 transition-transform duration-200 ml-auto flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    
                    {{-- Tooltip for collapsed state --}}
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 bg-slate-900 text-white text-xs px-2 py-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 whitespace-nowrap pointer-events-none shadow-lg border border-slate-700 font-medium">Tasks</div>
                </div>
                
                {{-- Submenu - Only show when sidebar is open AND menu is expanded --}}
                <div x-show="open && sidebarOpen" x-transition style="display: none;" class="pl-11 space-y-1">
                    <a href="#" class="block px-3 py-1.5 text-sm text-slate-400 rounded-md hover:text-white hover:bg-slate-800 transition-colors truncate">{{ 'All Tasks' }}</a>
                    <a href="#" class="block px-3 py-1.5 text-sm text-slate-400 rounded-md hover:text-white hover:bg-slate-800 transition-colors truncate">{{ 'Assigned to Me' }}</a>
                </div>
            </div>
            @endif

            {{-- Attendance & Leave --}}
           <div x-data="{ 
                open: localStorage.getItem('sidebar_attendance_open') === 'true', 
                init() { this.$watch('open', val => localStorage.setItem('sidebar_attendance_open', val)) } 
            }" class="space-y-1">
                <div @click="sidebarOpen ? (open = !open) : (sidebarOpen = true)" 
                     class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-slate-300 rounded-md hover:bg-slate-800 hover:text-white group transition-colors duration-200 cursor-pointer relative"
                     :class="!sidebarOpen ? 'justify-center' : ''">
                    <div class="flex items-center flex-1 min-w-0" :class="!sidebarOpen ? 'justify-center' : ''">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span x-show="sidebarOpen" x-transition class="truncate whitespace-nowrap">{{ 'Attendance & Leave' }}</span>
                    </div>
                    <svg x-show="sidebarOpen" class="w-4 h-4 text-slate-500 transition-transform duration-200 ml-auto flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    
                    {{-- Tooltip for collapsed state --}}
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 bg-slate-900 text-white text-xs px-2 py-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 whitespace-nowrap pointer-events-none shadow-lg border border-slate-700 font-medium">Attendance & Leave</div>
                </div>
                
                {{-- Submenu - Only show when sidebar is open AND menu is expanded --}}
                <div x-show="open && sidebarOpen" x-transition class="pl-11 space-y-1">
                    <a href="{{ route('leaves.index') }}" class="block px-3 py-1.5 text-sm text-slate-400 rounded-md hover:text-white hover:bg-slate-800 transition-colors truncate">{{ 'My Leaves' }}</a>
                    
                    <div x-data="{ 
                        subOpen: localStorage.getItem('sidebar_attendance_sub_open') === 'true',
                        init() { this.$watch('subOpen', val => localStorage.setItem('sidebar_attendance_sub_open', val)) }
                    }">
                        <div @click="subOpen = !subOpen" class="flex items-center justify-between px-3 py-1.5 text-sm font-medium text-slate-400 rounded-md transition-colors cursor-pointer hover:bg-slate-800 hover:text-white">
                            <span class="truncate">{{ 'Attendance' }}</span>
                            <svg class="w-3 h-3 text-slate-400 transform transition-transform duration-200 flex-shrink-0 ml-2" :class="{'rotate-180': subOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        
                        <div x-show="subOpen" x-transition class="mt-1 ml-4 space-y-1 border-l border-slate-700" style="display: none;">
                            @if($role === 'admin')
                                <a href="{{ route('admin.attendance.self') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('admin.attendance.self') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'Self Attendance' }}</a>
                                <a href="{{ route('admin.attendance.all') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('admin.attendance.all') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'All Attendance' }}</a>
                            @elseif($role === 'supervisor')
                                <a href="{{ route('supervisor.attendance.self') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('supervisor.attendance.self') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'My Attendance' }}</a>
                                <a href="{{ route('supervisor.attendance.team') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('supervisor.attendance.team') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'Team Attendance' }}</a>
                                <a href="{{ route('attendance.manual') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('attendance.manual') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'Manual Request' }}</a>
                            @else
                                <a href="{{ route('employee.attendance') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('employee.attendance') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'My Attendance' }}</a>
                                <a href="{{ route('attendance.manual') }}" class="block pl-4 py-1 text-sm transition-colors truncate {{ request()->routeIs('attendance.manual') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px] hover:text-blue-300' : 'text-slate-400 hover:text-white' }}">{{ 'Manual Request' }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approvals --}}
            @if($role === 'admin' || $role === 'supervisor')
            <div x-data="{ 
                open: localStorage.getItem('sidebar_approvals_open') === 'true', 
                init() { this.$watch('open', val => localStorage.setItem('sidebar_approvals_open', val)) } 
            }" class="space-y-1">
                <div @click="sidebarOpen ? (open = !open) : (sidebarOpen = true)" 
                     class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-slate-300 rounded-md hover:bg-slate-800 hover:text-white group transition-colors duration-200 cursor-pointer relative"
                     :class="!sidebarOpen ? 'justify-center' : ''">
                    <div class="flex items-center flex-1 min-w-0" :class="!sidebarOpen ? 'justify-center' : ''">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-show="sidebarOpen" x-transition class="truncate whitespace-nowrap">{{ 'Approvals' }}</span>
                        @if($pendingAttendanceCount > 0)
                            <span x-show="sidebarOpen" x-transition class="ml-auto mr-1 w-2 h-2 rounded-full bg-red-500"></span>
                        @endif
                    </div>
                    <svg x-show="sidebarOpen" class="w-4 h-4 text-slate-500 transition-transform duration-200 ml-auto flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                     
                    {{-- Tooltip for collapsed state --}}
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 bg-slate-900 text-white text-xs px-2 py-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 whitespace-nowrap pointer-events-none shadow-lg border border-slate-700 font-medium">Approvals</div>
                </div>
                
                {{-- Submenu - Only show when sidebar is open AND menu is expanded --}}
                <div x-show="open && sidebarOpen" x-transition class="pl-11 space-y-1">
                    @if($role === 'admin')
                        <a href="{{ route('admin.attendance.approvals') }}" class="flex items-center justify-between px-3 py-1.5 text-sm rounded-md hover:text-white hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.attendance.approvals') ? 'text-white bg-slate-800' : 'text-slate-400' }}">
                            <span class="truncate">{{ 'Attendance' }}</span>
                            @if($pendingAttendanceCount > 0)
                                <span class="ml-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $pendingAttendanceCount }}</span>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('supervisor.attendance.approvals') }}" class="flex items-center justify-between px-3 py-1.5 text-sm rounded-md hover:text-white hover:bg-slate-800 transition-colors {{ request()->routeIs('supervisor.attendance.approvals') ? 'text-white bg-slate-800' : 'text-slate-400' }}">
                            <span class="truncate">{{ 'Attendance' }}</span>
                            @if($pendingAttendanceCount > 0)
                                <span class="ml-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $pendingAttendanceCount }}</span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('leaves.approvals') }}" class="block px-3 py-1.5 text-sm text-slate-400 rounded-md hover:text-white hover:bg-slate-800 transition-colors truncate">{{ 'Leave' }}</a>
                </div>
            </div>
            @endif

            {{-- Settings --}}
            {{-- Settings --}}
            <div x-data="{ 
                open: localStorage.getItem('sidebar_settings_open') === 'true', 
                init() { this.$watch('open', val => localStorage.setItem('sidebar_settings_open', val)) } 
            }" class="space-y-1">
                <div @click="sidebarOpen ? (open = !open) : (sidebarOpen = true)" 
                     class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-slate-300 rounded-md hover:bg-slate-800 hover:text-white group transition-colors duration-200 cursor-pointer relative"
                     :class="!sidebarOpen ? 'justify-center' : ''">
                    <div class="flex items-center flex-1 min-w-0" :class="!sidebarOpen ? 'justify-center' : ''">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span x-show="sidebarOpen" x-transition class="truncate whitespace-nowrap">{{ 'Settings' }}</span>
                    </div>
                    <svg x-show="sidebarOpen" class="w-4 h-4 text-slate-500 transition-transform duration-200 ml-auto flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    
                    {{-- Tooltip for collapsed state --}}
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 bg-slate-900 text-white text-xs px-2 py-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 whitespace-nowrap pointer-events-none shadow-lg border border-slate-700 font-medium">Settings</div>
                </div>
                
                {{-- Submenu --}}
                <div x-show="open && sidebarOpen" x-transition class="pl-11 space-y-1">
                    @if($role === 'admin')
                        <a href="{{ route('users.create') }}" class="block px-3 py-1.5 text-sm rounded-md hover:text-white hover:bg-slate-800 transition-colors truncate {{ request()->routeIs('users.create') ? 'text-blue-400 border-l-2 border-blue-500 -ml-[1px]' : 'text-slate-400' }}">{{ 'Add New User' }}</a>
                    @endif
                    <a href="#" class="block px-3 py-1.5 text-sm text-slate-400 rounded-md hover:text-white hover:bg-slate-800 transition-colors truncate">{{ 'Profile Settings' }}</a>
                </div>
            </div>
        </nav>
    </div>

    {{-- User Profile --}}
    <div class="p-4 border-t border-slate-700/50">
        <div class="flex items-center justify-between" :class="!sidebarOpen ? 'justify-center' : ''">
            <div class="flex items-center min-w-0">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-slate-600 ring-2 ring-slate-700 text-white font-bold text-xs shrink-0">
                    {{ substr(Auth::user()->name ?? 'SM', 0, 2) }}
                </div>
                <div x-show="sidebarOpen" x-transition class="ml-3 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ ucfirst($role ?? 'Role') }}</p>
                </div>
            </div>
            
            {{-- Logout Button --}}
            <a href="{{ route('logout') }}" x-show="sidebarOpen" x-transition class="text-slate-400 hover:text-white p-1.5 rounded-md hover:bg-slate-800 transition-colors ml-2" title="Logout">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
        </div>
    </div>
</aside>
