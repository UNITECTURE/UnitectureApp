<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure User 3 exists (implied by UserSeeder)
        $employee = User::find(3);
        if (!$employee) {
            $this->command->info("User 3 not found. Run UserSeeder first.");
            return;
        }

        // 1. Leaves
        $this->command->info("Creating Leaves for User 3...");
        Leave::create([
            'user_id' => 3,
            'leave_type' => 'paid',
            'reason' => 'Sick Leave Test',
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(2),
            'days' => 2,
            'status' => 'pending',
        ]);

        Leave::create([
            'user_id' => 3,
            'leave_type' => 'unpaid',
            'reason' => 'Past Leave Test',
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->subDays(9),
            'days' => 2,
            'status' => 'approved',
        ]);

        // Update Balance
        $employee->update(['leave_balance' => 12.5]);

        // 2. Attendance
        $this->command->info("Creating Attendance for User 3...");
        
        // Today: Present
        Attendance::updateOrCreate(
            ['user_id' => 3, 'date' => Carbon::today()],
            [
                'status' => 'present',
                'clock_in' => Carbon::now()->setHour(9)->setMinute(0),
                'clock_out' => null,
                'type' => 'biometric'
            ]
        );

        // Yesterday: Absent
        Attendance::updateOrCreate(
            ['user_id' => 3, 'date' => Carbon::today()->subDays(1)],
            [
                'status' => 'absent',
                'clock_in' => null,
                'clock_out' => null,
                'type' => 'biometric'
            ]
        );
        
        $this->command->info("Test Data Seeding Completed.");
    }
}
