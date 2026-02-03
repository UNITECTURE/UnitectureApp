<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)],
            'new_password_confirmation' => ['required'],
        ], [
            'new_password.required' => 'New password is required',
            'new_password.confirmed' => 'Passwords do not match',
            'new_password.min' => 'Password must be at least 8 characters',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Password updated successfully!');
    }
}
