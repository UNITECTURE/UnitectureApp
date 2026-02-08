<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\User;

$mainBotToken = env('TELEGRAM_BOT_TOKEN');

echo "Testing all users with main bot (@unitecturebot)...\n";
echo "==========================================\n\n";

$users = User::whereNotNull('telegram_chat_id')->get();

foreach ($users as $user) {
    echo "User {$user->id} - {$user->full_name} (Chat ID: {$user->telegram_chat_id})\n";
    
    $testMessage = "🧪 <b>Test from Unitecture</b>\n\nHello {$user->full_name}! This is a test notification. ✅";
    
    $response = Http::post("https://api.telegram.org/bot{$mainBotToken}/sendMessage", [
        'chat_id' => $user->telegram_chat_id,
        'text' => $testMessage,
        'parse_mode' => 'HTML',
    ]);
    
    if ($response->successful()) {
        echo "   ✅ SUCCESS - Message sent\n";
    } else {
        echo "   ❌ FAILED - " . $response->json()['description'] . "\n";
    }
    
    echo "\n";
    sleep(1); // Avoid rate limiting
}

echo "==========================================\n";
echo "Test complete! Check users' Telegram apps.\n";
