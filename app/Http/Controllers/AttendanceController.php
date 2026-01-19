<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            2, 3 => 'admin',
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
        Log::info('Attendance Approvals Page Accessed', [
            'user_id' => $user->id,
            'role' => $role,
            'request_count' => $requests->count(),
            'filters' => $request->all()
        ]);

        return view('attendance.approvals', compact('role', 'requests', 'summary', 'status'));
    }
    
    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        $role = match((int)$user->role_id) {
            2, 3 => 'admin',
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
        
        // Fallback: Check for Approved Leave (if no attendance or absent)
        $isLeaveToday = false;
        if (!$att || $status === 'absent') {
             $leaveToday = \App\Models\Leave::where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->exists();
             if ($leaveToday) {
                 $status = 'leave';
                 $isLeaveToday = true;
             }
        }

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
            $todayLimit = $end; // For absent calc
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth(); // Full month for Holiday/Sunday/Total Working Calculation
            $todayLimit = Carbon::now(); // For absent calc
        }

        // Fetch Attendance Records
        $monthAtts = Attendance::where('user_id', $user->id)
                        ->whereBetween('date', [$start, $end])
                        ->get();

        // Fetch Approved Leaves in Range
        $monthLeaves = \App\Models\Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->get();

        // 1. Calculate Holidays in range (Full Month)
        $holidays = \App\Models\Holiday::whereBetween('date', [$start, $end])->pluck('date')->toArray();
        $holidayCount = count($holidays);

        // 2. Calculate Sundays in range (Full Month)
        $sundays = 0;
        $tempDate = $start->copy();
        while ($tempDate->lte($end)) {
            // Check if Sunday and NOT already a holiday
            if ($tempDate->isSunday() && !in_array($tempDate->toDateString(), $holidays)) {
                $sundays++;
            }
            $tempDate->addDay();
        }

        // 3. Count Present and Leaves
        $presentCount = $monthAtts->where('status', 'present')->count();
        // Base leave count from attendance
        $leaveCount = $monthAtts->where('status', 'leave')->count();
        
        // Add implicit leaves from Leave table (those without attendance records)
        // We iterate through all days in month, check if covered by leave, and NOT in atts (or atts is absent)
        $tempScan = $start->copy();
        $dateLimitForScan = $todayLimit->gt($end) ? $end : $todayLimit; // Only scan up to "today" or end of month? Usually leaves are planned ahead, but "Used" should probably reflect valid passed days or all?
        // User wants "Approved" leaves to show.
        
        $datesWithPresence = $monthAtts->where('status', 'present')->pluck('date')->map(fn($d) => substr($d, 0, 10))->toArray();
        $datesWithLeaveAtt = $monthAtts->where('status', 'leave')->pluck('date')->map(fn($d) => substr($d, 0, 10))->toArray();

        foreach ($monthLeaves as $leaveReq) {
            $lStart = Carbon::parse($leaveReq->start_date);
            $lEnd = Carbon::parse($leaveReq->end_date);
            
            // Interaction with Filter Window
             $overlapStart = $lStart->lt($start) ? $start : $lStart;
             $overlapEnd = $lEnd->gt($end) ? $end : $lEnd;
             
             if ($overlapStart->lte($overlapEnd)) {
                 $curr = $overlapStart->copy();
                 while ($curr->lte($overlapEnd)) {
                     $dStr = $curr->toDateString();
                     // If NO Attendance Record matching "present" or "leave" exists, count it.
                     if (!in_array($dStr, $datesWithPresence) && !in_array($dStr, $datesWithLeaveAtt)) {
                         $leaveCount++;
                         $datesWithLeaveAtt[] = $dStr; // Avoid double counting if multiple requests overlap (rare)
                     }
                     $curr->addDay();
                 }
             }
        }

        // 4. Calculate "Total Working Days" (Full Month Total Days - Sundays - Holidays)
        $daysInMonth = (int) $start->diffInDays($end) + 1;
        $totalWorkingDays = (int) ($daysInMonth - $holidayCount - $sundays);
        $totalHolidaysAndSundays = (int) ($holidayCount + $sundays);

        // 5. Calculate Absent (Only for ELAPSED days)
        $elapsedDays = 0;
        $potentialWorkingDaysElapsed = 0;
        
        $tempElapsed = $start->copy();
        // Limit loop to min(endOfMonth, today)
        $limitDate = $todayLimit->gt($end) ? $end : $todayLimit;

        while ($tempElapsed->lte($limitDate)) {
             $isHoliday = in_array($tempElapsed->toDateString(), $holidays);
             $isSunday = $tempElapsed->isSunday();
             
             if (!$isHoliday && !$isSunday) {
                 $potentialWorkingDaysElapsed++;
             }
             $tempElapsed->addDay();
        }
        
        $absentCount = max(0, $potentialWorkingDaysElapsed - $presentCount - $leaveCount);

        $cumulative_summary = [
            'total_working' => $totalWorkingDays,     // Card 1 (Full Month Potential)
            'my_working' => $presentCount,            // Card 2
            'leaves' => $leaveCount,                  // Card 3
            'holidays' => $totalHolidaysAndSundays    // Card 4 (Full Month)
        ];

        // Recalculate duration for display in table if needed
        $totalMinutes = 0;
        foreach ($monthAtts as $att) {
            if ($att->status === 'present') {
                 $parseDuration = function($str) {
                    if (!$str) return 0;
                    preg_match('/(\d+)\s*[hH]/i', $str, $hMatch);
                    preg_match('/(\d+)\s*[mM]/i', $str, $mMatch);
                    return (isset($hMatch[1]) ? (int)$hMatch[1] * 60 : 0) + (isset($mMatch[1]) ? (int)$mMatch[1] : 0);
                };
                $totalMinutes += $parseDuration($att->duration);
            }
        }
        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;
        $workingDuration = "{$h} Hrs {$m} Mins";

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
            2, 3 => 'admin',
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
            2, 3 => 'admin',
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
        
        Log::info('Attendance List Index Accessed', [
            'user_id' => $authUser->id,
            'role' => $role,
            'fetched_users_count' => $users->count(),
            'date_filter' => $date
        ]);

        // --- Daily Report Logic ---
        $daily_summary = ['total' => $users->count(), 'present' => 0, 'leave' => 0, 'absent' => 0];
        $daily_records = [];

        // Pre-fetch leaves for the specific date to avoid multiple queries
        $leavesOnDate = \App\Models\Leave::where('status', 'approved')
                         ->whereDate('start_date', '<=', $date)
                         ->whereDate('end_date', '>=', $date)
                         ->get()
                         ->groupBy('user_id');

        foreach ($users as $user) {
            $att = Attendance::where('user_id', $user->id)
                        ->where('date', $date)
                        ->first();
            
            $status = $att ? $att->status : 'absent'; // Default absent if no record
            
            // Fallback: Check for Approved Leave
            if (($status === 'absent' || !$att) && $leavesOnDate->has($user->id)) {
                $status = 'leave';
            }

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

        // --- Cumulative Report Logic ---
        $monthFilter = $request->input('month', 'this_month');
        
        if ($monthFilter === 'last_month') {
            $startOfMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfMonth = Carbon::now()->subMonth()->endOfMonth();
        } else {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
        }
        // Calculate Holidays in range (Full Month)
        $holidays = \App\Models\Holiday::whereBetween('date', [$startOfMonth, $endOfMonth])->pluck('date')->toArray();
        $holidayCount = count($holidays);

        // Calculate Sundays in range (Full Month)
        $sundays = 0;
        $tempDate = $startOfMonth->copy();
        while ($tempDate->lte($endOfMonth)) {
            // Check if Sunday and NOT already a holiday
            if ($tempDate->isSunday() && !in_array($tempDate->toDateString(), $holidays)) {
                $sundays++;
            }
            $tempDate->addDay();
        }

        // Calculate "Total Working Days" (Full Month Total Days - Sundays - Holidays)
        $daysInMonth = (int) $startOfMonth->diffInDays($endOfMonth) + 1;
        $totalWorkingDays = (int) ($daysInMonth - $holidayCount - $sundays);
        $totalHolidaysAndSundays = (int) ($holidayCount + $sundays);

        $cumulative_summary = [
            'total_days' => $daysInMonth,
            'working' => $totalWorkingDays, 
            'holidays' => $totalHolidaysAndSundays 
        ];

        $cumulative_records = [];

        foreach ($users as $user) {
            $monthAtts = Attendance::where('user_id', $user->id)
                            ->whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->get();
            
            // Present count
            $presentCount = $monthAtts->where('status', 'present')->count();
            
            // Base Leave count
            $leaveCount = $monthAtts->where('status', 'leave')->count();

            // Fetch Approved Leaves in Range (Per User)
            $monthLeaves = \App\Models\Leave::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $endOfMonth)
                ->where('end_date', '>=', $startOfMonth)
                ->get();
            
            // Calculate Leave Count (Merge)
            $datesWithPresence = $monthAtts->where('status', 'present')->pluck('date')->map(fn($d) => substr($d, 0, 10))->toArray();
            $datesWithLeaveAtt = $monthAtts->where('status', 'leave')->pluck('date')->map(fn($d) => substr($d, 0, 10))->toArray();

            foreach ($monthLeaves as $leaveReq) {
                $lStart = Carbon::parse($leaveReq->start_date);
                $lEnd = Carbon::parse($leaveReq->end_date);
                
                 $overlapStart = $lStart->lt($startOfMonth) ? $startOfMonth : $lStart;
                 $overlapEnd = $lEnd->gt($endOfMonth) ? $endOfMonth : $lEnd;
                 
                 if ($overlapStart->lte($overlapEnd)) {
                     $curr = $overlapStart->copy();
                     while ($curr->lte($overlapEnd)) {
                         $dStr = $curr->toDateString();
                         if (!in_array($dStr, $datesWithPresence) && !in_array($dStr, $datesWithLeaveAtt)) {
                             $leaveCount++;
                             $datesWithLeaveAtt[] = $dStr;
                         }
                         $curr->addDay();
                     }
                 }
            }
            
            // Absent calculation (Consistent with My Attendance)
            // Calculate potential working days passed SO FAR to determine absent
            $potentialWorkingDaysElapsed = 0;
            $limitDate = ($monthFilter === 'last_month') ? $endOfMonth : (Carbon::now()->gt($endOfMonth) ? $endOfMonth : Carbon::now());

            $tempElapsed = $startOfMonth->copy();
            while ($tempElapsed->lte($limitDate)) {
                 $isHoliday = in_array($tempElapsed->toDateString(), $holidays);
                 $isSunday = $tempElapsed->isSunday();
                 
                 if (!$isHoliday && !$isSunday) {
                     $potentialWorkingDaysElapsed++;
                 }
                 $tempElapsed->addDay();
            }

            // Absent is Potential Working Days Elapsed - (Present + Leaves)
            // Existing logic had explicit and implicit checks, simplified here to:
            $totalAbsent = max(0, $potentialWorkingDaysElapsed - $presentCount - $leaveCount);

            // Calculate Total Duration
            $totalMinutes = 0;
            foreach ($monthAtts as $att) {
                if ($att->status === 'present' && $att->duration) {
                    preg_match('/(\d+)\s*[hH]/i', $att->duration, $hMatch);
                    preg_match('/(\d+)\s*[mM]/i', $att->duration, $mMatch);
                    $minutes = (isset($hMatch[1]) ? (int)$hMatch[1] * 60 : 0) + (isset($mMatch[1]) ? (int)$mMatch[1] : 0);
                    $totalMinutes += $minutes;
                }
            }
            
            $totalH = floor($totalMinutes / 60);
            $totalM = $totalMinutes % 60;
            $displayDuration = "{$totalH} Hrs {$totalM} Mins";

            $cumulative_records[] = [
                'name' => $user->name,
                'present' => $presentCount,
                'leave' => $leaveCount,
                'absent' => $totalAbsent,
                'duration' => $displayDuration 
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
                    2, 3 => 'admin',
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
                
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now();
                $monthDays = $start->daysInMonth;
                $elapsed = $end->day;
                
                 $role = match((int)$user->role_id) {
                    2, 3 => 'admin',
                    1 => 'supervisor',
                    default => 'employee',
                };
                if ($role === 'supervisor') {
                    $users = $user->subordinates()->get(); 
                } else {
                    $users = User::all();
                }

                foreach ($users as $u) {
                    $monthAtts = Attendance::where('user_id', $u->id)
                                    ->whereBetween('date', [$start, Carbon::now()->endOfMonth()])
                                    ->get();
                    
                    $present = $monthAtts->where('status', 'present')->count();
                    $leave = $monthAtts->where('status', 'leave')->count();
                    $absent = max(0, $elapsed - $present - $leave);
                    
                    fputcsv($file, [$u->full_name, $present, $leave, $absent, $monthDays]);
                }

            } elseif ($type === 'self_daily') {
                // Self Daily Export
                fputcsv($file, ['Date', 'Status', 'In Time', 'Out Time', 'Duration']);
                
                $date = $request->input('date', Carbon::today()->toDateString());
                
                $att = Attendance::where('user_id', $user->id)->where('date', $date)->first();
                
                $status = $att ? ucfirst($att->status) : 'Absent';
                if ($att && $att->type === 'manual') $status .= ' (Manual)';
                
                $in = $att && $att->clock_in ? Carbon::parse($att->clock_in)->format('h:i A') : '-';
                $out = $att && $att->clock_out ? Carbon::parse($att->clock_out)->format('h:i A') : '-';
                $dur = $att ? $att->duration : '-';
                
                fputcsv($file, [$date, $status, $in, $out, $dur]);

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
