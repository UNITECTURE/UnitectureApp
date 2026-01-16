<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $password = Hash::make('password');

        // 1. Admin (ID 1)
        DB::table('user')->insertOrIgnore([
            'id' => 1,
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => $password,
            'role_id' => 2, // Admin
            'reporting_to' => null,
            'status' => 'active',
            'joining_date' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2. Supervisor (ID 2)
        DB::table('user')->insertOrIgnore([
            'id' => 2,
            'full_name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => $password,
            'role_id' => 1, // Supervisor
            'reporting_to' => null, // Reports to Admin? Or Null. Null is fine.
            'status' => 'active',
            'joining_date' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Employee (ID 3)
        DB::table('user')->insertOrIgnore([
            'id' => 3,
            'full_name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => $password,
            'role_id' => 0, // Employee
            'reporting_to' => 2, // Supervisor
            'status' => 'active',
            'joining_date' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
