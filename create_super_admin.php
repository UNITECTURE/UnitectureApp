<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Create Role
$role = Role::firstOrCreate(
    ['id' => 3],
    ['name' => 'super_admin', 'description' => 'Super Admin - Firm Owner']
);

if ($role->wasRecentlyCreated) {
    echo "Created Role: super_admin (ID: 3)\n";
} else {
    echo "Role super_admin already exists.\n";
}

// 2. Create Super Admin User
$email = 'superadmin@unitecture.com';
$password = 'Unitecture@2026';

$user = User::firstOrCreate(
    ['email' => $email],
    [
        'full_name' => 'Super Admin',
        'password' => Hash::make($password),
        'role_id' => 3,
        'joining_date' => now(),
        'status' => 'active',
        'leave_balance' => 0,
    ]
);

// Ensure role is correct if user existed
if ($user->role_id !== 3) {
    $user->role_id = 3;
    $user->save();
}

// Reset password to known value for testing
$user->password = Hash::make($password);
$user->save();

echo "Super Admin User Configured.\n";
echo "Email: $email\n";
echo "Password: $password\n";
