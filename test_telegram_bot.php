<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Testing Telegram Bot Tokens...\n";
echo "==========================================\n\n";

$mainBotToken = env('TELEGRAM_BOT_TOKEN');
$taskBotToken = env('TELEGRAM_TASK_BOT_TOKEN');

echo "Main Bot Token: " . substr($mainBotToken, 0, 20) . "...\n";
echo "Task Bot Token: " . substr($taskBotToken, 0, 20) . "...\n\n";

// Test Main Bot
echo "Testing Main Bot (TELEGRAM_BOT_TOKEN)...\n";
$response = Http::get("https://api.telegram.org/bot{$mainBotToken}/getMe");
if ($response->successful()) {
    $bot = $response->json()['result'];
    echo "✅ Main Bot is VALID\n";
    echo "   Bot Username: @{$bot['username']}\n";
    echo "   Bot Name: {$bot['first_name']}\n";
    echo "   Bot ID: {$bot['id']}\n";
} else {
    echo "❌ Main Bot is INVALID\n";
    echo "   Error: " . $response->body() . "\n";
}

echo "\n";

// Test Task Bot
echo "Testing Task Bot (TELEGRAM_TASK_BOT_TOKEN)...\n";
$response = Http::get("https://api.telegram.org/bot{$taskBotToken}/getMe");
if ($response->successful()) {
    $bot = $response->json()['result'];
    echo "✅ Task Bot is VALID\n";
    echo "   Bot Username: @{$bot['username']}\n";
    echo "   Bot Name: {$bot['first_name']}\n";
    echo "   Bot ID: {$bot['id']}\n";
} else {
    echo "❌ Task Bot is INVALID\n";
    echo "   Error: " . $response->body() . "\n";
}

echo "\n==========================================\n";
echo "Which bot is TelegramService using?\n";
echo "==========================================\n";

$configToken = config('services.telegram_tasks.bot_token', config('services.telegram.bot_token'));
if ($configToken === $taskBotToken) {
    echo "🎯 Currently using: TASK BOT (@{$bot['username']})\n";
} else if ($configToken === $mainBotToken) {
    echo "🎯 Currently using: MAIN BOT\n";
} else {
    echo "⚠️ Token mismatch!\n";
}

echo "\n==========================================\n";
echo "Testing message send to valid user...\n";
echo "==========================================\n";

// User 5 - Atharva (valid chat ID: 5880526986)
$testChatId = '5880526986';
$testMessage = '🧪 <b>Test from Unitecture</b>\n\nIf you receive this, notifications are working! ✅';

echo "Sending test message to Atharva (Chat ID: {$testChatId})...\n";

$telegram = app(\App\Services\TelegramService::class);
$result = $telegram->sendMessage($testChatId, $testMessage);

if ($result) {
    echo "✅ Message sent successfully!\n";
    echo "   Check Atharva's Telegram to confirm.\n";
} else {
    echo "❌ Message failed to send.\n";
    echo "   Check Laravel logs for details.\n";
    
    // Try direct API call to see exact error
    echo "\nTrying direct API call...\n";
    $directResponse = Http::post("https://api.telegram.org/bot{$configToken}/sendMessage", [
        'chat_id' => $testChatId,
        'text' => $testMessage,
        'parse_mode' => 'HTML',
    ]);
    
    if (!$directResponse->successful()) {
        echo "Error: " . $directResponse->body() . "\n";
    }
}

echo "\n==========================================\n";
echo "Testing message send to RAJ (1726037651)...\n";
echo "==========================================\n";

// User RAJ (Chat ID: 1726037651)
$rajChatId = '1726037651';
$rajMessage = '🧪 <b>Test for RAJ - Notification System Check</b>\n\nIf you receive this, task notifications should work! ✅\n\n<i>This is a system test message from the development team.</i>';

echo "Sending test message to RAJ (Chat ID: {$rajChatId})...\n";

$result = $telegram->sendMessage($rajChatId, $rajMessage);

if ($result) {
    echo "✅ Message sent successfully to RAJ!\n";
    echo "   Chat ID: {$rajChatId}\n";
    echo "   Check RAJ's Telegram to confirm receipt.\n";
} else {
    echo "❌ Message failed to send to RAJ.\n";
    echo "   Check Laravel logs for details.\n";
    
    // Try direct API call to see exact error
    echo "\nTrying direct API call...\n";
    $directResponse = Http::post("https://api.telegram.org/bot{$configToken}/sendMessage", [
        'chat_id' => $rajChatId,
        'text' => $rajMessage,
        'parse_mode' => 'HTML',
    ]);
    
    if ($directResponse->successful()) {
        echo "✅ Direct API call succeeded!\n";
    } else {
        echo "❌ Direct API call failed!\n";
        echo "Error: " . $directResponse->body() . "\n";
    }
}
