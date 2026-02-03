<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $holidays = Holiday::orderBy('date', 'desc')->get();
        return view('settings.holidays', compact('holidays'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
        ]);

        Holiday::create($request->only('name', 'date', 'description'));

        return back()->with('success', 'Holiday added successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $holiday->delete();
        return back()->with('success', 'Holiday removed.');
    }
}
