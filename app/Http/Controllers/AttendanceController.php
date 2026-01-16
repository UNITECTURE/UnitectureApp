<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

use App\Models\ManualAttendanceRequest;

class AttendanceController extends Controller
{
    // ... index method ...

    public function approvals(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        // Determine role string for view (0=emp, 1=sup, 2=admin)
        $role = match((int)$user->role_id) {
            2 => 'admin',
            1 => 'supervisor',
            default => 'employee', // Should not access usually
        };

        $baseQuery = ManualAttendanceRequest::query();

        // Scope for Supervisor
        if ($role === 'supervisor') {
            $subordinateIds = $user->subordinates()->pluck('id');
            $baseQuery->whereIn('user_id', $subordinateIds);
        }
        // Admin sees all (no extra where)

        // Summary Stats (Scoped)
        $summary = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // Apply Status Filter
        $status = strtolower($request->input('status', 'all'));
        if ($status !== 'all') {
            $baseQuery->where('status', $status);
        }

        // Apply Date Filter
        if ($request->filled('date')) {
            $baseQuery->where('date', $request->input('date'));
        }

        // Apply Search (User Name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $baseQuery->whereHas('user', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $requests = $baseQuery->with('user')->orderBy('created_at', 'desc')->get();

        return view('attendance.approvals', compact('role', 'requests', 'summary', 'status'));
    }
    
    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        $role = match((int)$user->role_id) {
            2 => 'admin',
            1 => 'supervisor',
            default => 'employee',
        };

        // --- Daily Logic ---
        $dateInput = $request->input('date');
        $today = Carbon::today();
        
        // Default Date: Today for everyone (Restriction removed)
        if (!$dateInput) {
            $date = $today;
        } else {
            $date = Carbon::parse($dateInput);
        }

        $daily_summary = ['total' => 1, 'present' => 0, 'leave' => 0, 'absent' => 0];
        $daily_records = [];

        // Fetch Attendance
        $att = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();
        $status = $att ? $att->status : 'absent'; 
        
        // Update Summary
        if ($status === 'present') $daily_summary['present'] = 1;
        elseif ($status === 'leave') $daily_summary['leave'] = 1;
        else $daily_summary['absent'] = 1;

        $isManual = $att && $att->type === 'manual';
        
        $login = $att && $att->clock_in ? Carbon::parse($att->clock_in)->format('h:i A') : '-';
        $logout = $att && $att->clock_out ? Carbon::parse($att->clock_out)->format('h:i A') : '-';
        
            $class = match($status) {
                'present' => $isManual ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800',
                'absent' => 'bg-red-100 text-red-800',
                'leave' => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-800',
            };

        $daily_records[] = [
            'name' => $user->full_name, // Using accessor
            'status' => ucfirst($status) . ($isManual ? ' (Manual)' : ''),
            'login_time' => $login,
            'logout_time' => $logout,
            'duration' => $att ? $att->duration : '-',
            'class' => $class
        ];

        // --- Cumulative Logic ---
        $filter = $request->input('filter', 'this_month');
        if ($filter === 'last_month') {
            $start = Carbon::now()->subMonth()->startOfMonth();
            $end = Carbon::now()->subMonth()->endOfMonth();
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now(); // Today
        }

        $monthAtts = Attendance::where('user_id', $user->id)
                        ->whereBetween('date', [$start, $end])
                        ->get();

        $presentCount = $monthAtts->where('status', 'present')->count();
        $leaveCount = $monthAtts->where('status', 'leave')->count();

        // Helper to parse duration
        $parseDuration = function($str) {
            if (!$str) return 0;
            preg_match('/(\d+)\s*[hH]/i', $str, $hMatch);
            preg_match('/(\d+)\s*[mM]/i', $str, $mMatch);
            return (isset($hMatch[1]) ? (int)$hMatch[1] * 60 : 0) + (isset($mMatch[1]) ? (int)$mMatch[1] : 0);
        };

