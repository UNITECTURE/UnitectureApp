<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\ManualAttendanceRequest;
use App\Models\Holiday;
use Carbon\Carbon;

class ProcessAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process {date? : The date to process (YYYY-MM-DD or "yesterday"/"today"). Defaults to today.}';

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

        if ($dateInput === 'yesterday') {
            $date = Carbon::yesterday();
        } elseif ($dateInput === 'today') {
            $date = Carbon::today();
        } else {
            $date = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
        }

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
        foreach ($nonBioUsers as $user) {
            $this->processUser($user, $date);
        }

        $this->info("Attendance processing complete.");
    }

    private function processUser($user, $date)
    {
        // 0. Check for 'Exempted' status override
        // If an admin has manually exempted this day, do not overwrite it.
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $date->toDateString())
            ->first();

        if ($existing && $existing->status === 'exempted') {
            $this->info("Skipping User {$user->id} for {$date->toDateString()} (Exempted)");
            return;
        }

        // 1. Fetch Biometric Logs (if linked)
        $clockIn = null;
        $clockOut = null;
        $biometricDurationMinutes = 0;

        if ($user->biometric_id) {
            // Get logs sorted by time
            // Shift Logic: 5 AM to 5 AM next day
            $shiftStart = $date->copy()->setTime(5, 0, 0);
            $shiftEnd = $date->copy()->addDay()->setTime(5, 0, 0);

            // Get logs within the shift window
            $logs = AttendanceLog::where('biometric_id', $user->biometric_id)
                ->whereBetween('punch_time', [$shiftStart, $shiftEnd])
                ->orderBy('punch_time', 'asc')
                ->get();

            $rawCount = $logs->count(); // Capture Raw Count

            if ($rawCount > 0) {
                // 1. Debounce / Deduplicate Logs
                // Filter logs that are too close to each other (e.g. within 60 seconds)
                $filteredLogs = collect([]);
                $lastLogTime = null;

                foreach ($logs as $log) {
                    if (!$lastLogTime) {
                        $filteredLogs->push($log);
                        $lastLogTime = Carbon::parse($log->punch_time);
                    } else {
                        $currentLogTime = Carbon::parse($log->punch_time);
                        // Ensure absolute difference to handle any sorting/timezone quirks
                        $diff = abs($currentLogTime->diffInSeconds($lastLogTime));

                        if ($diff > 60) {
                            $filteredLogs->push($log);
                            $lastLogTime = $currentLogTime;
                        }
                    }
                }

                $logs = $filteredLogs; // Use filtered list

                // Calculate using Min/Max to avoid negative values if list is unsorted
                // Get earliest punch time
                $minTime = $logs->min(function ($log) {
                    return strtotime($log->punch_time);
                });
                $clockIn = Carbon::createFromTimestamp($minTime);

                // Get latest punch time 
                $maxTime = $logs->max(function ($log) {
                    return strtotime($log->punch_time);
                });
                $clockOut = Carbon::createFromTimestamp($maxTime);

                // If only one punch, clockOut might be same as clockIn, duration 0
                if ($logs->count() <= 1) {
                    $clockOut = null;
                    // Or keep it as null/start=end? original logic:
                    // $clockIn = first, $clockOut = last (if count > 1).
                    // If count == 1, clockOut is null.
                }

                // Resolved Logic: Use Min/Max to ensure correct order
                if ($minTime === $maxTime || $logs->count() <= 1) {
                    $clockOut = null;
                    $biometricDurationMinutes = 0;
                } else {
                    $biometricDurationMinutes = abs($clockIn->diffInMinutes($clockOut));
                }


                // Ensure ClockIn is formatted as string for DB if needed, or Carbon object is fine for Eloquent
                // But let's keep consistency with original which assigned $logs->first()->punch_time (string/datetime)
                // We'll wrap in typical Carbon format or leave as Carbon instance (Eloquent handles it)

            }
        }

        // 2. Check Approved Manual Attendance
        // Fetch ALL requests for this user/date first to verify content
        $rawReqs = ManualAttendanceRequest::where('user_id', $user->id)
            ->where('date', $date->toDateString())
            ->get();

        $manualDurationMinutes = 0;
        $approvedReqs = collect([]);

        foreach ($rawReqs as $req) {
            $st = strtolower(trim($req->status));
            if ($st === 'approved') {
                $parsed = $this->parseDuration($req->duration);
                $manualDurationMinutes += $parsed;
                $approvedReqs->push($req);
            }
        }

        // Use first approved request for type determination, or just first request?
        // Logic uses $manualReq to set type='manual'.
        $manualReq = $approvedReqs->first();

        // 3. Calculate Totals
        // Sum biometric and manual minutes
        $totalMinutes = (int) $biometricDurationMinutes + (int) $manualDurationMinutes;

        // Determine Type
        if ($biometricDurationMinutes > 0 && $manualDurationMinutes > 0) {
            $type = 'hybrid';
        } elseif ($manualReq) {
            $type = 'manual';
        } else {
            $type = 'biometric';
        }

        // Determine Status
        // 3.1 Check for Sunday/Holiday Exemption if no work done
        $isHoliday = Holiday::where('date', $date->toDateString())->exists();
        $isSunday = $date->isSunday();

        if (($isSunday || $isHoliday) && $totalMinutes == 0) {
            // If strictly no approved work, do NOT mark absent.
            // If an "absent" record exists (e.g. from previous run), delete it
            // so the system defaults to "Sunday/Holiday".
            Attendance::where('user_id', $user->id)
                ->where('date', $date->toDateString())
                ->where('status', 'absent')
                ->delete();

            $this->info("Skipped/Cleaned up for User {$user->id} on {$date->toDateString()} (Sunday/Holiday)");
            return;
        }

        // Determine Status
        // Determine Status
        $isToday = $date->isToday();

        if ($isToday) {
            // For TODAY (Live Monitoring): Mark as present if they have clocked in at all.
            // We cannot enforce 9 hours yet for the 10 AM report.
            $status = ($totalMinutes > 0 || $manualReq) ? 'present' : 'absent';
        } else {
            // For PAST dates (Finalizing): Enforce strict 9-Hour Rule
            // Users are absent if they do not complete 9 hours (540 minutes)
            $status = ($totalMinutes >= 525) ? 'present' : 'absent';
        }

        // Format Total Duration
        $totalMinutes = abs($totalMinutes); // Ensure positive value
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

        $this->info("Processed User {$user->id}: Bio={$biometricDurationMinutes}m, Manual={$manualDurationMinutes}m, Total={$durationString}");

    }

    private function parseDuration($durationStr)
    {
        if (empty($durationStr))
            return 0;

        $durationStr = strtolower($durationStr);
        $totalMinutes = 0;

        // Try "X Hrs Y Mins" or "X Hours Y Minutes"
        if (str_contains($durationStr, 'hrs') || str_contains($durationStr, 'mins') || str_contains($durationStr, 'hour')) {
            preg_match('/(\d+)\s*(?:hr|hour)/i', $durationStr, $hMatch);
            preg_match('/(\d+)\s*(?:min)/i', $durationStr, $mMatch);

            $h = isset($hMatch[1]) ? (int) $hMatch[1] : 0;
            $m = isset($mMatch[1]) ? (int) $mMatch[1] : 0;
            $totalMinutes = ($h * 60) + $m;
        }
        // Try "2h 15m" or "2h" or "15m"
        else {
            preg_match('/(\d+)\s*h/i', $durationStr, $hMatch);
            preg_match('/(\d+)\s*m/i', $durationStr, $mMatch);

            $h = isset($hMatch[1]) ? (int) $hMatch[1] : 0;
            // Ensure 'm' isn't part of 'message' or 'month' if strict format used, but for "2h 15m" usually safe.
            // We check strictly for number followed by optional space then 'm' (end of string or space next)
            preg_match('/(\d+)\s*m(?![a-z])/i', $durationStr, $m2Match);

            // Fallback for just 'm' if previous regex failed
            $m = isset($mMatch[1]) ? (int) $mMatch[1] : 0;
            if ($m == 0 && isset($m2Match[1]))
                $m = (int) $m2Match[1];

            $totalMinutes = ($h * 60) + $m;
        }

        return $totalMinutes;
    }
}
