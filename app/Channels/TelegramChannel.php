<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        // Ensure user has a mapped Telegram ID
        if (empty($notifiable->telegram_chat_id)) {
            return;
        }

        // Get the formatted message from the notification class
        if (!method_exists($notification, 'toTelegram')) {
            Log::warning('Notification class missing toTelegram method');
            return;
        }

        $messageData = $notification->toTelegram($notifiable);
        
        // Handle both simple string or array return
        $text = is_array($messageData) ? ($messageData['text'] ?? '') : $messageData;

        if (empty($text)) {
            return;
        }

        $token = config('services.telegram.bot_token');
        if (empty($token)) {
             Log::warning('Telegram Bot Token is missing in .env');
             return;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $notifiable->telegram_chat_id,
                'text' => $text,
                'parse_mode' => 'HTML', // Using HTML allows bold/italic
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Telegram Notification Exception: " . $e->getMessage());
        }
    }
}