        $totalMinutes = 0;
        foreach ($monthAtts as $att) {
            if ($att->status === 'present') {
                $totalMinutes += $parseDuration($att->duration);
            }
        }
        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;
        $workingDuration = "{$h} Hrs {$m} Mins";

        // Calculate Total Days in Month (e.g. 31) for Display
        $monthDays = $start->daysInMonth;
        
        // Calculate Elapsed Days for Absent Logic (Don't count future as absent)
        $elapsedDays = $end->diffInDays($start) + 1;
        
        $absentCount = max(0, $elapsedDays - $presentCount - $leaveCount);

        $cumulative_summary = [
            'total_days' => $monthDays,
            'working' => $presentCount,
            'holidays' => 0 
        ];

        $cumulative_records = [[
            'name' => $user->full_name,
            'present' => $presentCount,
            'leave' => $leaveCount,
            'absent' => $absentCount,
            'working_duration' => $workingDuration
        ]];

        $myRequests = $user->manualRequests()->latest()->get();

        return view('attendance.self', compact(
            'role', 
            'user', 
            'myRequests', 
            'daily_summary', 
            'daily_records', 
            'cumulative_summary', 
            'cumulative_records'
        ));
    }

    // New Dedicated Manual Attendance Page
    public function manualAccess()
    {
        $user = Auth::user();
        $myRequests = $user->manualRequests()->orderBy('created_at', 'desc')->get();
        
        $role = match((int)$user->role_id) {
            2 => 'admin',
            1 => 'supervisor',
            default => 'employee',
        };

        return view('attendance.manual', compact('role', 'myRequests'));
    }

    public function index(Request $request) 
    {
        $authUser = Auth::user();
        if (!$authUser) return redirect('/login');

        $role = match((int)$authUser->role_id) {
            2 => 'admin',
            1 => 'supervisor',
            default => 'employee',
        };

        $date = $request->input('date', Carbon::today()->toDateString());
        
        // Fetch users based on role
        if ($role === 'supervisor') {
            $users = $authUser->subordinates()->get(); 
        } else {
            $users = User::all();
        }

        // --- Daily Report Logic ---
        $daily_summary = ['total' => $users->count(), 'present' => 0, 'leave' => 0, 'absent' => 0];
        $daily_records = [];

        foreach ($users as $user) {
            $att = Attendance::where('user_id', $user->id)
                        ->where('date', $date)
                        ->first();
            
            $status = $att ? $att->status : 'absent'; // Default absent if no record
            
            // Update Summary
            if ($status === 'present') $daily_summary['present']++;
            elseif ($status === 'leave') $daily_summary['leave']++;
            else $daily_summary['absent']++;

            $isManual = $att && $att->type === 'manual';
            
            $login = $att && $att->clock_in ? Carbon::parse($att->clock_in)->format('h:i A') : ($isManual ? '-' : '-');
            $logout = $att && $att->clock_out ? Carbon::parse($att->clock_out)->format('h:i A') : ($isManual ? '-' : '-');

            $class = match($status) {
                'present' => $isManual ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800',
                'absent' => 'bg-red-100 text-red-800',
                'leave' => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-800',
            };

            $daily_records[] = [
                'name' => $user->name,
                'status' => ucfirst($status) . ($isManual ? ' (Manual)' : ''),
                'login' => $login,
                'logout' => $logout,
                'duration' => $att ? $att->duration : '-',
                'class' => $class
            ];
        }

        // --- Cumulative Report Logic (Current Month) ---
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $daysElapsed = Carbon::now()->day; 
        
        $cumulative_summary = [
            'total_days' => $startOfMonth->daysInMonth,
            'working' => $daysElapsed, // Simplified
            'holidays' => 0
        ];

        $cumulative_records = [];

        foreach ($users as $user) {
            $monthAtts = Attendance::where('user_id', $user->id)
                            ->whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->get();
            
            $presentCount = $monthAtts->where('status', 'present')->count();
            $leaveCount = $monthAtts->where('status', 'leave')->count();
            $explicitAbsent = $monthAtts->where('status', 'absent')->count();
            $missing = $daysElapsed - ($presentCount + $leaveCount + $explicitAbsent);
            $totalAbsent = $explicitAbsent + max(0, $missing);

            $cumulative_records[] = [
                'name' => $user->name,
                'present' => $presentCount,
                'leave' => $leaveCount,
                'absent' => $totalAbsent,
                'duration' => '0 Hours' 
            ];
        }

        return view('attendance.list', compact('role', 'daily_summary', 'daily_records', 'cumulative_summary', 'cumulative_records'));
    }

    public function export(Request $request) 
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        $type = $request->input('type', 'self');

        // Headers for CSV download
        $fileName = "attendance_" . date('Y-m-d_H-i') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($user, $request, $type) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'team') {
                // Team Export (Daily Report)
                fputcsv($file, ['Name', 'Date', 'Status', 'In Time', 'Out Time', 'Duration']);
                
                $date = $request->input('date', \Carbon\Carbon::today()->toDateString());
                
                 $role = match((int)$user->role_id) {
                    2 => 'admin',
                    1 => 'supervisor',
                    default => 'employee',
                };
                
                if ($role === 'supervisor') {
                    $users = $user->subordinates()->get(); 
                } else {
                    $users = \App\Models\User::all();
                }

                foreach ($users as $u) {
                    $att = \App\Models\Attendance::where('user_id', $u->id)->where('date', $date)->first();
                    $status = $att ? ucfirst($att->status) : 'Absent';
                    $in = $att && $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-';
                    $out = $att && $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-';
                    $dur = $att ? $att->duration : '-';
                    
                    if ($att && $att->type === 'manual') $status .= ' (Manual)';
                    
                    fputcsv($file, [$u->full_name, $date, $status, $in, $out, $dur]);
                }
            } elseif ($type === 'team_cumulative') {
                // Team Cumulative Export
                fputcsv($file, ['Name', 'Present Days', 'Leave Days', 'Absent Days', 'Total Month Days']);
                
                $start = \Carbon\Carbon::now()->startOfMonth();
                $end = \Carbon\Carbon::now();
                $monthDays = $start->daysInMonth;
                $elapsed = $end->day;
                
                 $role = match((int)$user->role_id) {
                    2 => 'admin',
                    1 => 'supervisor',
                    default => 'employee',
                };
                if ($role === 'supervisor') {
                    $users = $user->subordinates()->get(); 
                } else {
                    $users = \App\Models\User::all();
                }

                foreach ($users as $u) {
                    $monthAtts = \App\Models\Attendance::where('user_id', $u->id)
                                    ->whereBetween('date', [$start, \Carbon\Carbon::now()->endOfMonth()])
                                    ->get();
                    
                    $present = $monthAtts->where('status', 'present')->count();
                    $leave = $monthAtts->where('status', 'leave')->count();
                    $absent = max(0, $elapsed - $present - $leave);
                    
                    fputcsv($file, [$u->full_name, $present, $leave, $absent, $monthDays]);
                }

            } else {
                // Self Export (Monthly Report)
                // DEBUG: Show type received
                fputcsv($file, ['Date (Type: ' . $type . ')', 'Status', 'In Time', 'Out Time', 'Duration']);
                
                $filter = $request->input('filter', 'this_month'); // Default to this month
                
                if ($filter === 'last_month') {
                    $start = \Carbon\Carbon::now()->subMonth()->startOfMonth();
                    $end = \Carbon\Carbon::now()->subMonth()->endOfMonth();
                } else {
                    $start = \Carbon\Carbon::now()->startOfMonth();
                    $end = \Carbon\Carbon::now();
                }
                
                $atts = \App\Models\Attendance::where('user_id', $user->id)
                            ->whereBetween('date', [$start, $end])
                            ->orderBy('date', 'desc')
                            ->get();
                            
                foreach ($atts as $att) {
                    $status = ucfirst($att->status);
                    if ($att->type === 'manual') $status .= ' (Manual)';
                    
                    $in = $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-';
                    $out = $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-';
                    
                    fputcsv($file, [$att->date, $status, $in, $out, $att->duration]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
