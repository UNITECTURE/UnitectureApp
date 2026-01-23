<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
    }

    /**
     * Send a message to a specific Telegram Chat ID.
     *
     * @param string $chatId
     * @param string $message
     * @return bool
     */
    public function sendMessage($chatId, $message)
    {
        if (!$this->token || !$chatId) {
            Log::warning("Telegram notification skipped: Token or Chat ID missing.");
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Telegram API Error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Telegram Notification Exception: " . $e->getMessage());
            return false;
        }
    }
}
