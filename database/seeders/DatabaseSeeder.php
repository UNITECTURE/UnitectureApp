<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        // IDs: 0 => Employee, 1 => Supervisor, 2 => Admin, 3 => Super Admin
        $roles = [
            ['id' => 0, 'name' => 'employee'],
            ['id' => 1, 'name' => 'supervisor'],
            ['id' => 2, 'name' => 'admin'],
            ['id' => 3, 'name' => 'super_admin'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['name' => $role['name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Define Users
        $users = [
            [
                'full_name' => 'Super Admin User',
                'email' => 'superadmin@gmail.com',
                'role_id' => 3, // Super Admin
                'joining_date' => '2023-01-01',
                'reporting_to' => null,
            ],
            [
                'full_name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'role_id' => 2, // Admin
                'joining_date' => '2024-01-01',
                'reporting_to' => null, // Reports to Super Admin (set later)
            ],
            [
                'full_name' => 'Supervisor User',
                'email' => 'supervisor@gmail.com',
                'role_id' => 1, // Supervisor
                'joining_date' => '2024-01-15',
                'reporting_to' => null, // Reports to Admin (set later)
            ],
            [
                'full_name' => 'Employee User',
                'email' => 'employee@gmail.com',
                'role_id' => 0, // Employee
                'joining_date' => '2024-02-01',
                'reporting_to' => null, // Reports to Supervisor (set later)
            ],
        ];

        foreach ($users as $userData) {
            // Calculate Leave Balance Logic (mock)
            $joiningDate = \Carbon\Carbon::parse($userData['joining_date']);
            $monthsSinceJoining = $joiningDate->diffInMonths(now());
            // Logic: 1.25 per month after 3 months probation
            $accrualMonths = max(0, $monthsSinceJoining - 3);
            $initialBalance = $accrualMonths * 1.25;

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'full_name' => $userData['full_name'],
                    'password' => Hash::make('password'),
                    'role_id' => $userData['role_id'],
                    'joining_date' => $userData['joining_date'],
                    'status' => 'active',
                    'leave_balance' => $initialBalance,
                ]
            );
        }

        // Second pass for reporting relationships
        $superAdmin = User::where('email', 'superadmin@gmail.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $supervisor = User::where('email', 'supervisor@gmail.com')->first();
        $employee = User::where('email', 'employee@gmail.com')->first();

        // Chain: Employee -> Supervisor -> Admin -> Super Admin
        if ($admin && $superAdmin) {
            $admin->reporting_to = $superAdmin->id;
            $admin->save();
        }

        if ($supervisor && $admin) {
            $supervisor->reporting_to = $admin->id;
            $supervisor->save();
        }

        if ($employee && $supervisor) {
            $employee->reporting_to = $supervisor->id;
            $employee->save();
        }
    }
}
