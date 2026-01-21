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
    protected $description = 'Fetch attendance logs from ZKTeco Biometric Device';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = env('BIOMETRIC_DEVICE_IP', '192.168.1.201');
        $port = env('BIOMETRIC_DEVICE_PORT', 4370);

        $this->info("Attempting to connect to ZKTeco Device at $ip:$port...");

        $zk = new ZkLibrary($ip, $port);
        
        if ($zk->connect()) { 
            $this->info("Connected successfully!");
            
            $this->info("Fetching attendance logs...");
            $logs = $zk->getAttendance(); // This will return empty array in my simplified lib for now
            
            $this->info("Fetched " . count($logs) . " records.");

            if (count($logs) === 0) {
                 $this->warn("No records returned. This might be due to empty device log OR basic library limitations.");
            }

            $zk->disconnect();
            
            // Process Logs (Assuming $logs is array of [uid, state, timestamp])
            // Since the library is partial, this loop won't run effectively unless we have real data.
            // ...
            
        } else {
            $this->error("Failed to connect to device.");
            $this->warn("Note: This custom library is a lightweight UDP implementation.");
            $this->warn("For production reliability, please ensure 'rats/zkteco-modules' is installed via Composer.");
            $this->warn("Your 'laravel/framework' version requirement in composer.json might be conflicting.");
        }
        
        return 0;
    }
}
