<?php

use Illuminate\Support\Facades\Route;
use App\Services\TelegramService;
use App\Models\User;
use Illuminate\Http\Request;

// Add this to your routes/web.php file

Route::get('/telegram/setup-webhook', function(TelegramService $telegram) {
    // Get the correct bot token
    $botToken = config('telegram_tasks.bot_token', config('telegram.bot_token'));
    
    // Set webhook URL (replace with your actual domain when deployed)
    $webhookUrl = url('/telegram/webhook');
    
    $response = Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
        'url' => $webhookUrl
    ]);
    
    return response()->json([
        'success' => true,
        'response' => $response->json(),
        'webhook_url' => $webhookUrl
    ]);
})->name('telegram.setup.webhook');

Route::post('/telegram/webhook', function(Request $request, TelegramService $telegram) {
    $data = $request->all();
    
    // Check if it's a message
    if (isset($data['message'])) {
        $chatId = $data['message']['chat']['id'];
        $text = $data['message']['text'] ?? '';
        $username = $data['message']['from']['username'] ?? 'Unknown';
        
        // Handle /start command
        if ($text === '/start') {
            $message = "👋 <b>Welcome to Unitecture App Notifications!</b>\n\n";
            $message .= "📱 Your Telegram Chat ID is: <code>{$chatId}</code>\n\n";
            $message .= "To receive notifications:\n";
            $message .= "1. Copy your chat ID above\n";
            $message .= "2. Go to your profile in Unitecture App\n";
            $message .= "3. Paste this chat ID in the Telegram field\n";
            $message .= "4. Save your profile\n\n";
            $message .= "✅ You'll then receive notifications for:\n";
            $message .= "• Task assignments and updates\n";
            $message .= "• Leave approvals/rejections\n";
            $message .= "• Manual attendance requests\n";
            
            $telegram->sendMessage($chatId, $message);
        }
        
        // Handle /myid command
        if ($text === '/myid') {
            $message = "📱 Your Telegram Chat ID: <code>{$chatId}</code>\n\n";
            $message .= "Copy this ID and add it to your Unitecture App profile to receive notifications.";
            
            $telegram->sendMessage($chatId, $message);
        }
    }
    
    return response()->json(['ok' => true]);
})->name('telegram.webhook');

Route::get('/telegram/test/{userId}', function($userId, TelegramService $telegram) {
    $user = User::find($userId);
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    if (!$user->telegram_chat_id) {
        return response()->json(['error' => 'User has no Telegram chat ID'], 400);
    }
    
    try {
        $message = "🎉 <b>Test Notification</b>\n\n";
        $message .= "Hello {$user->full_name}!\n\n";
        $message .= "If you received this message, your Telegram notifications are working correctly! ✅";
        
        $telegram->sendMessage($user->telegram_chat_id, $message);
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification sent successfully',
            'user' => $user->full_name,
            'chat_id' => $user->telegram_chat_id
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to send message',
            'details' => $e->getMessage()
        ], 500);
    }
})->name('telegram.test');
