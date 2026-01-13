<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unitecture</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#0B1120] text-slate-300 flex flex-col fixed inset-y-0 z-50 transition-transform duration-300 md:translate-x-0 -translate-x-full" id="sidebar">
            <div class="h-16 flex items-center px-6 bg-[#0B1120]">
                <h1 class="text-white text-lg font-bold tracking-wide">MenuBar</h1>
             </div>

            <nav class="flex-1 px-3 space-y-1 overflow-y-auto pt-4">
                <!-- Dashboard -->
                <a href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 bg-[#1E293B] text-white rounded-md group transition-colors shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>

                <!-- Tasks -->
                <div class="space-y-1">
                    <button class="w-full flex items-center justify-between px-3 py-2.5 text-slate-400 hover:text-white rounded-md transition-colors group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="text-sm font-medium">Tasks</span>
                        </div>
                        <svg class="w-3 h-3 text-slate-500 transition-transform group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                    </button>
                    <!-- Submenu items -->
                    <div class="pl-11 space-y-1 block">
                         <a href="#" class="block py-2 text-xs text-slate-400 hover:text-white flex items-center gap-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                             All Tasks
                         </a>
                         <a href="#" class="block py-2 text-xs text-slate-400 hover:text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                             Assigned to Me
                         </a>
                    </div>
                </div>

                <!-- Leaves -->
                <button class="w-full flex items-center justify-between px-3 py-2.5 text-slate-400 hover:text-white rounded-md transition-colors group">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-sm font-medium">Leaves</span>
                    </div>
                    <svg class="w-3 h-3 text-slate-500 transition-transform group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                </button>
                    <!-- Submenu items -->
                     <div class="pl-11 space-y-1 block">
                        <a href="#" class="block py-2 text-xs text-slate-400 hover:text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            My Leaves
                        </a>
                   </div>


                 <!-- Attendance -->
                 <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-400 hover:text-white rounded-md transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">Attendance</span>
                </a>

                 <!-- Leave Approvals -->
                 <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-400 hover:text-white rounded-md transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">Leave Approvals</span>
                </a>

                <!-- My Team -->
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-400 hover:text-white rounded-md transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="text-sm font-medium">My Team</span>
                </a>

                <!-- Settings -->
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-400 hover:text-white rounded-md transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm font-medium">Settings</span>
                </a>
            </nav>

            <div class="p-4 bg-[#0B1120]">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-white text-sm font-bold border border-slate-600">
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Sarah Mitchell' }}</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest">{{ Auth::user()->role ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 bg-slate-100 min-h-screen font-roboto">
            <div class="p-8 space-y-8 max-w-7xl mx-auto">
                 <!-- Role Switcher Bar -->
                 <div class="bg-white border border-slate-200 rounded-sm p-4 shadow-sm flex items-center gap-4">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Demo Role Switcher:</span>
                    <div class="relative inline-block text-left w-64">
                         <select class="block w-full text-sm text-slate-600 bg-slate-50 border-0 py-1.5 focus:ring-0 rounded-md">
                            <option>Admin</option>
                            <option>Employee</option>
                        </select>
                    </div>
                 </div>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
