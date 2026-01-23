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
        // IDs: 0 => Employee, 1 => Supervisor, 2 => Admin
        $roles = [
            ['id' => 0, 'name' => 'employee'],
            ['id' => 1, 'name' => 'supervisor'],
            ['id' => 2, 'name' => 'admin'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['name' => $role['name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Define Users
        // Admin, Supervisor, Employee, Employee2
        $users = [
            [
                'full_name' => 'Admin User',
                'email' => 'admin@example.com',
                'role_id' => 2, // Admin
                'joining_date' => '2024-01-01',
                'reporting_to' => null,
            ],
            [
                'full_name' => 'Sarah Supervisor',
                'email' => 'supervisor@example.com',
                'role_id' => 1, // Supervisor
                'joining_date' => '2024-01-15',
                'reporting_to' => 2, // Reports to Admin? Usually internal
            ],
            [
                'full_name' => 'John Employee',
                'email' => 'employee@example.com',
                'role_id' => 0, // Employee
                'joining_date' => '2024-02-01',
                'reporting_to' => 1, // Reports to Sarah
            ],
            [
                'full_name' => 'Jane Employee',
                'email' => 'employee2@example.com',
                'role_id' => 0, // Employee
                'joining_date' => '2024-03-01',
                'reporting_to' => 1, // Reports to Sarah
            ],
        ];

        foreach ($users as $userData) {
            // Calculate Leave Balance Logic (mock)
            $joiningDate = \Carbon\Carbon::parse($userData['joining_date']);
            $monthsSinceJoining = $joiningDate->diffInMonths(now());
            // Logic: 1.25 per month after 3 months probation (example from existing code)
            $accrualMonths = max(0, $monthsSinceJoining - 3);
            $initialBalance = $accrualMonths * 1.25;

            // Use updateOrInsert or just create if not exists
            $user = User::firstOrNew(['email' => $userData['email']]);
            $user->fill([
                'full_name' => $userData['full_name'],
                'password' => Hash::make('password'),
                'role_id' => $userData['role_id'],
                'joining_date' => $userData['joining_date'],
                'status' => 'active',
                'leave_balance' => $initialBalance,
                // We will report_to IDs, but need to ensure they exist. 
                // Since we iterate in order (Admin -> Sup -> Emp), referencing ID by array position isn't perfect if IDs are auto-increment.
                // But let's assume we can resolve reporting_to by email lookup or doing a second pass.
            ]);
            $user->save();
        }

        // Second pass for reporting relationships
        $admin = User::where('email', 'admin@example.com')->first();
        $supervisor = User::where('email', 'supervisor@example.com')->first();
        $employee = User::where('email', 'employee@example.com')->first();
        $employee2 = User::where('email', 'employee2@example.com')->first();

        if ($supervisor && $admin) {
            $supervisor->reporting_to = $admin->id;
            $supervisor->save();
        }
        if ($employee && $supervisor) {
            $employee->reporting_to = $supervisor->id;
            $employee->save();
        }
        if ($employee2 && $supervisor) {
            $employee2->reporting_to = $supervisor->id;
            $employee2->save();
        }
    }
}
