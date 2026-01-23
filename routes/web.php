<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ManualAttendanceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\BiometricPushController;

// Biometric Device Push Listener (Bypassed CSRF in bootstrap/app.php)
Route::any('/api/essl/attendance', [BiometricPushController::class, 'handlePush'])->name('api.biometric.push');

// Trigger Attendance Processing (Called by Bridge)
Route::get('/api/attendance/process/{date?}', function ($date = null) {
    \Illuminate\Support\Facades\Artisan::call('attendance:process', ['date' => $date]);
    return response()->json(['status' => 'processed', 'message' => 'Attendance calculations updated for ' . ($date ?? 'default')]);
});

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/');
    }
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $user = Auth::user();
        return redirect()->route('dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->name('login.post');

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Leaves & Users (Merged Routes)
Route::middleware('auth')->group(function () {
    Route::get('/leaves/admin-report', [App\Http\Controllers\LeaveController::class, 'adminReport'])->name('leaves.admin-report');
    Route::get('/leaves', [App\Http\Controllers\LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [App\Http\Controllers\LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leave-approvals', [App\Http\Controllers\LeaveController::class, 'approvals'])->name('leaves.approvals');
    Route::patch('/leaves/{leave}/status', [App\Http\Controllers\LeaveController::class, 'updateStatus'])->name('leaves.status');
    Route::get('/leaves/report', [App\Http\Controllers\LeaveController::class, 'report'])->name('leaves.report');
    Route::get('/leaves/export', [App\Http\Controllers\LeaveController::class, 'exportReport'])->name('leaves.export');

    // User Management
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
});

// Attendance Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/manual', [ManualAttendanceController::class, 'store'])->name('attendance.manual.store');
    Route::post('/attendance/manual/{id}/approve', [ManualAttendanceController::class, 'approve'])->name('attendance.manual.approve');
    Route::post('/attendance/manual/reject/{id}', [ManualAttendanceController::class, 'reject'])->name('attendance.manual.reject');
    Route::get('/attendance/manual', [AttendanceController::class, 'manualAccess'])->name('attendance.manual');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

    // Admin Routes
    Route::get('/admin/attendance', [AttendanceController::class, 'myAttendance'])->name('admin.attendance.self');
    Route::get('/admin/attendance/approvals', [AttendanceController::class, 'approvals'])->name('admin.attendance.approvals');
    Route::get('/admin/attendance/all', [AttendanceController::class, 'index'])->name('admin.attendance.all');

    // Supervisor Routes
    Route::get('/supervisor/attendance', [AttendanceController::class, 'myAttendance'])->name('supervisor.attendance.self');
    Route::get('/supervisor/attendance/approvals', [AttendanceController::class, 'approvals'])->name('supervisor.attendance.approvals');
    Route::get('/supervisor/attendance/team', [AttendanceController::class, 'index'])->name('supervisor.attendance.team');

    // Employee Routes
    Route::get('/employee/attendance', [AttendanceController::class, 'myAttendance'])->name('employee.attendance');
    // Employee Routes
    Route::get('/employee/attendance', [AttendanceController::class, 'myAttendance'])->name('employee.attendance');

    // Settings
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');


    Route::resource('holidays', App\Http\Controllers\HolidayController::class)->only(['index', 'store', 'destroy']);

    // Project Management
    Route::get('/projects/create', [App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');

    // Task Management
    Route::get('/tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/assigned', [App\Http\Controllers\TaskController::class, 'assigned'])->name('tasks.assigned');
    Route::get('/tasks/create', [App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('/tasks/{task}/stage', [App\Http\Controllers\TaskController::class, 'updateStage'])->name('tasks.updateStage');
    Route::get('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
});

// Test Telegram Route
Route::get('/dev/test-telegram', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user)
        return 'Please Login first';
    if (!$user->telegram_chat_id)
        return 'Current User has no Telegram ID mapped in DB';

    $token = env('TELEGRAM_BOT_TOKEN');
    $response = \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
        'chat_id' => $user->telegram_chat_id,
        'text' => "🔔 Test Message from Unitecture App",
    ]);

    return "Telegram API Response: " . $response->body();
});

Route::get('/dev/check-schema', function () {
    $results = [];
    $results['user_exists'] = \Illuminate\Support\Facades\Schema::hasTable('users');
    if ($results['user_exists']) {
        $results['user_columns'] = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    }
    $results['leaves_exists'] = \Illuminate\Support\Facades\Schema::hasTable('leaves');
    $results['roles_exists'] = \Illuminate\Support\Facades\Schema::hasTable('roles');
    return $results;
});
