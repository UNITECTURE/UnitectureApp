<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    /**
     * Show the team calendar view.
     */
    public function index()
    {
        $authUser = Auth::user();

        // Determine which users are visible and available for filtering
        if ($authUser->isAdmin()) {
            // Admins can see everyone
            $users = User::orderBy('full_name')->get();
        } elseif ($authUser->isSupervisor()) {
            // Supervisors see themselves and their direct reports
            $teamIds = $authUser->subordinates()->pluck('id')->toArray();
            $users = User::whereIn('id', array_merge([$authUser->id], $teamIds))
                ->orderBy('full_name')
                ->get();
        } else {
            // Employees only see themselves
            $users = collect([$authUser]);
        }

        return view('calendar.index', [
            'users' => $users,
        ]);
    }

    /**
     * Return JSON events for FullCalendar.
     */
    public function events(Request $request)
    {
        $authUser = Auth::user();

        $start = $request->query('start');
        $end = $request->query('end');

        if (!$start || !$end) {
            return response()->json(['events' => []]);
        }

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        // Determine visible user IDs based on role
        if ($authUser->isAdmin()) {
            $visibleUserIds = User::pluck('id');
        } elseif ($authUser->isSupervisor()) {
            $teamIds = $authUser->subordinates()->pluck('id');
            $visibleUserIds = $teamIds->push($authUser->id);
        } else {
            $visibleUserIds = collect([$authUser->id]);
        }

        // Optional filter by specific users (single or multi-select)
        $filterUserIds = $request->query('user_ids');
        if (is_string($filterUserIds)) {
            // Support comma-separated string
            $filterUserIds = array_filter(explode(',', $filterUserIds));
        }
        if (is_array($filterUserIds) && !empty($filterUserIds)) {
            $ids = collect($filterUserIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $visibleUserIds->contains($id))
                ->values();

            if ($ids->isNotEmpty()) {
                $visibleUserIds = $ids;
            }
        }

        // Get event type filters
        $eventTypes = $request->query('event_types', []);
        if (is_string($eventTypes)) {
            $eventTypes = array_filter(explode(',', $eventTypes));
        }
        if (!is_array($eventTypes)) {
            $eventTypes = [];
        }
        $eventTypes = array_map('strtolower', $eventTypes);
        $showLeaves = empty($eventTypes) || in_array('leave', $eventTypes);
        $showTasks = empty($eventTypes) || in_array('task', $eventTypes);
        $showAttendance = empty($eventTypes) || in_array('attendance', $eventTypes);

        $events = [];

        // Leaves (all-day, date range) — only approved
        if ($showLeaves) {
            $leaves = Leave::with('user')
                ->whereIn('user_id', $visibleUserIds)
                ->whereIn('status', ['approved', 'approved_by_supervisor'])
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate)
                ->get();

            foreach ($leaves as $leave) {
                $events[] = [
                    'id' => 'leave-' . $leave->id,
                    'title' => ($leave->user->name ?? 'User') . ' - Leave',
                    'start' => $leave->start_date?->toDateString(),
                    // FullCalendar expects exclusive end for all-day ranges
                    'end' => $leave->end_date?->copy()->addDay()->toDateString(),
                    'allDay' => true,
                    'color' => '#22c55e', // green (approved only)
                    'type' => 'leave',
                    'extendedProps' => [
                        'user' => $leave->user->name ?? null,
                        'status' => $leave->status,
                        'leave_type' => $leave->leave_type,
                        'reason' => $leave->reason,
                    ],
                ];
            }
        }

        // Holidays (company-wide all-day) - always show
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

        foreach ($holidays as $holiday) {
            $events[] = [
                'id' => 'holiday-' . $holiday->id,
                'title' => $holiday->name,
                'start' => $holiday->date?->toDateString(),
                'allDay' => true,
                'color' => '#3b82f6', // blue
                'type' => 'holiday',
                'extendedProps' => [
                    'description' => $holiday->description,
                ],
            ];
        }

        // Tasks (date range with optional time, only for assignees)
        if ($showTasks) {
            $tasks = Task::with(['assignees', 'project'])
                ->whereHas('assignees', function ($query) use ($visibleUserIds) {
                    $query->whereIn('users.id', $visibleUserIds);
                })
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate)
                ->get();

            foreach ($tasks as $task) {
                $assigneeNames = $task->assignees->pluck('name')->implode(', ');

                $events[] = [
                    'id' => 'task-' . $task->id,
                    'title' => $task->title,
                    'start' => optional($task->start_date)->toDateString(),
                    'end' => optional($task->end_date)->toIso8601String(),
                    'allDay' => false,
                    'color' => '#3b82f6', // light blue
                    'type' => 'task',
                    'extendedProps' => [
                        'project' => $task->project?->name,
                        'assignees' => $assigneeNames,
                        'priority' => $task->priority,
                        'status' => $task->status,
                        'stage' => $task->stage,
                    ],
                ];
            }
        }

        // Absences from Attendance (highlight days with explicit absence)
        if ($showAttendance) {
            $absences = Attendance::with('user')
                ->whereIn('user_id', $visibleUserIds)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'absent')
                ->get();

            foreach ($absences as $attendance) {
                $events[] = [
                    'id' => 'absence-' . $attendance->id,
                    'title' => ($attendance->user->name ?? 'User') . ' - Absent',
                    'start' => $attendance->date,
                    'allDay' => true,
                    'color' => '#ef4444', // red
                    'type' => 'absence',
                ];
            }
        }

        return response()->json([
            'events' => $events,
        ]);
    }
}

