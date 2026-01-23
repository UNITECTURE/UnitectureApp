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
            'biometric_id' => 'nullable|integer|unique:users,biometric_id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('profile_image')) {
            $uploadedFile = $request->file('profile_image');
            $uploadResult = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($uploadedFile->getRealPath(), [
                'folder' => 'unitecture_users'
            ]);
            $imageUrl = $uploadResult->getSecurePath();
        }

        User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'reporting_to' => $request->reporting_to,
            'joining_date' => $request->joining_date,
            'status' => $request->status,
            'telegram_chat_id' => $request->telegram_chat_id,
            'biometric_id' => $request->biometric_id,
            'leave_balance' => 0, // Default balance for new users
            'profile_image' => $imageUrl,
        ]);

        return redirect()->route('dashboard')->with('success', 'User created successfully.');
    }
}
