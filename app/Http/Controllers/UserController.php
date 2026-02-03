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
            'biometric_id' => 'nullable|string|max:20|unique:users,biometric_id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('profile_image')) {
            $uploadedFile = $request->file('profile_image');
            
            // Check if Cloudinary credentials are set because Cloudinary package crashes if config is missing
            $hasCloudinary = !empty(env('CLOUDINARY_URL')) || (!empty(env('CLOUDINARY_CLOUD_NAME')) && !empty(env('CLOUDINARY_KEY')) && !empty(env('CLOUDINARY_SECRET')));

            if ($hasCloudinary) {
                try {
                    $uploadResult = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($uploadedFile->getRealPath(), [
                        'folder' => 'unitecture_users',
                        'resource_type' => 'auto'
                    ]);
                    $imageUrl = $uploadResult->getSecurePath();
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                    // Fallback to local if Cloudinary fails
                     $path = $uploadedFile->store('profile_images', 'public');
                    $imageUrl = asset('storage/' . $path);
                }
            } else {
                // Fallback to local storage
                $path = $uploadedFile->store('profile_images', 'public');
                $imageUrl = asset('storage/' . $path);
            }
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
    public function team()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        // Fetch users reporting to the current user
        $team = User::where('reporting_to', $user->id)
                    ->with('role') 
                    ->get();
        
        return view('team.index', compact('team'));
    }

    /**
     * Show user management page (Admin only)
     */
    public function manageUsers()
    {
        $users = User::with('role')->orderBy('created_at', 'desc')->get();
        return view('users.manage', compact('users'));
    }

    /**
     * Delete a user (Admin only)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === \Illuminate\Support\Facades\Auth::id()) {
            return redirect()->route('users.manage')->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->route('users.manage')->with('success', 'User deleted successfully.');
    }

    /**
     * Calculate leave balance based on joining date
     * Counts only complete calendar months after 3-month probation
     * Accrues 1.25 days per month
     */
    private function calculateLeaveBalance($joiningDate)
    {
        $joiningDate = \Carbon\Carbon::parse($joiningDate);
        $today = now();
        
        // Count complete calendar months (only months where the 1st has passed)
        $completedMonths = 0;
        $currentDate = $joiningDate->copy();
        
        while ($currentDate->addMonth() <= $today) {
            $completedMonths++;
        }
        
        // After 3 months probation, accrue 1.25 days per month
        $accrualMonths = max(0, $completedMonths - 3);
        return $accrualMonths * 1.25;
    }
}
