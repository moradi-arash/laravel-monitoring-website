<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telegram_bot_token',
        'telegram_chat_id',
        'notify_redirect_suspicious',
        'notify_redirect_domain_change',
        'notify_redirect_unexpected',
        'notify_content_suspicious',
        'notify_content_directory_listing',
        'notify_connection',
        'notify_ssl',
        'notify_dns',
        'notify_timeout',
        'notify_http',
    ];

    protected $casts = [
        'notify_redirect_suspicious' => 'boolean',
        'notify_redirect_domain_change' => 'boolean',
        'notify_redirect_unexpected' => 'boolean',
        'notify_content_suspicious' => 'boolean',
        'notify_content_directory_listing' => 'boolean',
        'notify_connection' => 'boolean',
        'notify_ssl' => 'boolean',
        'notify_dns' => 'boolean',
        'notify_timeout' => 'boolean',
        'notify_http' => 'boolean',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decrypted Telegram bot token.
     */
    public function getTelegramBotTokenAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // Try to decrypt, if it fails, return the original value (unencrypted)
        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails, the value is not encrypted, return as-is
            return $value;
        }
    }

    /**
     * Set the encrypted Telegram bot token.
     */
    public function setTelegramBotTokenAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['telegram_bot_token'] = null;
            return;
        }

        // Check if the value is already encrypted by trying to decrypt it
        try {
            decrypt($value);
            // If we get here, it's already encrypted, store as-is
            $this->attributes['telegram_bot_token'] = $value;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails, it's not encrypted, so encrypt it
            $this->attributes['telegram_bot_token'] = encrypt($value);
        }
    }

    /**
     * Get the decrypted Telegram chat ID.
     */
    public function getTelegramChatIdAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // Try to decrypt, if it fails, return the original value (unencrypted)
        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails, the value is not encrypted, return as-is
            return $value;
        }
    }

    /**
     * Set the encrypted Telegram chat ID.
     */
    public function setTelegramChatIdAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['telegram_chat_id'] = null;
            return;
        }

        // Check if the value is already encrypted by trying to decrypt it
        try {
            decrypt($value);
            // If we get here, it's already encrypted, store as-is
            $this->attributes['telegram_chat_id'] = $value;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails, it's not encrypted, so encrypt it
            $this->attributes['telegram_chat_id'] = encrypt($value);
        }
    }

    /**
     * Check if user should receive notification for specific error type
     */
    public function shouldNotify(?string $errorType): bool
    {
        // If no error type specified, allow notification
        if (!$errorType) {
            return true;
        }
        
        // Map error types to notification preference columns
        $preferenceMap = [
            'redirect_suspicious' => 'notify_redirect_suspicious',
            'redirect_domain_change' => 'notify_redirect_domain_change',
            'redirect_unexpected' => 'notify_redirect_unexpected',
            'content_suspicious' => 'notify_content_suspicious',
            'content_directory_listing' => 'notify_content_directory_listing',
            'connection' => 'notify_connection',
            'ssl' => 'notify_ssl',
            'dns' => 'notify_dns',
            'timeout' => 'notify_timeout',
            'http' => 'notify_http',
        ];
        
        $preferenceColumn = $preferenceMap[$errorType] ?? null;
        
        // If error type not mapped, allow notification
        if (!$preferenceColumn) {
            return true;
        }
        
        // Check specific preference - if null, default to true (allow notification)
        $value = $this->$preferenceColumn;
        return $value === null ? true : (bool) $value;
    }

    /**
     * Get all notification preferences
     */
    public function getNotificationPreferences(): array
    {
        return [
            'notify_redirect_suspicious' => $this->notify_redirect_suspicious,
            'notify_redirect_domain_change' => $this->notify_redirect_domain_change,
            'notify_redirect_unexpected' => $this->notify_redirect_unexpected,
            'notify_content_suspicious' => $this->notify_content_suspicious,
            'notify_content_directory_listing' => $this->notify_content_directory_listing,
            'notify_connection' => $this->notify_connection,
            'notify_ssl' => $this->notify_ssl,
            'notify_dns' => $this->notify_dns,
            'notify_timeout' => $this->notify_timeout,
            'notify_http' => $this->notify_http,
        ];
    }

    /**
     * Get human-readable labels for error types
     */
    public static function getErrorTypeLabels(): array
    {
        return [
            'redirect_suspicious' => 'Suspicious Redirects (Suspended Pages)',
            'redirect_domain_change' => 'Domain Change Redirects (Possible Hack)',
            'redirect_unexpected' => 'Unexpected Redirects',
            'content_suspicious' => 'Suspicious Content Detection',
            'content_directory_listing' => 'Directory Listing (Site Data Deleted)',
            'connection' => 'Connection Errors',
            'ssl' => 'SSL Certificate Errors',
            'dns' => 'DNS Resolution Errors',
            'timeout' => 'Timeout Errors',
            'http' => 'HTTP Errors (4xx, 5xx)',
        ];
    }
}