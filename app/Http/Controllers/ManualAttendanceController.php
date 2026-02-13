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
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'reason' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (str_word_count($value) > 30) {
                        $fail('The ' . $attribute . ' must not exceed 30 words.');
                    }
                },
            ],
        ]);

        // Check for overlapping manual attendance requests on the same date
        if ($request->start_time && $request->end_time) {
            $requestStart = Carbon::parse($request->date . ' ' . $request->start_time);
            $requestEnd = Carbon::parse($request->date . ' ' . $request->end_time);

            // Find existing requests for the same user and date (pending or approved)
            $existingRequests = ManualAttendanceRequest::where('user_id', $request->user_id)
                ->where('date', $request->date)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            foreach ($existingRequests as $existing) {
                if ($existing->start_time && $existing->end_time) {
                    $existingStart = Carbon::parse($existing->date . ' ' . $existing->start_time);
                    $existingEnd = Carbon::parse($existing->date . ' ' . $existing->end_time);

                    // Check for overlap: new request overlaps if it starts before existing ends AND ends after existing starts
                    if ($requestStart->lt($existingEnd) && $requestEnd->gt($existingStart)) {
                        return redirect()->back()
                            ->withErrors([
                                'error' => 'You already have a manual attendance request for an overlapping time period (' .
                                    $existing->start_time . ' - ' . $existing->end_time . ') on this date.'
                            ])
                            ->withInput();
                    }
                }
            }
        }

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

        // Also notify all Admins (Role ID 2) about the manual attendance request
        $admins = User::where('role_id', 2)->whereNotNull('telegram_chat_id')->get();
        foreach ($admins as $admin) {
            // Don't send duplicate notification if admin is already the supervisor
            if (!$supervisor || $admin->id !== $supervisor->id) {
                $admin->notify(new NewManualAttendanceRequest($manualRequest));
            }
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
        if (!$durationStr)
            return 0;
        // Robust regex for "8h", "8 hrs", "8 Hours" etc (Case insensitive)
        preg_match('/(\d+)\s*[hH]/i', $durationStr, $hMatch);
        preg_match('/(\d+)\s*[mM]/i', $durationStr, $mMatch);

        $h = isset($hMatch[1]) ? (int) $hMatch[1] : 0;
        $m = isset($mMatch[1]) ? (int) $mMatch[1] : 0;

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
     * API endpoint to check for overlapping manual attendance requests
     */
    public function checkOverlap(Request $request)
    {
        $userId = $request->input('user_id');
        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if (!$userId || !$date || !$startTime || !$endTime) {
            return response()->json(['has_overlap' => false]);
        }

        try {
            $requestStart = Carbon::parse($date . ' ' . $startTime);
            $requestEnd = Carbon::parse($date . ' ' . $endTime);

            // Find existing requests for the same user and date (pending or approved)
            $existingRequests = ManualAttendanceRequest::where('user_id', $userId)
                ->where('date', $date)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            foreach ($existingRequests as $existing) {
                if ($existing->start_time && $existing->end_time) {
                    $existingStart = Carbon::parse($existing->date . ' ' . $existing->start_time);
                    $existingEnd = Carbon::parse($existing->date . ' ' . $existing->end_time);

                    // Check for overlap
                    if ($requestStart->lt($existingEnd) && $requestEnd->gt($existingStart)) {
                        return response()->json([
                            'has_overlap' => true,
                            'message' => 'You already have a request for ' . $existing->start_time . ' - ' . $existing->end_time . ' on this date.'
                        ]);
                    }
                }
            }

            return response()->json(['has_overlap' => false]);
        } catch (\Exception $e) {
            return response()->json(['has_overlap' => false]);
        }
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
                    $diffMinutes = abs($start->diffInMinutes($end));

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
