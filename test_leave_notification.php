<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TelegramService;
use App\Models\User;
use App\Models\Leave;

echo "🏥 Testing Leave Notification for Atharva\n";
echo "==========================================\n\n";

// Get Atharva's latest leave request
$leave = Leave::where('user_id', 5)
    ->orderBy('created_at', 'desc')
    ->first();

if (!$leave) {
    echo "❌ No leave request found for Atharva\n";
    exit(1);
}

$leave->load('user');

echo "User: {$leave->user->full_name}\n";
echo "Chat ID: {$leave->user->telegram_chat_id}\n";
echo "Leave: {$leave->start_date} to {$leave->end_date}\n";
echo "Status: {$leave->status}\n";
echo "Reason: {$leave->reason}\n\n";

// Test sending approval notification (using MAIN bot)
echo "📤 Sending test leave approval notification...\n";
try {
    $mainBot = new TelegramService('main'); // Leave uses main bot
    
    $message = "🎉 <b>Leave Request Approved</b>\n\n";
    $message .= "📅 <b>Period:</b> {$leave->start_date} to {$leave->end_date}\n";
    $message .= "📝 <b>Reason:</b> {$leave->reason}\n";
    $message .= "✅ <b>Status:</b> Approved\n\n";
    $message .= "This is a TEST notification from MAIN BOT (@unitecturebot)";
    
    $mainBot->sendMessage($leave->user->telegram_chat_id, $message);
    
    echo "✅ Leave notification sent successfully!\n\n";
    echo "📱 Check Telegram - Atharva should receive this from @unitecturebot\n";
    
} catch (\Exception $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}
