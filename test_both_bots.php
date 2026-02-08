<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TelegramService;
use App\Models\User;

echo "🤖 Testing Both Telegram Bots\n";
echo "==========================================\n\n";

// Test user: Atharva (ID 5) - has valid chat ID 5880526986
$user = User::find(5);

if (!$user || !$user->telegram_chat_id) {
    echo "❌ User not found or no chat ID\n";
    exit(1);
}

echo "Testing with user: {$user->full_name}\n";
echo "Chat ID: {$user->telegram_chat_id}\n\n";

// Test 1: Main Bot (for leaves/attendance)
echo "1️⃣ Testing MAIN BOT (Leaves/Attendance)...\n";
try {
    $mainBot = new TelegramService('main');
    $mainBot->sendMessage($user->telegram_chat_id, 
        "🏥 <b>Leave Test Notification</b>\n\n" .
        "This is from the <b>MAIN BOT</b> (@unitecturebot)\n" .
        "Used for: Leave & Attendance notifications\n\n" .
        "If you see this, leave notifications will work! ✅"
    );
    echo "   ✅ Main bot message sent\n\n";
} catch (\Exception $e) {
    echo "   ❌ Main bot failed: " . $e->getMessage() . "\n\n";
}

// Test 2: Task Bot (for tasks)
echo "2️⃣ Testing TASK BOT (Tasks)...\n";
try {
    $taskBot = new TelegramService('task');
    $taskBot->sendMessage($user->telegram_chat_id, 
        "📋 <b>Task Test Notification</b>\n\n" .
        "This is from the <b>TASK BOT</b> (@unitecturetaskbot)\n" .
        "Used for: Task assignments, updates & comments\n\n" .
        "If you see this, task notifications will work! ✅"
    );
    echo "   ✅ Task bot message sent\n\n";
} catch (\Exception $e) {
    echo "   ❌ Task bot failed: " . $e->getMessage() . "\n\n";
}

echo "==========================================\n";
echo "📱 Check Telegram to see which messages arrived!\n\n";
echo "Expected:\n";
echo "  • Main bot message → from @unitecturebot\n";
echo "  • Task bot message → from @unitecturetaskbot\n\n";
echo "⚠️  If you only receive ONE message:\n";
echo "  → User needs to START the other bot in Telegram\n";
echo "  → Each bot requires separate /start command\n";
