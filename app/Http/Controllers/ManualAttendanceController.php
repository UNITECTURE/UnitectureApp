<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\NewManualAttendanceRequest;
use App\Notifications\ManualAttendanceStatusUpdated;

use Carbon\Carbon;

class ManualAttendanceController extends Controller
{
    // Apply for manual attendance (Employee)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => [
                'required', 
                'date', 
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    $today = Carbon::today();
                    $minDate = $today->copy()->subDays(4);
                    $maxDate = $today->copy()->endOfMonth();

                    if ($date->lt($minDate)) {
                        $fail("You can only apply for attendance for the past 4 days.");
                    }
                // Future dates allowed within this month (checked by maxDate below)
                    if ($date->gt($maxDate)) {
                         $fail("You cannot apply for dates beyond the current month (" . $maxDate->format('M d') . ").");
                    }
                },
            ],
            'duration' => 'required|string',
            'reason' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
        ]);

        $manualRequest = ManualAttendanceRequest::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $request->duration,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Notify Supervisor or Superadmin
        $employee = User::find($request->user_id);
        $supervisor = null;

        if ($employee && $employee->reporting_to) {
            $supervisor = User::find($employee->reporting_to);
        } else {
            // Fallback: If no supervisor (e.g. Admin), notify Superadmin (Role 3)
            $supervisor = User::where('role_id', 3)->first();
        }

        if ($supervisor) {
            $supervisor->notify(new NewManualAttendanceRequest($manualRequest));
        }

        return redirect()->back()->with('success', 'Manual attendance requested successfully.');
    }

    // Approve manual attendance (Supervisor)
    public function approve(Request $request, $id)
    {
        $manualRequest = ManualAttendanceRequest::findOrFail($id);
        $approver = Auth::user();

        // 1. Prevent Approval of Own Request
        if ($manualRequest->user_id === $approver->id) {
            return redirect()->back()->with('error', 'You cannot approve your own request.');
        }

        // 2. If Requester is Admin (Role 2), Approver MUST be Super Admin (Role 3)
        $requester = User::find($manualRequest->user_id);
        if ($requester && $requester->role_id === 2 && $approver->role_id !== 3) {
            return redirect()->back()->with('error', 'Only Super Admin can approve Admin requests.');
        }
        
        // Update request status
        $manualRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
        ]);

        // Find existing attendance or create new
        $attendance = Attendance::firstOrNew([
            'user_id' => $manualRequest->user_id,
            'date' => $manualRequest->date,
        ]);

        // Calculate Total Duration (Overwrite with Manual)
        // $existingMinutes = $this->parseDuration($attendance->duration); // Removed to prevent double counting
        $manualMinutes = $this->parseDuration($manualRequest->duration);
        
        $totalMinutes = $manualMinutes;
        
        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;
        $totalDurationString = "{$hours} Hrs {$mins} Mins";

        // Update Record
        $attendance->duration = $totalDurationString;
        $attendance->status = 'present'; 
        $attendance->type = 'manual';

        
        $attendance->save();

        // Notify Employee
        $employee = User::find($manualRequest->user_id);
        if ($employee) {
            $employee->notify(new ManualAttendanceStatusUpdated($manualRequest));
        }

        return redirect()->back()->with('success', 'Attendance approved and record updated.');
    }

    private function parseDuration(?string $durationStr)
    {
        if (!$durationStr) return 0;
        // Robust regex for "8h", "8 hrs", "8 Hours" etc (Case insensitive)
        preg_match('/(\d+)\s*[hH]/i', $durationStr, $hMatch);
        preg_match('/(\d+)\s*[mM]/i', $durationStr, $mMatch);
        
        $h = isset($hMatch[1]) ? (int)$hMatch[1] : 0;
        $m = isset($mMatch[1]) ? (int)$mMatch[1] : 0;
        
        return ($h * 60) + $m;
    }

    public function reject(Request $request, $id)
    {
        $manualRequest = ManualAttendanceRequest::findOrFail($id);
        
        $manualRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id() ?? 1,
            'rejection_reason' => $request->input('reason', 'Rejected by supervisor'), 
        ]);

        // Notify Employee
        $employee = User::find($manualRequest->user_id);
        if ($employee) {
            $employee->notify(new ManualAttendanceStatusUpdated($manualRequest));
        }

        return redirect()->back()->with('success', 'Attendance request rejected.');
    }

    public function cancel(Request $request, $id)
    {
        $manualRequest = ManualAttendanceRequest::findOrFail($id);
        $user = Auth::user();

        // Check authorization (User can cancel their own, or Admin)
        if ($manualRequest->user_id !== $user->id && $user->role_id !== 2 && $user->role_id !== 3) {
            return redirect()->back()->with('error', 'You are not authorized to cancel this request.');
        }

        if ($manualRequest->status === 'cancelled') {
             return redirect()->back()->with('info', 'Request is already cancelled.');
        }

        // Revert Attendance if needed
        $this->revertAttendanceEffect($manualRequest);

        // Update Request Status
        $manualRequest->status = 'cancelled';
        $manualRequest->save();

        return redirect()->back()->with('success', 'Manual attendance request cancelled and hours reverted.');
    }



    /**
     * Helper to revert attendance changes if the request was approved.
     */
    private function revertAttendanceEffect(ManualAttendanceRequest $manualRequest)
    {
        if ($manualRequest->status === 'approved') {
            $attendance = Attendance::where('user_id', $manualRequest->user_id)
                            ->where('date', $manualRequest->date)
                            ->first();

            if ($attendance) {
                // If clock_in and clock_out exist, revert to biometric calculation
                if ($attendance->clock_in && $attendance->clock_out) {
                    $start = Carbon::parse($attendance->clock_in);
                    $end = Carbon::parse($attendance->clock_out);
                    $diffMinutes = $start->diffInMinutes($end);

                    $h = floor($diffMinutes / 60);
                    $m = $diffMinutes % 60;
                    
                    $attendance->duration = "{$h} Hrs {$m} Mins";
                    $attendance->status = 'present';
                    $attendance->type = 'biometric'; // Revert type
                } else {
                    // No biometric data, reset to absent/empty
                    $attendance->duration = null;
                    $attendance->status = 'absent';
                    $attendance->type = 'biometric';
                }
                $attendance->save();
            }
        }
    }
}
