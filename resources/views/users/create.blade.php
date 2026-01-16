@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Add New User</h2>
            <p class="text-sm text-slate-500 mt-1">Create a new employee account and assign roles.</p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            @if($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="space-y-2">
                    <label for="name" class="text-sm font-semibold text-slate-700">Full Name</label>
                    <input type="text" name="name" id="name" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                        placeholder="John Doe">
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="text-sm font-semibold text-slate-700">Email Address</label>
                    <input type="email" name="email" id="email" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                        placeholder="john@unitecture.com">
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600">
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600">
                </div>

                <!-- Role -->
                <div class="space-y-2">
                    <label for="role_id" class="text-sm font-semibold text-slate-700">Role</label>
                    <select name="role_id" id="role_id" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reporting To -->
                <div class="space-y-2">
                    <label for="reporting_to" class="text-sm font-semibold text-slate-700">Reporting To (Manager)</label>
                    <select name="reporting_to" id="reporting_to"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                        <option value="">No Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ ucfirst($manager->role->name) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Joining Date -->
                <div class="space-y-2">
                    <label for="joining_date" class="text-sm font-semibold text-slate-700">Joining Date</label>
                    <input type="date" name="joining_date" id="joining_date" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600">
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label for="status" class="text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" id="status" required
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600 bg-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Telegram Chat ID -->
                <div class="space-y-2">
                    <label for="telegram_chat_id" class="text-sm font-semibold text-slate-700">Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" id="telegram_chat_id"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none text-slate-600"
                        placeholder="e.g. 123456789">
                    <p class="text-xs text-slate-500">Optional: Get this from @userinfobot on Telegram.</p>
                </div>
            </div>

            <div class="pt-6 flex items-center justify-end gap-4 border-t border-slate-100">
                <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
                <button type="submit" class="px-8 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
