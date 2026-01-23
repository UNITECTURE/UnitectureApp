<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BiometricPushController extends Controller
{
    /**
     * Handle incoming push data from eSSL/ZKTeco Device
     */
    public function handlePush(Request $request)
    {
        // 1. Log the incoming request for debugging
        Log::info('Biometric Push Received:', $request->all());

        // 2. Handle Custom Python Bridge (JSON)
        if ($request->isJson()) {
            return $this->processJsonPayload($request);
        }

        // 3. Handle Device Initialization / Handshake
        // The device often sends 'cdata' or just 'SN' to check connection
        if ($request->has('SN') && !$request->has('table')) {
            // Just a handshake or config check
            return response("OK", 200); 
        }

        // 4. Handle Attendance Logs (Legacy HTTP Push)
        // Check for 'table=ATTLOG' which indicates attendance data
        if ($request->input('table') === 'ATTLOG') {
            return $this->processLegacyAttendanceLog($request);
        }
        
        return response("OK", 200);
    }

    private function processJsonPayload(Request $request)
    {
        try {
            $data = $request->json()->all();
            
            if (isset($data['logs']) && is_array($data['logs'])) {
                $count = 0;
                foreach ($data['logs'] as $log) {
                    if (isset($log['user_id']) && isset($log['timestamp'])) {
                        $this->saveLog($log['user_id'], $log['timestamp']);
                        $count++;
                    }
                }
                return response()->json(['status' => 'success', 'count' => $count], 200);
            }
            
            return response()->json(['status' => 'error', 'message' => 'Invalid format'], 400);

        } catch (\Exception $e) {
            Log::error("Biometric Bridge Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function processLegacyAttendanceLog(Request $request)
    {
        try {
            // Handling POST Body Content (Raw Lines)
            $content = $request->getContent();
            
            // If content is empty, check query params (Get request mode)
            if (empty($content)) {
                $biometricId = $request->input('PIN');
                $timestamp = $request->input('Stamp');
                
                if ($biometricId && $timestamp) {
                    $this->saveLog($biometricId, $timestamp);
                }
            } else {
                // Parse lines (POST Body)
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (trim($line) === '') continue;
                    
                    $parts = preg_split('/\s+/', trim($line));
                    
                    if (count($parts) >= 2) {
                        $biometricId = $parts[0];
                        $timestamp = $parts[1] . ' ' . ($parts[2] ?? '00:00:00'); // Date + Time
                        
                        $this->saveLog($biometricId, $timestamp);
                    }
                }
            }
            
            return response("OK", 200);
            
        } catch (\Exception $e) {
            Log::error("Biometric Push Error: " . $e->getMessage());
            return response("ERROR", 500);
        }
    }
    
    private function saveLog($biometricId, $timestamp)
    {
        // Normalize Date
        try {
            $dt = Carbon::parse($timestamp);
        } catch (\Exception $e) {
            return; 
        }

        // Prevent Duplicates
        $exists = AttendanceLog::where('biometric_id', $biometricId)
                    ->where('punch_time', $dt->toDateTimeString())
                    ->exists();
                    
        if (!$exists) {
            AttendanceLog::create([
                'biometric_id' => $biometricId,
                'punch_time' => $dt,
                'device_id' => 'PUSH_API' // Marker to know source
            ]);
            
            Log::info("Saved Punch: User $biometricId at $timestamp");
        }
    }
}
