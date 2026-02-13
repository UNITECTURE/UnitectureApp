<?php

namespace App\Http\Controllers;

use App\Models\CalendarNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarNoteController extends Controller
{
    /**
     * Get calendar notes for authenticated user or specific employee (if supervisor)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $targetUserId = $request->query('user_id', $user->id);

        // Check if user can view the calendar notes
        if ($targetUserId != $user->id) {
            if (!($user->isSupervisor() || $user->isAdmin())) {
                abort(403, 'Unauthorized');
            }
        }

        $notes = CalendarNote::where('user_id', $targetUserId)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($notes);
    }

    /**
     * Get notes for a specific date range
     */
    public function getByDateRange(Request $request)
    {
        $user = Auth::user();
        $targetUserId = $request->query('user_id', $user->id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Check authorization
        if ($targetUserId != $user->id) {
            if (!($user->isSupervisor() || $user->isAdmin())) {
                abort(403, 'Unauthorized');
            }
        }

        $notes = CalendarNote::where('user_id', $targetUserId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($notes);
    }

    /**
     * Store a new calendar note
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $targetUserId = $request->input('user_id', $user->id);

        // Check authorization
        if ($targetUserId != $user->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'date' => 'required|date',
            'note' => 'required|string|max:500',
        ]);

        $data['user_id'] = $targetUserId;

        // Check if note already exists for this date
        $existingNote = CalendarNote::where('user_id', $targetUserId)
            ->where('date', $data['date'])
            ->first();

        if ($existingNote) {
            $existingNote->update(['note' => $data['note']]);
            return response()->json([
                'message' => 'Note updated successfully',
                'note' => $existingNote,
            ], 200);
        }

        $note = CalendarNote::create($data);

        return response()->json([
            'message' => 'Note created successfully',
            'note' => $note,
        ], 201);
    }

    /**
     * Update a calendar note
     */
    public function update(Request $request, CalendarNote $calendarNote)
    {
        $user = Auth::user();

        // Check authorization
        if ($calendarNote->user_id != $user->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $calendarNote->update($data);

        return response()->json([
            'message' => 'Note updated successfully',
            'note' => $calendarNote,
        ]);
    }

    /**
     * Delete a calendar note
     */
    public function destroy(CalendarNote $calendarNote)
    {
        $user = Auth::user();

        // Check authorization
        if ($calendarNote->user_id != $user->id) {
            abort(403, 'Unauthorized');
        }

        $calendarNote->delete();

        return response()->json([
            'message' => 'Note deleted successfully',
        ]);
    }
}
