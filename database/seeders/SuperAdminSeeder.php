<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Role Exists
        $role = Role::firstOrCreate(
            ['id' => 3],
            ['name' => 'super_admin']
        );
        $this->command->info('Role Checked/Created: ' . $role->name);

        // 2. Ensure User Exists
        $email = 'superadmin@unitecture.com';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'full_name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make('Unitecture@2026'),
                'role_id' => 3,
                'joining_date' => now(),
                'status' => 'active',
                'leave_balance' => 0,
            ]);
            $this->command->info('Super Admin User Created');
        } else {
            // Update role if needed
            if ($user->role_id !== 3) {
                $user->role_id = 3;
                $user->save();
                $this->command->info('Super Admin User Role Updated to 3');
            } else {
                $this->command->info('Super Admin User Already Exists with Correct Role');
            }
        }
    }
}
