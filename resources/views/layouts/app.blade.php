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
    
    <style>
        :root {
            --sidebar-bg: #0B1120;
            --sidebar-hover: #1E293B;
            --sidebar-text: #94A3B8;
            --sidebar-text-active: #FFFFFF;
            --sidebar-border: #1E293B;
            --accent-blue: #3B82F6;
        }

        body {
            background-color: #F8FAFC;
        }

        .sidebar {
            background-color: var(--sidebar-bg) !important;
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--sidebar-border);
            z-index: 1000;
        }

        .sidebar-header {
            height: 80px;
            display: flex;
            items-center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-nav {
            flex: 1;
            padding: 24px 16px;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 12px;
            border-radius: 8px;
            color: var(--sidebar-text);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            text-align: left;
        }

        .nav-item:hover {
            color: var(--sidebar-text-active);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .nav-item.active {
            color: var(--sidebar-text-active);
            background-color: var(--sidebar-hover);
        }

        .dropdown-container {
            margin-top: 4px;
        }

        .submenu {
            padding-left: 44px;
            position: relative;
            margin-top: 4px;
        }

        .submenu-item {
            display: block;
            padding: 8px 12px;
            color: var(--sidebar-text);
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
            position: relative;
        }

        .submenu-item:hover {
            color: var(--sidebar-text-active);
        }

        .submenu-item.active {
            color: var(--accent-blue);
        }

        .submenu-item.active::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 16px;
            background-color: var(--accent-blue);
        }

        .nested-submenu {
            padding-left: 20px;
            border-left: 1px solid var(--sidebar-border);
            margin-left: -20px;
            margin-top: 4px;
        }

        .rotate-icon {
            transition: transform 0.2s ease;
        }

        .rotated {
            transform: rotate(180deg);
        }

        /* Hide scrollbar */
        .sidebar-nav::-webkit-scrollbar {
            display: none;
        }
        .sidebar-nav {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Profile Section */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background-color: rgba(0, 0, 0, 0.2);
        }

        .profile-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background-color: #334155;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .profile-info {
            flex: 1;
            min-width: 0;
        }

        .profile-name {
            color: white;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-role {
            color: #64748B;
            font-size: 12px;
            margin: 0;
            text-transform: capitalize;
        }

        .logout-btn {
            color: #64748B;
            transition: color 0.2s;
            cursor: pointer;
            background: none;
            border: none;
            padding: 4px;
        }

        .logout-btn:hover {
            color: #EF4444;
        }

        /* Main Content Shift */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 32px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>

    <script>
    function toggleDropdown(id, event) {
        if (event) event.preventDefault();
        const element = document.getElementById(id);
        const icon = event.currentTarget.querySelector('.rotate-icon');
        
        if (element.classList.contains('hidden')) {
            element.classList.remove('hidden');
            if (icon) icon.classList.add('rotated');
        } else {
            element.classList.add('hidden');
            if (icon) icon.classList.remove('rotated');
        }
    }
    </script>
</head>
<body class="font-sans antialiased text-slate-800">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <!-- Header -->
            <div class="sidebar-header">
                <h1 class="text-white text-xl font-bold tracking-tight">MenuBar</h1>
                <button class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <a href="/dashboard" class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>

                <!-- Attendance & Leave -->
                <div class="dropdown-container">
                    <button class="nav-item" onclick="toggleDropdown('leave-dropdown', event)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span style="flex: 1;">Attendance & Leave</span>
                        <svg class="w-3 h-3 rotate-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="leave-dropdown" class="submenu hidden">
                        <a href="{{ route('leaves.index') }}" class="submenu-item {{ Request::routeIs('leaves.index') ? 'active' : '' }}">My Leaves</a>
                        
                        <!-- Attendance Inner -->
                        <div class="dropdown-inner">
                            <button class="submenu-item" style="width: 100%; text-align: left; display: flex; align-items: center; justify-content: space-between; padding-right: 0;" onclick="toggleDropdown('attendance-inner-dropdown', event)">
                                <span>Attendance</span>
                                <svg class="w-2.5 h-2.5 rotate-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="attendance-inner-dropdown" class="nested-submenu hidden">
                                <a href="#" class="submenu-item">My Attendance</a>
                                @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                                <a href="#" class="submenu-item">Team Attendance</a>
                                @endif
                                <a href="#" class="submenu-item">Manual Request</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approvals (Supervisor/Admin Only) -->
                @if(Auth::user()->isSupervisor() || Auth::user()->isAdmin())
                <div class="dropdown-container">
                    <button class="nav-item" onclick="toggleDropdown('approvals-dropdown', event)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span style="flex: 1;">Approvals</span>
                        <svg class="w-3 h-3 rotate-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="approvals-dropdown" class="submenu hidden">
                        <a href="#" class="submenu-item">Attendance</a>
                        <a href="{{ route('leaves.approvals') }}" class="submenu-item {{ Request::routeIs('leaves.approvals') ? 'active' : '' }}">Leave</a>
                    </div>
                </div>
                @endif

                <!-- Settings -->
                <div class="dropdown-container">
                    <button class="nav-item" onclick="toggleDropdown('settings-dropdown', event)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span style="flex: 1;">Settings</span>
                        <svg class="w-3 h-3 rotate-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="settings-dropdown" class="submenu hidden">
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('users.create') }}" class="submenu-item {{ Request::routeIs('users.create') ? 'active' : '' }}">Add New User</a>
                        @endif
                        <a href="#" class="submenu-item">Profile Settings</a>
                    </div>
                </div>
            </nav>

            <!-- Profile Section -->
            <div class="sidebar-footer">
                <div class="profile-container">
                    <div class="avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="profile-info">
                        <p class="profile-name">{{ Auth::user()->name }}</p>
                        <p class="profile-role">{{ Auth::user()->role->name }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content flex-1">
            <div class="max-w-7xl mx-auto">
                @if(session('success'))
                <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
                    {{ session('success') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
