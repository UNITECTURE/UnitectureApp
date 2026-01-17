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
        $leaves = Auth::user()->leaves()->orderBy('created_at', 'desc')->get();
        
        // Calculate Used Leaves (Approved & Paid)
        $usedLeaves = $leaves->where('status', 'approved')->where('leave_type', 'paid')->sum('days');
        
        return view('leaves.index', compact('leaves', 'usedLeaves'));
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
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;

        $user = Auth::user();
        $leaveType = ($user->leave_balance >= $days) ? 'paid' : 'unpaid';

        $leave = Leave::create([
            'user_id' => $user->id,
            'leave_type' => $leaveType,
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'status' => 'pending',
        ]);

        // Send Telegram Notification to Supervisor
        if ($user->manager && $user->manager->telegram_chat_id) {
            $message = "<b>New Leave Request</b>\n\n";
            $message .= "Employee: {$user->name}\n";
            $message .= "Duration: {$days} days ({$request->start_date} to {$request->end_date})\n";
            $message .= "Reason: {$request->reason}";
            
            $this->telegramService->sendMessage($user->manager->telegram_chat_id, $message);
        }

        // Also notify Admins (Role ID 2)
        $admins = User::where('role_id', 2)->whereNotNull('telegram_chat_id')->get();
        foreach ($admins as $admin) {
            $adminMessage = "<b>Alert: Leave requested</b>\n\n";
            $adminMessage .= "Employee: {$user->name}\n";
            $adminMessage .= "Status: Pending Supervisor\n";
            $adminMessage .= "Dates: {$request->start_date} to {$request->end_date}";
            
            $this->telegramService->sendMessage($admin->telegram_chat_id, $adminMessage);
        }

        return redirect()->route('leaves.index')->with('success', 'Leave requested successfully. System assigned type: ' . ucfirst($leaveType));
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
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
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

        // Supervisor Approval Logic
        if ($user->isSupervisor()) {
            // Can only approve subordinates
            if ($leave->user->reporting_to != $user->id) {
                abort(403, 'Unauthorized action.');
            }
            
            if ($request->status === 'approved') {
                $leave->update(['status' => 'approved_by_supervisor']);
            } else {
                $leave->update(['status' => 'rejected']);
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
            } else {
                $leave->update(['status' => 'rejected']);
            }
        }

        // Send Telegram Notification to Employee
        if ($leave->user->telegram_chat_id) {
            $statusText = str_replace('_', ' ', $leave->status);
            $message = "<b>Leave Alert</b>\n\n";
            $message .= "Your leave request for {$leave->start_date} has been <b>" . strtoupper($statusText) . "</b> by {$user->name}.";
            
            $this->telegramService->sendMessage($leave->user->telegram_chat_id, $message);
        }

        return back()->with('success', 'Leave status updated.');
    }
}
