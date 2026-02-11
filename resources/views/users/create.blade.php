@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-slate-50 overflow-hidden" x-data="{ sidebarOpen: true }">
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
                            <h2 class="text-xl font-bold text-slate-800">Add New User</h2>
                            <p class="text-sm text-slate-500 mt-1">Create a new employee account and assign roles.</p>
                        </div>

                        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data"
                            class="p-8 space-y-6">
                            @csrf

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
                                    <label for="profile_image" class="text-sm font-semibold text-slate-700">Profile Image
                                        <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <input type="file" name="profile_image" id="profile_image" required accept="image/*"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('profile_image') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="text-xs text-slate-500">
                                        Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB.
                                    </p>
                                    @error('profile_image')
                                        <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="space-y-2">
                                    <label for="name" class="text-sm font-semibold text-slate-700">Full Name</label>
                                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="John Doe">
                                    @error('name')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <label for="email" class="text-sm font-semibold text-slate-700">Email Address</label>
                                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="john@unitecture.com">
                                    @error('email')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="space-y-2">
                                    <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                                    <input type="password" name="password" id="password" required
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        oninput="checkPasswordStrength(this.value)">

                                    <!-- Strength Bars -->
                                    <div class="flex gap-1 mt-2 h-1">
                                        <div class="h-full w-full rounded-full bg-slate-200 transition-colors duration-300"
                                            id="bar-1"></div>
                                        <div class="h-full w-full rounded-full bg-slate-200 transition-colors duration-300"
                                            id="bar-2"></div>
                                        <div class="h-full w-full rounded-full bg-slate-200 transition-colors duration-300"
                                            id="bar-3"></div>
                                        <div class="h-full w-full rounded-full bg-slate-200 transition-colors duration-300"
                                            id="bar-4"></div>
                                    </div>

                                    <!-- Validation Text -->
                                    <p class="text-xs text-red-500 mt-2 transition-colors duration-300" id="password-hint">
                                        Min. 8 characters, 1 lowercase, 1 uppercase and 1 number. ONLY the following special
                                        characters are allowed: !@#$%^
                                    </p>
                                    @error('password')
                                        <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm
                                        Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600">
                                </div>

                                <script>
                                    function checkPasswordStrength(password) {
                                        const bars = [
                                            document.getElementById('bar-1'),
                                            document.getElementById('bar-2'),
                                            document.getElementById('bar-3'),
                                            document.getElementById('bar-4')
                                        ];
                                        const hint = document.getElementById('password-hint');

                                        let strength = 0;

                                        // Core requirements
                                        const validChars = /^[a-zA-Z0-9!@#$%^]*$/.test(password);
                                        const hasUpper = /[A-Z]/.test(password);
                                        const hasLower = /[a-z]/.test(password);
                                        const hasNumber = /[0-9]/.test(password);
                                        const hasLength = password.length >= 8;

                                        if (hasLength) strength++;
                                        if (hasLower && hasUpper) strength++;
                                        if (hasNumber) strength++;
                                        if (password.length >= 12) strength++;

                                        // Reset bars
                                        bars.forEach(bar => bar.className = 'h-full w-full rounded-full bg-slate-200 transition-colors duration-300');

                                        // Hint Color Logic
                                        if (validChars && hasUpper && hasLower && hasNumber && hasLength) {
                                            hint.classList.remove('text-red-500');
                                            hint.classList.add('text-green-600');
                                        } else {
                                            hint.classList.add('text-red-500');
                                            hint.classList.remove('text-green-600');
                                        }

                                        // Fill bars
                                        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
                                        for (let i = 0; i < strength; i++) {
                                            if (i < 4) {
                                                bars[i].classList.remove('bg-slate-200');
                                                bars[i].classList.add(colors[Math.min(strength - 1, 3)]);
                                            }
                                        }
                                    }
                                </script>

                                <!-- Role -->
                                <div class="space-y-2">
                                    <label for="role_id" class="text-sm font-semibold text-slate-700">Role</label>
                                    <select name="role_id" id="role_id" required
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('role_id') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Primary Supervisor (required for employees) -->
                                <div class="space-y-2">
                                    <label for="reporting_to" class="text-sm font-semibold text-slate-700">Primary Supervisor</label>
                                    <select name="reporting_to" id="reporting_to"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('reporting_to') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        <option value="">No primary supervisor</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('reporting_to') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}
                                                ({{ ucfirst($manager->role->name) }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-500">Required for employees. Optional for supervisors/admins.</p>
                                    @error('reporting_to')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Secondary Supervisor (optional, for employees only) -->
                                <div class="space-y-2" id="secondary_supervisor_wrap">
                                    <label for="secondary_supervisor_id" class="text-sm font-semibold text-slate-700">Secondary Supervisor</label>
                                    <select name="secondary_supervisor_id" id="secondary_supervisor_id"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('secondary_supervisor_id') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        <option value="">None</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('secondary_supervisor_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}
                                                ({{ ucfirst($manager->role->name) }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-500">Optional. Both supervisors can assign tasks to this employee.</p>
                                    @error('secondary_supervisor_id')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Joining Date -->
                                <div class="space-y-2">
                                    <label for="joining_date" class="text-sm font-semibold text-slate-700">Joining
                                        Date</label>
                                    <input type="date" name="joining_date" id="joining_date" required value="{{ old('joining_date') }}"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('joining_date') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600">
                                    @error('joining_date')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <label for="status" class="text-sm font-semibold text-slate-700">Status</label>
                                    <select name="status" id="status" required
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('status') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Biometric ID -->
                                <div class="space-y-2">
                                    <label for="biometric_id" class="text-sm font-semibold text-slate-700">Biometric ID</label>
                                    <input type="text" name="biometric_id" id="biometric_id" value="{{ old('biometric_id') }}"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('biometric_id') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="e.g. 101 or BIO-001">
                                    @error('biometric_id')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Telegram Chat ID -->
                                <div class="space-y-2">
                                    <label for="telegram_chat_id" class="text-sm font-semibold text-slate-700">Telegram Chat
                                        ID</label>
                                    <input type="text" name="telegram_chat_id" id="telegram_chat_id" value="{{ old('telegram_chat_id') }}"
                                        class="w-full px-4 py-2.5 rounded-lg border {{ $errors->has('telegram_chat_id') ? 'border-red-500' : 'border-slate-200' }} focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                                        placeholder="e.g. 123456789">
                                    <p class="text-xs text-slate-500">Optional: Get this from @userinfobot on Telegram.</p>
                                    @error('telegram_chat_id')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="pt-6 flex items-center justify-end gap-4 border-t border-slate-100">
                                <a href="{{ route('dashboard') }}"
                                    class="px-6 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
                                <button type="submit"
                                    class="px-8 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                                    Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection