<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role_id' => 2,
                'joining_date' => '2024-01-01',
                'reporting_to' => null,
            ],
            [
                'name' => 'Sarah Supervisor',
                'email' => 'supervisor@example.com',
                'role_id' => 1,
                'joining_date' => '2024-01-15',
                'reporting_to' => 1, // Will update after creation
            ],
            [
                'name' => 'John Employee',
                'email' => 'employee@example.com',
                'role_id' => 0,
                'joining_date' => '2024-02-01',
                'reporting_to' => 2, // Will update after creation
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $joiningDate = \Carbon\Carbon::parse($userData['joining_date']);
            $today = \Carbon\Carbon::now();
            
            // Count complete calendar months (only months where the 1st has passed)
            $completedMonths = 0;
            $currentDate = $joiningDate->copy();
            
            while ($currentDate->addMonth() <= $today) {
                $completedMonths++;
            }
            
            // After 3 months probation, accrue 1.25 days per month
            $accrualMonths = max(0, $completedMonths - 3);
            $initialBalance = $accrualMonths * 1.25;

            $user = User::create([
                'full_name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'role_id' => $userData['role_id'],
                'joining_date' => $userData['joining_date'],
                'status' => 'active',
                'leave_balance' => $initialBalance,
                'reporting_to' => $userData['reporting_to'],
            ]);
            $createdUsers[$user->id] = $user;
        }
    }
}
