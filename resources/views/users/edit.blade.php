@extends('layouts.app')

@section('content')
    <style>
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
    <div class="flex h-screen bg-slate-50 overflow-hidden" >
        <x-sidebar :role="Auth::user()->isAdmin() ? 'admin' : (Auth::user()->isSupervisor() ? 'supervisor' : 'employee')" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                <div class="max-w-4xl mx-auto">
                    {{-- Back Button --}}
                    <button onclick="history.back()"
                        class="mb-4 flex items-center text-slate-500 hover:text-slate-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back
                    </button>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-xl font-bold text-slate-800">Edit User</h2>
                            <p class="text-sm text-slate-500 mt-1">Update employee details and role settings.</p>
                        </div>

                        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data"
                            class="p-8 space-y-6">
                            @csrf
                            @method('PUT')

                            @if($errors->any())
                                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200"
                                    role="alert">
                                    <ul class="list-disc pl-5">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Profile Image -->
                                <div class="space-y-2 md:col-span-2">
                                    <label for="profile_image" class="text-sm font-semibold text-slate-700">Profile Image</label>
                                    <div class="flex items-center gap-4">
                                        @php
                                            $avatarUrl = $user->profile_image && filter_var($user->profile_image, FILTER_VALIDATE_URL) ? $user->profile_image : ($user->profile_image ? asset('storage/' . $user->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=94a3b8&color=fff&size=64');
                                        @endphp
                                        <img src="{{ $avatarUrl }}" alt="" class="w-12 h-12 rounded-full object-cover">
                                        <input type="file" name="profile_image" id="profile_image" accept="image/*"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    </div>
                                    <p class="text-xs text-slate-500">Leave empty to keep current image.</p>
                                </div>

                                <!-- Name -->
                                <div class="space-y-2">
                                    <label for="name" class="text-sm font-semibold text-slate-700">Full Name</label>
                                    <input type="text" name="name" id="name" required
                                        value="{{ old('name', $user->full_name) }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="John Doe">
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <label for="email" class="text-sm font-semibold text-slate-700">Email Address</label>
                                    <input type="email" name="email" id="email" required
                                        value="{{ old('email', $user->email) }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="john@unitecture.com">
                                </div>

                                <!-- Password -->
                                <div class="space-y-2">
                                    <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="password"
                                            class="w-full px-4 py-2.5 pr-12 rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                            placeholder="Enter new password">
                                        <button type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                            aria-label="Toggle password visibility"
                                            onclick="togglePasswordVisibility('password', 'edit-password-eye-show', 'edit-password-eye-hide')">
                                            <svg id="edit-password-eye-show" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                <circle cx="12" cy="12" r="3" stroke-width="2" />
                                            </svg>
                                            <svg id="edit-password-eye-hide" class="w-5 h-5" hidden viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 5.09A9.96 9.96 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.03 10.03 0 01-4.132 5.366" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.1 6.1A10.03 10.03 0 002.458 12C3.732 16.057 7.523 19 12 19c1.03 0 2.03-.156 2.97-.447" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500">Leave empty to keep current password. Minimum 8 characters.</p>
                                    @error('password')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm Password</label>
                                    <div class="relative">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="w-full px-4 py-2.5 pr-12 rounded-lg border {{ $errors->has('password_confirmation') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                            placeholder="Confirm new password">
                                        <button type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                            aria-label="Toggle confirm password visibility"
                                            onclick="togglePasswordVisibility('password_confirmation', 'edit-password-confirm-eye-show', 'edit-password-confirm-eye-hide')">
                                            <svg id="edit-password-confirm-eye-show" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                <circle cx="12" cy="12" r="3" stroke-width="2" />
                                            </svg>
                                            <svg id="edit-password-confirm-eye-hide" class="w-5 h-5" hidden viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 5.09A9.96 9.96 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.03 10.03 0 01-4.132 5.366" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.1 6.1A10.03 10.03 0 002.458 12C3.732 16.057 7.523 19 12 19c1.03 0 2.03-.156 2.97-.447" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Role -->
                                <div class="space-y-2">
                                    <label for="role_id" class="text-sm font-semibold text-slate-700">Role</label>
                                    <select name="role_id" id="role_id" required
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Primary Supervisor (required for employees) -->
                                <div class="space-y-2">
                                    <label for="reporting_to" class="text-sm font-semibold text-slate-700">Primary Supervisor</label>
                                    <select name="reporting_to" id="reporting_to"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        <option value="">No primary supervisor</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" @selected(old('reporting_to', $user->reporting_to) == $manager->id)>{{ $manager->name }}
                                                ({{ ucfirst($manager->role->name) }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-500">Required for employees. Optional for supervisors/admins.</p>
                                </div>

                                <!-- Secondary Supervisor (optional, for employees only) -->
                                <div class="space-y-2" id="secondary_supervisor_wrap">
                                    <label for="secondary_supervisor_id" class="text-sm font-semibold text-slate-700">Secondary Supervisor</label>
                                    <select name="secondary_supervisor_id" id="secondary_supervisor_id"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        <option value="">None</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" @selected(old('secondary_supervisor_id', $user->secondary_supervisor_id) == $manager->id)>{{ $manager->name }}
                                                ({{ ucfirst($manager->role->name) }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-500">Optional. Both supervisors can assign tasks to this employee.</p>
                                </div>

                                <!-- Joining Date -->
                                <div class="space-y-2">
                                    <label for="joining_date" class="text-sm font-semibold text-slate-700">Joining Date</label>
                                    <input type="date" name="joining_date" id="joining_date" required
                                        value="{{ old('joining_date', optional($user->joining_date)->format('Y-m-d')) }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600">
                                </div>

                                <!-- Biometric ID -->
                                <div class="space-y-2">
                                    <label for="biometric_id" class="text-sm font-semibold text-slate-700">Biometric ID</label>
                                    <input type="text" name="biometric_id" id="biometric_id"
                                        value="{{ old('biometric_id', $user->biometric_id) }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="e.g. 101 or BIO-001">
                                </div>

                                <!-- Telegram Chat ID -->
                                <div class="space-y-2">
                                    <label for="telegram_chat_id" class="text-sm font-semibold text-slate-700">Telegram Chat ID</label>
                                    <input type="text" name="telegram_chat_id" id="telegram_chat_id"
                                        value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="e.g. 123456789">
                                    <p class="text-xs text-slate-500">Optional: Get this from @userinfobot on Telegram.</p>
                                </div>
                            </div>

                            <script>
                                function togglePasswordVisibility(inputId, showId, hideId) {
                                    const input = document.getElementById(inputId);
                                    const showIcon = document.getElementById(showId);
                                    const hideIcon = document.getElementById(hideId);

                                    if (!input || !showIcon || !hideIcon) return;

                                    const isPassword = input.type === 'password';
                                    input.type = isPassword ? 'text' : 'password';
                                    showIcon.hidden = isPassword;
                                    hideIcon.hidden = !isPassword;
                                }
                            </script>

                            <div class="pt-6 flex items-center justify-end gap-4 border-t border-slate-100">
                                <a href="{{ route('users.manage') }}"
                                    class="px-6 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
                                <button type="submit"
                                    class="px-8 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                                    Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
