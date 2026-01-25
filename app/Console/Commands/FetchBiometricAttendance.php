<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Services\ZkLibrary;

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
    protected $description = 'Fetch attendance logs from ZKTeco Device and sync to Cloud Server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Configuration
        $ip = env('BIOMETRIC_DEVICE_IP', '192.168.1.201');
        $port = env('BIOMETRIC_DEVICE_PORT', 4370);
        $remoteUrl = env('BIOMETRIC_SYNC_URL'); // e.g. https://your-app.com/api/essl/attendance
        
        $this->info("--- Biometric Attendance Sync ---");
        
        if (!$remoteUrl) {
            $this->error("Error: BIOMETRIC_SYNC_URL is not set in .env");
            $this->info("Please set this to your deployed app URL (e.g., https://example.com/api/essl/attendance).");
            return 1;
        }

        $this->info("Target Server: $remoteUrl");
        $this->info("Connecting to Device at $ip:$port...");

        $zk = new ZkLibrary($ip, $port);
        
        if ($zk->connect()) { 
            $this->info("Connected to device successfully.");
            
            $this->info("Fetching attendance logs...");
            $logs = $zk->getAttendance(); 
            
            $count = count($logs);
            $this->info("Fetched $count records locally.");

            if ($count > 0) {
                // Map logs to API format
                // Assuming ZKLib returns ['uid' => ..., 'timestamp' => ...] or similar
                // We map it to expectation: user_id, timestamp
                $payloadLogs = [];
                foreach ($logs as $log) {
                    $payloadLogs[] = [
                        'user_id' => $log['uid'] ?? $log[0] ?? null,
                        'timestamp' => $log['timestamp'] ?? $log[3] ?? null, // Adjust based on Lib return
                        'device_id' => $ip
                    ];
                }

                $this->info("Pushing data to Cloud Server...");
                
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(30)->post($remoteUrl, [
                        'logs' => $payloadLogs,
                        'source' => 'local_bridge'
                    ]);

                    if ($response->successful()) {
                        $this->info("Success! Server Response: " . $response->body());
                        // Optional: Clear logs from device if successful?
                        // $zk->clearAttendance(); 
                    } else {
                        $this->error("Failed to push to server. Status: " . $response->status());
                        $this->error("Response: " . $response->body());
                    }
                } catch (\Exception $e) {
                    $this->error("Connection Error: " . $e->getMessage());
                }

            } else {
                $this->warn("No new attendance records found on device.");
            }

            $zk->disconnect();
            
        } else {
            $this->error("Failed to connect to Biometric Device at $ip");
            $this->warn("Make sure this computer is on the same network as the device.");
        }
        
        return 0;
    }
}
