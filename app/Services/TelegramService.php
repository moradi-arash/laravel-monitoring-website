<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $chatId;

    public function __construct()
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($botToken)) {
            throw new \InvalidArgumentException('Telegram bot token is not configured. Please set TELEGRAM_BOT_TOKEN in your .env file.');
        }

        if (empty($chatId)) {
            throw new \InvalidArgumentException('Telegram chat ID is not configured. Please set TELEGRAM_CHAT_ID in your .env file.');
        }

        $this->botToken = $botToken;
        $this->chatId = $chatId;
    }

    /**
     * Send a website down alert to Telegram
     */
    public function sendWebsiteDownAlert(string $url, string $error, ?int $statusCode = null): bool
    {
        $message = "🚨 <b>Website Down Alert</b>\n\n";
        $message .= "🌐 <b>Website:</b> {$url}\n";
        $message .= "❌ <b>Error:</b> {$error}\n";
        
        if ($statusCode) {
            $message .= "📊 <b>Status Code:</b> {$statusCode}\n";
        }
        
        $message .= "\n⏰ <b>Time:</b> " . now()->format('Y-m-d H:i:s');

        return $this->sendMessage($message);
    }

    /**
     * Send a generic message to Telegram
     */
    public function sendMessage(string $message): bool
    {
        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            
            $response = Http::post($url, [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Telegram API error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Telegram service error: ' . $e->getMessage());
            return false;
        }
    }
}
