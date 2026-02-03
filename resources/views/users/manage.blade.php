@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-gray-50 overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FB]">
                <div class="container mx-auto px-6 py-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Manage Users</h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">View and manage all employees</p>
                        </div>
                        <a href="{{ route('settings.index') }}"
                            class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Settings
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="bg-white rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            Employee
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            Joining Date
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($users as $user)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    @if($user->profile_image)
                                                        <img src="{{ $user->profile_image }}" alt="{{ $user->full_name }}" 
                                                            class="w-10 h-10 rounded-full object-cover mr-3">
                                                    @else
                                                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                                            <span class="text-indigo-600 font-bold text-sm">
                                                                {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-semibold text-slate-800">{{ $user->full_name }}</div>
                                                        @if($user->biometric_id)
                                                            <div class="text-xs text-slate-400">ID: {{ $user->biometric_id }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                    @if($user->role_id == 3 || $user->role_id == 2) bg-purple-100 text-purple-700
                                                    @elseif($user->role_id == 1) bg-blue-100 text-blue-700
                                                    @else bg-gray-100 text-gray-700
                                                    @endif">
                                                    {{ $user->role->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                    @if($user->status === 'active') bg-green-100 text-green-700
                                                    @else bg-red-100 text-red-700
                                                    @endif">
                                                    {{ ucfirst($user->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                {{ $user->joining_date ? $user->joining_date->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($user->id !== Auth::id())
                                                    <button 
                                                        onclick="confirmDelete('{{ $user->id }}', '{{ $user->full_name }}')"
                                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                @else
                                                    <span class="text-xs text-slate-400 italic">Current User</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                                No users found
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

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">Confirm Delete</h3>
            </div>

            <div class="p-6">
                <p class="text-slate-600 mb-6">
                    Are you sure you want to delete <strong id="userName"></strong>? This action cannot be undone.
                </p>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div style="display: flex; gap: 12px;">
                        <button type="button" onclick="closeDeleteModal()" 
                            style="flex: 1; padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #334155; font-weight: 500; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="submit" 
                            style="flex: 1; padding: 10px 16px; border-radius: 8px; border: none; background: #dc2626; color: white; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }
        .animate-scaleIn {
            animation: scaleIn 0.3s ease-out;
        }
    </style>

    <script>
        function confirmDelete(userId, userName) {
            document.getElementById('userName').textContent = userName;
            document.getElementById('deleteForm').action = `/users/${userId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
@endsection
