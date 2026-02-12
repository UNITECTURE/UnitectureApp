<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function create()
    {
        $roles = Role::all();
        $managers = User::whereIn('role_id', [1, 2, 3])->get(); // Supervisors, Admins, and Super Admins
        return view('users.create', compact('roles', 'managers'));
    }

    public function store(Request $request)
    {
        // Store uploaded image temporarily if present (before validation)
        $tempImagePath = null;
        if ($request->hasFile('profile_image')) {
            $tempImagePath = $request->file('profile_image')->store('temp_uploads', 'public');
            session(['temp_profile_image' => $tempImagePath]);
        } elseif (session('temp_profile_image') && Storage::disk('public')->exists(session('temp_profile_image'))) {
            // Use previously uploaded temp image
            $tempImagePath = session('temp_profile_image');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'reporting_to' => 'nullable|exists:users,id',
            'secondary_supervisor_id' => 'nullable|exists:users,id',
            'joining_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'telegram_chat_id' => 'nullable|string|max:50',
            'biometric_id' => 'nullable|string|max:20|unique:users,biometric_id',
            'profile_image' => $tempImagePath ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        // Employees must have a primary supervisor
        if ((int) $request->role_id === 0 && empty($request->reporting_to)) {
            return back()->withErrors(['reporting_to' => 'Employees must have a primary supervisor.'])->withInput();
        }

        $imageUrl = null;
        if ($request->hasFile('profile_image')) {
            $uploadedFile = $request->file('profile_image');
            
            try {
                // Upload to Cloudinary using Storage facade
                $path = Storage::disk('cloudinary')
                    ->putFile('unitecture_users', $uploadedFile);
                
                // Get the full URL from Cloudinary
                $imageUrl = Storage::disk('cloudinary')->url($path);
                
                if (!$imageUrl) {
                    throw new \Exception('Failed to get URL from Cloudinary');
                }
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                return back()->withErrors(['profile_image' => 'Failed to upload image to Cloudinary.'])->withInput();
            }
        } elseif ($tempImagePath && Storage::disk('public')->exists($tempImagePath)) {
            // Upload the temp file to Cloudinary
            try {
                $tempFile = Storage::disk('public')->path($tempImagePath);
                $path = Storage::disk('cloudinary')
                    ->putFile('unitecture_users', new \Illuminate\Http\File($tempFile));
                
                $imageUrl = Storage::disk('cloudinary')->url($path);
                
                if (!$imageUrl) {
                    throw new \Exception('Failed to get URL from Cloudinary');
                }
                
                // Delete temp file
                Storage::disk('public')->delete($tempImagePath);
                
                if (!$imageUrl) {
                    throw new \Exception('Failed to get URL from Cloudinary');
                }
                
                // Delete temp file
                Storage::disk('public')->delete($tempImagePath);
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                return back()->withErrors(['profile_image' => 'Failed to upload image to Cloudinary.'])->withInput();
            }
        }

        User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'reporting_to' => $request->reporting_to,
            'secondary_supervisor_id' => $request->secondary_supervisor_id,
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
        // Primary + secondary subordinates (both can be assigned tasks)
        $team = User::where('reporting_to', $user->id)
            ->orWhere('secondary_supervisor_id', $user->id)
            ->with(['role', 'primarySupervisor', 'secondarySupervisor'])
            ->get();
        
        return view('team.index', compact('team'));
    }

    /**
     * Show user management page (Admin only)
     */
    public function manageUsers()
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403, 'Only admins can manage users.');
        }
        $users = User::with(['role', 'primarySupervisor', 'secondarySupervisor'])->orderBy('created_at', 'desc')->get();
        return view('users.manage', compact('users'));
    }

    /**
     * Show edit user form (Admin only)
     */
    public function edit($id)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403, 'Only admins can manage users.');
        }
        $user = User::findOrFail($id);
        $roles = Role::all();
        $managers = User::whereIn('role_id', [1, 2, 3])->get(); // Supervisors, Admins, and Super Admins
        return view('users.edit', compact('user', 'roles', 'managers'));
    }

    /**
     * Update user (Admin only)
     */
    public function update(Request $request, $id)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403, 'Only admins can manage users.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'reporting_to' => 'nullable|exists:users,id',
            'secondary_supervisor_id' => 'nullable|exists:users,id',
            'joining_date' => 'required|date',
            'telegram_chat_id' => 'nullable|string|max:50',
            'biometric_id' => 'nullable|string|max:20|unique:users,biometric_id,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Employees must have a primary supervisor
        if ((int) $request->role_id === 0 && empty($request->reporting_to)) {
            return back()->withErrors(['reporting_to' => 'Employees must have a primary supervisor.'])->withInput();
        }

        $imageUrl = $user->profile_image;
        if ($request->hasFile('profile_image')) {
            $uploadedFile = $request->file('profile_image');

            try {
                // Upload to Cloudinary using Storage facade
                $path = \Illuminate\Support\Facades\Storage::disk('cloudinary')
                    ->putFile('unitecture_users', $uploadedFile);
                
                // Get the full URL from Cloudinary
                $imageUrl = \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($path);
                
                if (!$imageUrl) {
                    throw new \Exception('Failed to get URL from Cloudinary');
                }
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                return back()->withErrors(['profile_image' => 'Failed to upload image to Cloudinary.'])->withInput();
            }
        }

        $user->full_name = $request->name;
        $user->email = $request->email;
        
        // Only update password if provided
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        
        $user->role_id = $request->role_id;
        $user->reporting_to = $request->reporting_to;
        $user->secondary_supervisor_id = $request->secondary_supervisor_id;
        $user->joining_date = $request->joining_date;
        $user->telegram_chat_id = $request->telegram_chat_id;
        $user->biometric_id = $request->biometric_id;
        $user->profile_image = $imageUrl;

        $user->save();

        return redirect()->route('users.manage')->with('success', 'User updated successfully.');
    }

    /**
     * Show all teams: each supervisor with their members (Admin only).
     */
    public function teamsIndex()
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403, 'Only admins can view teams.');
        }
        $supervisors = User::where('role_id', 1) // supervisor role
            ->with([
                'subordinates' => fn ($q) => $q->with(['role', 'secondarySupervisor']),
                'secondarySubordinates' => fn ($q) => $q->with(['role', 'primarySupervisor']),
            ])
            ->orderBy('full_name')
            ->get();
        $supervisorsList = User::whereIn('role_id', [1, 2, 3])->orderBy('full_name')->get(); // Supervisors, Admins, and Super Admins
        return view('teams.index', compact('supervisors', 'supervisorsList'));
    }

    /**
     * Assign or update secondary supervisor for an employee (Admin only).
     */
    public function updateSecondarySupervisor(Request $request, User $user)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403, 'Only admins can assign secondary supervisor.');
        }
        $request->validate([
            'secondary_supervisor_id' => 'nullable|exists:users,id',
        ]);
        $user->secondary_supervisor_id = $request->secondary_supervisor_id ?: null;
        $user->save();
        return redirect()->route('teams.index')->with('success', 'Secondary supervisor updated for ' . $user->full_name . '.');
    }

    /**
     * Remove employee from team: clear primary and secondary supervisor (Admin only).
     */
    public function removeFromTeam(User $user)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403, 'Only admins can remove members from teams.');
        }
        $user->reporting_to = null;
        $user->secondary_supervisor_id = null;
        $user->save();
        return redirect()->route('teams.index')->with('success', $user->full_name . ' has been removed from their team(s).');
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
