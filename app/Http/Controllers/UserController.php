<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        $roles = Role::all();
        $managers = User::whereIn('role_id', [1, 2])->get(); // Supervisors and Admins
        return view('users.create', compact('roles', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'reporting_to' => 'nullable|exists:users,id',
            'joining_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'telegram_chat_id' => 'nullable|string|max:50',
        ]);

        // Calculate leave balance based on joining date
        $joiningDate = \Carbon\Carbon::parse($request->joining_date);
        $today = now();
        
        // Count complete calendar months (only months where the 1st has passed)
        $completedMonths = 0;
        $currentDate = $joiningDate->copy();
        
        while ($currentDate->addMonth() <= $today) {
            $completedMonths++;
        }
        
        // After 3 months probation, accrue 1.25 days per month
        $accrualMonths = max(0, $completedMonths - 3);
        $leaveBalance = $accrualMonths * 1.25;

        User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'reporting_to' => $request->reporting_to,
            'joining_date' => $request->joining_date,
            'status' => $request->status,
            'telegram_chat_id' => $request->telegram_chat_id,
            'leave_balance' => $leaveBalance,
        ]);

        return redirect()->route('dashboard')->with('success', 'User created successfully.');
    }
}
