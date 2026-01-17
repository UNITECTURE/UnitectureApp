<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FetchBiometricAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch attendance logs from biometric device and process them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = env('BIOMETRIC_DEVICE_IP', '192.168.1.201');
        $port = env('BIOMETRIC_DEVICE_PORT', 4370);

        $this->info("Connecting to biometric device at {$ip}:{$port}...");

        try {
            // Check if class exists to avoid crash if package not installed
            if (!class_exists(ZKTeco::class)) {
                $this->error("ZKTeco library not found. Please run 'composer require rats/zkteco-modules'.");
                return Command::FAILURE;
            }

            $zk = new ZKTeco($ip, $port);
            
            if (!$zk->connect()) {
                $this->error("Unable to connect to the device.");
                return Command::FAILURE;
            }

            $this->info("Connected. Fetching attendance logs...");
            
            $logs = $zk->getAttendance(); // Returns array of arrays
            
            if (empty($logs)) {
                $this->info("No logs found on the device.");
                $zk->disconnect();
                return Command::SUCCESS;
            }

            $this->info("Fetched " . count($logs) . " logs. saving to database...");

            $affectedKeys = []; // Stores "biometric_id|date" to re-calculate attendance

            // Process Logs
            foreach ($logs as $log) {
                // Log structure usually: ['uid' => 1, 'id' => '1', 'state' => 1, 'timestamp' => '2023-01-01 10:00:00', 'type' => 0]
                // 'id' is often the user ID on device (biometric_id).
                
                $biometricId = $log['id'];
                $timestamp = $log['timestamp'];
                
                // Parse timestamp
                $dt = Carbon::parse($timestamp);
                $date = $dt->format('Y-m-d');

                // Check for duplicate in attendance_logs
                // We use a quick check. Optimized approach would be to fetch all max timestamps per user, but for now row-by-row
                $exists = AttendanceLog::where('biometric_id', $biometricId)
                            ->where('punch_time', $timestamp)
                            ->exists();

                if (!$exists) {
                    AttendanceLog::create([
                        'biometric_id' => $biometricId,
                        'punch_time' => $timestamp,
                        'device_id' => $ip // store IP as device identifier
                    ]);
                    
                    // Mark this user+date for processing
                    $key = $biometricId . '|' . $date;
                    if (!in_array($key, $affectedKeys)) {
                        $affectedKeys[] = $key;
                    }
                }
            }

            $this->info("Raw logs saved. Processing " . count($affectedKeys) . " daily records...");

            // Process Attendance (Clock In / Out)
            foreach ($affectedKeys as $key) {
                [$biometricId, $date] = explode('|', $key);

                // Find User
                $user = User::where('biometric_id', $biometricId)->first();
                
                if (!$user) {
                    // Log warning but continue
                    // $this->warn("No user found with biometric_id {$biometricId}");
                    continue;
                }

                // Get min and max punch for this user on this date
                $punches = AttendanceLog::where('biometric_id', $biometricId)
                            ->whereDate('punch_time', $date)
                            ->orderBy('punch_time')
                            ->pluck('punch_time');

                if ($punches->isEmpty()) continue;

                $clockIn = $punches->first();
                $clockOut = $punches->last();

                // If only one punch, clock_out is null? Or same?
                // Usually if count == 1, clock_out is null.
                if ($punches->count() == 1) {
                    $clockOut = null;
                }

                // Calculate duration
                $duration = null;
                if ($clockIn && $clockOut) {
                    $start = Carbon::parse($clockIn);
                    $end = Carbon::parse($clockOut);
                    $diff = $start->diff($end);
                    $duration = $diff->format('%H:%I'); // 08:30
                }

                // Update Attendance Table
                Attendance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date
                    ],
                    [
                        'clock_in' => $clockIn ? Carbon::parse($clockIn)->format('H:i:s') : null,
                        'clock_out' => $clockOut ? Carbon::parse($clockOut)->format('H:i:s') : null,
                        'status' => 'present',
                        'duration' => $duration
                    ]
                );
            }

            $zk->disconnect();
            $this->info("Attendance synced successfully.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
