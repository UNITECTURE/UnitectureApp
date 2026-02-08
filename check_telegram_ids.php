<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = DB::table('users')->select('id', 'full_name', 'telegram_chat_id')->get();

echo "Total Users: " . $users->count() . "\n";
echo "==========================================\n\n";

$withChatId = 0;
$withoutChatId = 0;

foreach($users as $user) {
    if ($user->telegram_chat_id) {
        $withChatId++;
        echo "✅ ID: {$user->id} | {$user->full_name} | Chat ID: {$user->telegram_chat_id}\n";
    } else {
        $withoutChatId++;
        echo "❌ ID: {$user->id} | {$user->full_name} | Chat ID: NULL\n";
    }
}

echo "\n==========================================\n";
echo "Users WITH Telegram Chat ID: $withChatId\n";
echo "Users WITHOUT Telegram Chat ID: $withoutChatId\n";
