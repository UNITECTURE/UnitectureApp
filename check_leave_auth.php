<?php
/**
 * Debug script to check leave cancellation authorization
 * Upload to GoDaddy and access via: https://hrms.unitecture.co/check_leave_auth.php?leave_id=11
 * DELETE after debugging!
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "=== Leave Cancellation Authorization Debug ===\n\n";

$leaveId = $_GET['leave_id'] ?? 11;

try {
    // Get authenticated user
    $user = auth()->user();
    if (!$user) {
        echo "ERROR: No authenticated user. Please login first.\n";
        exit;
    }
    
    echo "Current User:\n";
    echo "  ID: {$user->id}\n";
    echo "  Name: {$user->full_name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Role ID: {$user->role_id}\n\n";
    
    // Get leave
    $leave = App\Models\Leave::with('user')->find($leaveId);
    if (!$leave) {
        echo "ERROR: Leave ID {$leaveId} not found.\n";
        exit;
    }
    
    echo "Leave Request:\n";
    echo "  Leave ID: {$leave->id}\n";
    echo "  Leave User ID: {$leave->user_id}\n";
    echo "  Leave Status: {$leave->status}\n\n";
    
    if ($leave->user) {
        echo "Leave Owner:\n";
        echo "  Name: {$leave->user->full_name}\n";
        echo "  Email: {$leave->user->email}\n";
        echo "  Reporting To: " . ($leave->user->reporting_to ?? 'NULL') . "\n";
        echo "  Secondary Supervisor: " . ($leave->user->secondary_supervisor_id ?? 'NULL') . "\n\n";
    } else {
        echo "WARNING: Leave user relationship not loaded!\n\n";
    }
    
    // Authorization checks
    echo "Authorization Checks:\n";
    
    $isOwnLeave = ($leave->user_id == $user->id);
    echo "  Is Own Leave? " . ($isOwnLeave ? 'YES' : 'NO') . "\n";
    echo "    (Leave User ID {$leave->user_id} == Current User ID {$user->id})\n\n";
    
    $isSupervisor = false;
    if ($leave->user) {
        $isSupervisor = ($leave->user->reporting_to == $user->id || $leave->user->secondary_supervisor_id == $user->id);
        echo "  Is Supervisor? " . ($isSupervisor ? 'YES' : 'NO') . "\n";
        echo "    (Reporting To: {$leave->user->reporting_to} == {$user->id} OR Secondary: {$leave->user->secondary_supervisor_id} == {$user->id})\n\n";
    }
    
    $isAdminOrSuperAdmin = in_array($user->role_id, [2, 3]);
    echo "  Is Admin/Super Admin? " . ($isAdminOrSuperAdmin ? 'YES' : 'NO') . "\n";
    echo "    (Role ID {$user->role_id} in [2, 3])\n\n";
    
    $canCancel = $isOwnLeave || $isSupervisor || $isAdminOrSuperAdmin;
    echo "RESULT: Can Cancel? " . ($canCancel ? 'YES ✓' : 'NO ✗') . "\n";
    
    if (!$canCancel) {
        echo "\nREASON: User does not meet any authorization criteria.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
echo "<br><strong style='color:red;'>DELETE THIS FILE AFTER DEBUGGING!</strong>";
