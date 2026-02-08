<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing invalid Telegram chat IDs...\n";
echo "==========================================\n\n";

// Set invalid chat IDs to NULL
$updated = DB::table('users')
    ->whereIn('id', [9, 11])  // raj and akshay
    ->update(['telegram_chat_id' => null]);

echo "✅ Updated {$updated} users with invalid chat IDs to NULL\n\n";

// Show updated users
$users = DB::table('users')
    ->whereIn('id', [9, 11])
    ->select('id', 'full_name', 'telegram_chat_id', 'email')
    ->get();

echo "Updated Users:\n";
echo "==========================================\n";
foreach($users as $user) {
    $chatId = $user->telegram_chat_id ?? 'NULL (needs to link Telegram)';
    echo "ID: {$user->id} | {$user->full_name} | Email: {$user->email} | Chat ID: {$chatId}\n";
}

echo "\n==========================================\n";
echo "📱 Next Steps:\n";
echo "1. Ask raj and akshay to link their Telegram accounts\n";
echo "2. They need to find your Telegram bot and click START\n";
echo "3. The bot should send them their correct chat ID\n";
echo "4. They update their profile with that chat ID\n";
