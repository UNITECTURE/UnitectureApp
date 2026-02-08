<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Services\TelegramService;

class LeaveController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display the user's leaves (My Leaves).
     */
    public function index()
    {
        $user = Auth::user();
        $leaves = $user->leaves()->orderBy('created_at', 'desc')->get();
        
        // Calculate Used Leaves (Approved & Paid)
        $usedLeaves = $leaves->where('status', 'approved')->where('leave_type', 'paid')->sum('days');
        
        // Calculate Earned Leaves based on joining date
        $joiningDate = \Carbon\Carbon::parse($user->joining_date);
        $eligibilityDate = $joiningDate->copy()->addMonths(3)->startOfMonth();
        $currentMonth = now()->startOfMonth();
        
        // Only calculate if eligible (3+ months from joining)
        if ($currentMonth >= $eligibilityDate) {
            // Total months from eligibility date to current month
            $monthsEligible = $eligibilityDate->diffInMonths($currentMonth) + 1;
            $earnedLeaves = $monthsEligible * 1.25;
            
            // If earned reaches 25, it resets to 0
            if ($earnedLeaves >= 25) {
                $earnedLeaves = 0;
            }
        } else {
            // Not yet eligible
            $earnedLeaves = 0;
        }
        
        return view('leaves.index', compact('leaves', 'usedLeaves', 'earnedLeaves'));
    }

    /**
     * Store a new leave request.
     */
    /**
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return back()->withErrors(['error' => 'Admins cannot apply for leave.']);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
            'leave_category' => 'required|in:planned,emergency',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $leaveCategory = $request->leave_category;
        $daysUntilLeave = now()->diffInDays($startDate);
        
        // Validate 7-day prior notice for PLANNED leaves only
        if ($leaveCategory === 'planned' && $daysUntilLeave < 7) {
            return back()->withErrors(['error' => "Planned leave must be applied at least 7 days in advance. Days remaining: {$daysUntilLeave}. Consider applying as Emergency leave if urgent."]);
        }
        
        // Emergency leaves can only be applied for same day or next day
        if ($leaveCategory === 'emergency' && $daysUntilLeave > 1) {
            return back()->withErrors(['error' => 'Emergency leave can only be applied for today or tomorrow. Use Planned leave for future dates.']);
        }

        // Prevent duplicate/overlapping leave requests for the same date range
        $user = Auth::user();
        $hasOverlap = Leave::where('user_id', $user->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date', '<=', $endDate)
                      ->whereDate('end_date', '>=', $startDate);
            })
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors(['error' => 'Leave already applied for the selected date(s). Please choose different dates.']);
        }
        
        // Calculate actual leave days (Excluding Sundays and Holidays)
        $holidays = \App\Models\Holiday::whereBetween('date', [$startDate, $endDate])->get()->map(function($holiday) {
            return $holiday->date->format('Y-m-d');
        })->toArray();

        $days = 0;
        $tempDate = $startDate->copy();
        
        while ($tempDate->lte($endDate)) {
            // Check if Sunday OR Holiday
            if (!$tempDate->isSunday() && !in_array($tempDate->format('Y-m-d'), $holidays)) {
                $days++;
            }
            $tempDate->addDay();
        }
        
        // Prevent 0 days leave if user selects only holidays/Sundays (Optional: validate or just allow 0)
        // If days is 0, it means they applied for holidays. We can still record it but it consumes 0 balance.
        
        $leaveType = ($user->leave_balance >= $days) ? 'paid' : 'unpaid';

        $leave = Leave::create([
            'user_id' => $user->id,
            'leave_type' => $leaveType,
            'leave_category' => $leaveCategory,
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'status' => 'pending',
        ]);

        // Determine if urgent
        $isUrgent = $leaveCategory === 'emergency';
        $urgentTag = $isUrgent ? '🚨 <b>URGENT</b> ' : '';

        // Send Telegram Notification to Supervisor
        if ($user->manager && $user->manager->telegram_chat_id) {
            $message = "{$urgentTag}<b>New Leave Request</b>\n\n";
            $message .= "Employee: {$user->name}\n";
            $message .= "<b>Date Applied:</b> " . now()->format('Y-m-d H:i A') . "\n";
            $message .= "<b>Leave Category:</b> " . ucfirst($leaveCategory) . "\n";
            $message .= "<b>Leave Type:</b> " . ucfirst($leaveType) . "\n";
            $message .= "<b>Duration:</b> {$days} day(s)\n";
            $message .= "<b>Dates:</b> {$request->start_date} to {$request->end_date}\n";
            $message .= "<b>Reason:</b> {$request->reason}\n\n";
            
            if ($isUrgent) {
                $message .= "<b>⚠️ ACTION REQUIRED: This is an emergency leave and needs immediate approval!</b>";
            }
            
            $this->telegramService->sendMessage($user->manager->telegram_chat_id, $message);
        }

        // Also notify Admins (Role ID 2)
        $admins = User::where('role_id', 2)->whereNotNull('telegram_chat_id')->get();
        foreach ($admins as $admin) {
            $adminMessage = "{$urgentTag}<b>Alert: Leave Request Received</b>\n\n";
            $adminMessage .= "Employee: {$user->name}\n";
            $adminMessage .= "<b>Date Applied:</b> " . now()->format('Y-m-d H:i A') . "\n";
            $adminMessage .= "<b>Leave Category:</b> " . ucfirst($leaveCategory) . "\n";
            $adminMessage .= "<b>Leave Type:</b> " . ucfirst($leaveType) . "\n";
            $adminMessage .= "<b>Status:</b> " . ($isUrgent ? 'Pending Supervisor (URGENT)' : 'Pending Supervisor Approval') . "\n";
            $adminMessage .= "<b>Dates:</b> {$request->start_date} to {$request->end_date}";
            
            $this->telegramService->sendMessage($admin->telegram_chat_id, $adminMessage);
        }

        $message = "Leave requested successfully as <b>" . ucfirst($leaveCategory) . "</b>. System assigned type: <b>" . ucfirst($leaveType) . "</b>";
        if ($isUrgent) {
            $message .= ". ⚠️ Awaiting immediate supervisor approval.";
        }
        
        return redirect()->route('leaves.index')->with('success', $message);
    }

    public function approvals(Request $request)
    {
        $user = Auth::user();
        if ($user->isEmployee()) {
            abort(403, 'Unauthorized action.');
        }

        $query = Leave::with('user.role')->orderBy('created_at', 'desc');

        // Supervisors only see their subordinates
        if ($user->isSupervisor()) {
            $subordinateIds = User::where('reporting_to', $user->id)->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }
        // Admins see everything EXCEPT their own requests (if any)
        elseif ($user->isAdmin()) {
            $query->where('user_id', '!=', $user->id);
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $leaves = $query->paginate(10);

        // Calculate counts for stats cards based on the same visibility rules
        $statsQuery = Leave::query();
        if ($user->isSupervisor()) {
            $subordinateIds = User::where('reporting_to', $user->id)->pluck('id');
            $statsQuery->whereIn('user_id', $subordinateIds);
        } elseif ($user->isAdmin()) {
            $statsQuery->where('user_id', '!=', $user->id);
        }

        $counts = [
            'all' => $statsQuery->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'partially_approved' => (clone $statsQuery)->where('status', 'approved_by_supervisor')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $statsQuery)->where('status', 'cancelled')->count(),
        ];

        Log::info('Leave Approvals Accessed', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'visible_records' => $leaves->total(),
            'page' => $leaves->currentPage()
        ]);

        return view('leaves.approvals', compact('leaves', 'counts'));
    }

    public function adminReport(Request $request)
    {
        // 1. Check permissions
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // 2. Data Preparation
        $users = User::orderBy('full_name')->get();
        
        $selectedUserId = $request->user_id ?? ($users->first()->id ?? null);
        $selectedYear = $request->year ?? now()->year;
        
        $selectedUser = $users->find($selectedUserId);
        
        if (!$selectedUser) {
            return view('leaves.admin-report', [
                'users' => $users,
                'summary' => [],
                'chartData' => [],
                'selectedUser' => null,
                'selectedYear' => $selectedYear
            ]);
        }

        // 3. Logic for Report
        // We'll iterate months Jan-Dec
        $monthlyStats = [];
        $totalWorked = 0;
        $totalPresent = 0;
        $totalPaidLeave = 0;
        $totalUnpaidLeave = 0;

        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($selectedYear, $month, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($selectedYear, $month, 1)->endOfMonth();
            
            // Standard Working Days (M-F)
            // Or simplified: Days in month minus weekends.
            $workingDays = 0;
            $current = $monthStart->copy();
            while ($current <= $monthEnd) {
                if ($current->isWeekday()) {
                    $workingDays++;
                }
                $current->addDay();
            }

            // Actual Present Days (from Attendance)
            $presentDays = \App\Models\Attendance::where('user_id', $selectedUserId)
                ->whereYear('date', $selectedYear)
                ->whereMonth('date', $month)
                ->where(function($q) {
                    $q->where('status', 'present')->orWhereNotNull('clock_in');
                })
                ->count();

            // Paid Leaves (Approved)
            // Leave duration can span months, but for simplicity we rely on start_date month
            // A more robust solution would split days across months. 
            // For this quick implementation, we sum days for leaves STARTING in this month.
            $paidLeaves = Leave::where('user_id', $selectedUserId)
                ->where('leave_type', 'paid')
                ->where('status', 'approved')
                ->whereYear('start_date', $selectedYear)
                ->whereMonth('start_date', $month)
                ->sum('days');

            $unpaidLeaves = Leave::where('user_id', $selectedUserId)
                ->where('leave_type', 'unpaid')
                ->where('status', 'approved')
                ->whereYear('start_date', $selectedYear)
                ->whereMonth('start_date', $month)
                ->sum('days');

            $monthlyStats[] = [
                'month_name' => $monthStart->format('F'),
                'working_days' => $workingDays, 
                'present_days' => $presentDays,
                'paid_leave' => $paidLeaves,
                'unpaid_leave' => $unpaidLeaves
            ];

            // Aggregates
            $totalWorked += $workingDays; // Using theoretical working days for "Total Working Days" card
            $totalPresent += $presentDays;
            $totalPaidLeave += $paidLeaves;
            $totalUnpaidLeave += $unpaidLeaves;
        }

        return view('leaves.admin-report', compact(
            'users', 
            'selectedUserId', 
            'selectedYear',
            'monthlyStats',
            'totalWorked',
            'totalPresent',
            'totalPaidLeave',
            'totalUnpaidLeave'
        ));
    }

    public function updateStatus(Request $request, Leave $leave)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $user = Auth::user();

        // Prevent approval of already rejected leaves
        if ($leave->status === 'rejected' && $request->status === 'approved') {
            return back()->withErrors(['error' => 'Cannot approve a rejected leave request. A rejected leave cannot be re-approved.']);
        }

        // Supervisor Approval Logic
        if ($user->isSupervisor()) {
            // Can only approve subordinates
            if ($leave->user->reporting_to != $user->id) {
                abort(403, 'Unauthorized action.');
            }
            
            if ($request->status === 'approved') {
                $leave->update(['status' => 'approved_by_supervisor']);
            } else {
                $leave->update(['status' => 'rejected', 'rejected_by' => 'supervisor']);
            }
        } 
        // Admin Approval Logic
        else if ($user->isAdmin()) {
            // Admin can approve anyone's request
            if ($request->status === 'approved') {
                $leave->update(['status' => 'approved']);
                if ($leave->leave_type === 'paid') {
                    $leave->user->decrement('leave_balance', $leave->days);
                }

                // Sync with Attendance Table
                $currentDate = $leave->start_date->copy();
                $endDate = $leave->end_date->copy();

                while ($currentDate->lte($endDate)) {
                    \App\Models\Attendance::updateOrCreate(
                        [
                            'user_id' => $leave->user_id,
                            'date' => $currentDate->toDateString()
                        ],
                        [
                            'status' => 'leave',
                            'clock_in' => null,
                            'clock_out' => null,
                            'duration' => null,
                        ]
                    );
                    $currentDate->addDay();
                }
            } else {
                $leave->update(['status' => 'rejected', 'rejected_by' => 'admin']);
            }
        }

        // Send Telegram Notification to Employee
        if ($leave->user->telegram_chat_id) {
            $statusText = str_replace('_', ' ', $leave->status);
            $message = "<b>Leave Alert</b>\n\n";
            $message .= "Your leave request for {$leave->start_date} has been <b>" . strtoupper($statusText) . "</b> by {$user->name}.";
            
            $this->telegramService->sendMessage($leave->user->telegram_chat_id, $message);
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Leave status updated successfully.']);
        }

        return redirect()->back()->with('success', 'Leave status updated successfully.');
    }

    /**
     * Cancel a leave request by the employee
     */
    public function cancel(Leave $leave)
    {
        $user = Auth::user();

        // Only the employee who created the leave can cancel it
        if ($leave->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Cannot cancel if already rejected or cancelled
        if (in_array($leave->status, ['rejected', 'cancelled'])) {
            return back()->withErrors(['error' => 'This leave request cannot be cancelled as it is already ' . $leave->status . '.']);
        }

        // If leave was approved and paid, restore the balance
        if ($leave->status === 'approved' && $leave->leave_type === 'paid') {
            $leave->user->increment('leave_balance', $leave->days);

            // Remove from attendance table
            $currentDate = $leave->start_date->copy();
            $endDate = $leave->end_date->copy();

            while ($currentDate->lte($endDate)) {
                \App\Models\Attendance::where('user_id', $leave->user_id)
                    ->where('date', $currentDate->toDateString())
                    ->where('status', 'leave')
                    ->delete();
                $currentDate->addDay();
            }
        }

        // Update status to cancelled
        $leave->update(['status' => 'cancelled']);

        // Send Telegram Notification to Supervisor
        if ($user->manager && $user->manager->telegram_chat_id) {
            $message = "<b>🚫 Leave Cancelled</b>\n\n";
            $message .= "Employee: {$user->name}\n";
            $message .= "<b>Cancelled By:</b> {$user->name} (Self)\n";
            $message .= "<b>Leave Type:</b> " . ucfirst($leave->leave_type) . "\n";
            $message .= "<b>Duration:</b> {$leave->days} day(s)\n";
            $message .= "<b>Dates:</b> {$leave->start_date->format('Y-m-d')} to {$leave->end_date->format('Y-m-d')}\n";
            $message .= "<b>Reason for Leave:</b> {$leave->reason}\n";
            $message .= "<b>Cancelled At:</b> " . now()->format('Y-m-d H:i A');
            
            $this->telegramService->sendMessage($user->manager->telegram_chat_id, $message);
        }

        // Send Telegram Notification to Admins
        $admins = User::where('role_id', 2)->whereNotNull('telegram_chat_id')->get();
        foreach ($admins as $admin) {
            $adminMessage = "<b>🚫 Leave Cancelled Alert</b>\n\n";
            $adminMessage .= "Employee: {$user->name}\n";
            $adminMessage .= "<b>Cancelled By:</b> {$user->name}\n";
            $adminMessage .= "<b>Leave Type:</b> " . ucfirst($leave->leave_type) . "\n";
            $adminMessage .= "<b>Previous Status:</b> " . ucfirst(str_replace('_', ' ', $leave->getOriginal('status'))) . "\n";
            $adminMessage .= "<b>Dates:</b> {$leave->start_date->format('Y-m-d')} to {$leave->end_date->format('Y-m-d')}\n";
            $adminMessage .= "<b>Cancelled At:</b> " . now()->format('Y-m-d H:i A');
            
            $this->telegramService->sendMessage($admin->telegram_chat_id, $adminMessage);
        }

        return redirect()->route('leaves.index')->with('success', 'Leave request cancelled successfully. Balance has been restored if applicable.');
    }

    private function getReportData($selectedYear, $selectedMonth, $selectedUserId)
    {
        $selectedUser = $selectedUserId ? User::find($selectedUserId) : null;
        $monthlyData = [];
        $totals = ['working_days' => 0, 'present' => 0, 'paid_leave' => 0, 'unpaid_leave' => 0];

        if ($selectedUser) {
             $currentYear = Carbon::now()->year;
             $currentMonth = Carbon::now()->month;
             
             $startM = ($selectedMonth === 'all') ? 1 : (int)$selectedMonth;
             $endM = ($selectedMonth === 'all') ? 12 : (int)$selectedMonth;

             if ($selectedMonth === 'all' && (int)$selectedYear === $currentYear) {
                 $endM = $currentMonth;
             }

             for ($m = $startM; $m <= $endM; $m++) {
                 if ($m > 12) break;
                 
                 $startOfMonth = Carbon::create($selectedYear, $m, 1);
                 $endOfMonth = $startOfMonth->copy()->endOfMonth();
                 
                 // 1. Calculate Working Days (Potential)
                 $holidays = \App\Models\Holiday::whereBetween('date', [$startOfMonth, $endOfMonth])->get()->map(function($h){ return $h->date->format('Y-m-d'); })->toArray();
                 $holidayCount = count($holidays);
                 
                 $sundays = 0;
                 $temp = $startOfMonth->copy();
                 while ($temp->lte($endOfMonth)) {
                     if ($temp->isSunday() && !in_array($temp->format('Y-m-d'), $holidays)) {
                         $sundays++;
                     }
                     $temp->addDay();
                 }
                 
                 $daysInMonth = $startOfMonth->daysInMonth;
                 $workingDays = max(0, $daysInMonth - $holidayCount - $sundays);
                 
                 // 2. Present Days
                 $present = \App\Models\Attendance::where('user_id', $selectedUserId)
                             ->whereBetween('date', [$startOfMonth, $endOfMonth])
                             ->where('status', 'present')
                             ->count();
                             
                 // 3. Leaves (Paid/Unpaid)
                 $leaves = Leave::where('user_id', $selectedUserId)
                           ->where('status', 'approved')
                           ->where(function($q) use ($startOfMonth, $endOfMonth) {
                               $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                                 ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                                 ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                                     $q2->where('start_date', '<', $startOfMonth)
                                        ->where('end_date', '>', $endOfMonth);
                                 });
                           })
                           ->get();
                 
                 $paid = 0; 
                 $unpaid = 0;
                 foreach ($leaves as $leave) {
                     $lStart = Carbon::parse($leave->start_date);
                     $lEnd = Carbon::parse($leave->end_date);
                     
                     $overlapStart = $lStart->lt($startOfMonth) ? $startOfMonth : $lStart;
                     $overlapEnd = $lEnd->gt($endOfMonth) ? $endOfMonth : $lEnd;
                     
                     if ($overlapStart->lte($overlapEnd)) {
                         $days = $overlapStart->diffInDays($overlapEnd) + 1;
                         if ($leave->leave_type === 'paid') $paid += $days;
                         else $unpaid += $days;
                     }
                 }
                 
                 $monthlyData[] = [
                     'month' => $startOfMonth->format('F'),
                     'working_days' => $workingDays,
                     'present' => $present,
                     'paid_leave' => $paid,
                     'unpaid_leave' => $unpaid
                 ];
                 
                 $totals['working_days'] += $workingDays;
                 $totals['present'] += $present;
                 $totals['paid_leave'] += $paid;
                 $totals['unpaid_leave'] += $unpaid;
             }
        }
        
        return ['monthlyData' => $monthlyData, 'totals' => $totals, 'user' => $selectedUser];
    }

    public function report(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $users = User::orderBy('full_name')->get();
        $years = range(Carbon::now()->year, Carbon::now()->year - 4);
        
        $selectedYear = $request->input('year', Carbon::now()->year);
        $selectedMonth = $request->input('month', 'all'); 
        $selectedUserId = $request->input('user_id');
        
        $data = $this->getReportData($selectedYear, $selectedMonth, $selectedUserId);
        $monthlyData = $data['monthlyData'];
        $totals = $data['totals'];
        $selectedUser = $data['user'];

        return view('leaves.report', compact('users', 'years', 'selectedYear', 'selectedMonth', 'selectedUser', 'monthlyData', 'totals'));
    }

    public function exportReport(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $selectedYear = $request->input('year', Carbon::now()->year);
        $selectedMonth = $request->input('month', 'all');
        $selectedUserId = $request->input('user_id');

        if (!$selectedUserId) {
            return back()->with('error', 'Please select an employee to export.');
        }

        $data = $this->getReportData($selectedYear, $selectedMonth, $selectedUserId);
        $monthlyData = $data['monthlyData'];
        $totals = $data['totals'];
        $user = $data['user'];

        $fileName = 'leave_report_' . $user->id . '_' . $selectedYear . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Month', 'Total Working Days', 'Present Days', 'Paid Leave', 'Unpaid Leave'];

        $callback = function() use($monthlyData, $columns, $totals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($monthlyData as $row) {
                fputcsv($file, [
                    $row['month'],
                    $row['working_days'],
                    $row['present'],
                    $row['paid_leave'],
                    $row['unpaid_leave']
                ]);
            }
            
            // Add Totals Row
            fputcsv($file, []);
            fputcsv($file, ['TOTALS', $totals['working_days'], $totals['present'], $totals['paid_leave'], $totals['unpaid_leave']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
