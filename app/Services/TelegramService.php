<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $chatId;

    public function __construct(?string $botToken = null, ?string $chatId = null)
    {
        if ($botToken !== null && $chatId !== null) {
            $this->botToken = $botToken;
            $this->chatId = $chatId;
        } else {
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
    }

    /**
     * Create a TelegramService instance with specific credentials
     */
    public static function withCredentials(string $botToken, string $chatId): self
    {
        return new self($botToken, $chatId);
    }

    /**
     * Create a TelegramService instance for a specific user
     */
    public static function forUser(User $user): ?self
    {
        $settings = $user->settings;
        
        if (!$settings || empty($settings->telegram_bot_token) || empty($settings->telegram_chat_id)) {
            return null;
        }
        
        return self::withCredentials($settings->telegram_bot_token, $settings->telegram_chat_id);
    }

    /**
     * Send a website down alert to Telegram
     */
    public function sendWebsiteDownAlert(
        string $url, 
        string $error, 
        ?int $statusCode = null, 
        ?string $redirectUrl = null, 
        ?string $errorType = null
    ): bool
    {
        // Choose emoji and title based on error type
        $emoji = "🚨";
        $title = "Website Alert";
        
        if ($errorType === 'redirect_suspicious' || $errorType === 'redirect_domain_change') {
            $emoji = "⚠️";
            $title = "Suspicious Redirect Detected";
        } elseif ($errorType === 'content_suspicious') {
            $emoji = "🔴";
            $title = "Suspicious Content Detected";
        } elseif ($errorType === 'content_directory_listing') {
            $emoji = "📁";
            $title = "Directory Listing Detected";
        } elseif ($errorType === 'redirect_unexpected') {
            $emoji = "⚠️";
            $title = "Unexpected Redirect";
        }
        
        $message = "{$emoji} <b>{$title}</b>\n\n";
        $message .= "🌐 <b>Original URL:</b> {$url}\n";
        
        if ($redirectUrl && $redirectUrl !== $url) {
            $message .= "↪️ <b>Redirected to:</b> {$redirectUrl}\n";
        }
        
        $message .= "❌ <b>Error:</b> {$error}\n";
        
        if ($statusCode) {
            $message .= "📊 <b>Status Code:</b> {$statusCode}\n";
        }
        
        if ($errorType) {
            $typeLabels = [
                'redirect_suspicious' => '⚠️ Suspicious Redirect',
                'redirect_domain_change' => '🚨 Domain Change / Possible Hack',
                'redirect_unexpected' => 'ℹ️ Unexpected Redirect',
                'content_suspicious' => '🔴 Suspicious Content',
                'content_directory_listing' => '📁 Directory Listing (Site Data Deleted)',
                'connection' => '🔌 Connection Error',
                'ssl' => '🔒 SSL Error',
                'dns' => '🌐 DNS Error',
                'timeout' => '⏱️ Timeout',
                'http' => '📡 HTTP Error',
            ];
            
            if (isset($typeLabels[$errorType])) {
                $message .= "🏷️ <b>Type:</b> {$typeLabels[$errorType]}\n";
            }
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
