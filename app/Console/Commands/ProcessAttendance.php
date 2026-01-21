<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\ManualAttendanceRequest;
use Carbon\Carbon;

class ProcessAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process {date? : The date to process (YYYY-MM-DD). Defaults to today.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process raw biometric logs into daily attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateInput = $this->argument('date');
        $date = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
        
        $this->info("Processing attendance for: " . $date->toDateString());

        // Process all users who have a biometric ID mapped
        $users = User::whereNotNull('biometric_id')->get();

        foreach ($users as $user) {
            $this->processUser($user, $date);
        }

        // Also process users WITHOUT biometric ID if they have Manual Requests? 
        // User didn't specify, but safer to process ALL users to ensure Manual Requests are applied even if no biometric ID.
        // But the prompt emphasizes "Match biometric_id with users". 
        // If I limit to `whereNotNull`, users without biometric ID (remote?) rely purely on Manual?
        // Logic below handles manual. I'll include users with Manual Requests even if no biometric ID?
        // Actually, simpler to just process ALL active users.
        
        $nonBioUsers = User::whereNull('biometric_id')->get();
        foreach($nonBioUsers as $user) {
             $this->processUser($user, $date);
        }
        
        $this->info("Attendance processing complete.");
    }

    private function processUser($user, $date)
    {
        // 1. Fetch Biometric Logs (if linked)
        $clockIn = null;
        $clockOut = null;
        $biometricDurationMinutes = 0;

        if ($user->biometric_id) {
            // Get logs sorted by time
            $logs = AttendanceLog::where('biometric_id', $user->biometric_id)
                        ->whereDate('punch_time', $date)
                        ->orderBy('punch_time', 'asc')
                        ->get();
            
            if ($logs->count() > 0) {
                // First Punch is Global IN
                $clockIn = $logs->first()->punch_time;
                
                // Last Punch is Global OUT (if multiple)
                if ($logs->count() > 1) {
                    $clockOut = $logs->last()->punch_time;
                }
                
                // Pair Calculation (In -> Out, In -> Out)
                $count = $logs->count();
                // Iterating in steps of 2: [0,1], [2,3], [4,5]
                for ($i = 0; $i < $count - 1; $i += 2) {
                     $inTime = Carbon::parse($logs[$i]->punch_time);
                     $outTime = Carbon::parse($logs[$i+1]->punch_time);
                     
                     // Calculate difference in minutes (Absolute)
                     $diff = $outTime->diffInMinutes($inTime);
                     $biometricDurationMinutes += $diff;
                }
            }
        }

        // 2. Check Approved Manual Attendance
        $manualReq = ManualAttendanceRequest::where('user_id', $user->id)
                        ->where('date', $date->toDateString())
                        ->where('status', 'approved')
                        ->first();
        
        $manualDurationMinutes = 0;
        if ($manualReq) {
            $manualDurationMinutes = $this->parseDuration($manualReq->duration);
        }

        // 3. Calculate Totals
        // Ensure total is positive integer
        $totalMinutes = abs((int)$biometricDurationMinutes + (int)$manualDurationMinutes);
        
        // Determine Type (Manual vs Biometric) for Color Coding
        // If Manual Request exists, prioritize 'manual' type so it shows in Purple/Distinct color
        $type = $manualReq ? 'manual' : 'biometric';
        
        // Determine Status
        // If total duration > 0 OR manual approved -> Present
        // Otherwise Absent
        $status = ($totalMinutes > 0 || $manualReq) ? 'present' : 'absent';
        
        // Format Total Duration
        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;
        $durationString = "{$hours} Hrs {$mins} Mins";

        // 4. Update or Create Attendance Record
        Attendance::updateOrCreate(
            [
                'user_id' => $user->id, 
                'date' => $date->toDateString()
            ],
            [
                'clock_in' => $clockIn,   // First punch
                'clock_out' => $clockOut, // Last punch (or null)
                'duration' => $durationString,
                'status' => $status,
                'type' => $type
            ]
        );
    }
    
    private function parseDuration($durationStr)
    {
        // Handles formats like "8 Hrs 30 Mins" produced by UI
        preg_match('/(\d+)\s*Hrs/i', $durationStr, $hMatch);
        preg_match('/(\d+)\s*Mins/i', $durationStr, $mMatch);
        
        $h = isset($hMatch[1]) ? (int)$hMatch[1] : 0;
        $m = isset($mMatch[1]) ? (int)$mMatch[1] : 0;
        
        return ($h * 60) + $m;
    }
}
