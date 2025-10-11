<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSettingSimple extends Model
{
    protected $table = 'site_settings';
    
    protected $fillable = [
        'site_name',
        'logo_path',
        'check_interval_minutes',
        'last_auto_check_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_interval_minutes' => 'integer',
            'last_auto_check_at' => 'datetime',
        ];
    }

    /**
     * Get the site name, falling back to config if not set.
     */
    public function getSiteName(): string
    {
        return $this->site_name ?? config('app.name', 'Laravel');
    }

    /**
     * Get the logo path, returning null if not set (for fallback to default).
     */
    public function getLogoPath(): ?string
    {
        return $this->logo_path;
    }

    /**
     * Get the next check time based on last check and interval.
     */
    public function getNextCheckTime(): \Carbon\Carbon
    {
        try {
            // Get interval and ensure it's an integer
            $interval = (int) ($this->getAttribute('check_interval_minutes') ?? 10);
            
            // Validate interval (must be positive)
            if ($interval <= 0) {
                $interval = 10;
            }
            
            // Handle timezone issues - if last_auto_check_at is null or invalid, use current time
            $lastAutoCheck = $this->getAttribute('last_auto_check_at');
            
            if (!$lastAutoCheck) {
                $lastCheck = now()->subMinutes($interval);
            } else {
                // Ensure the timestamp is properly parsed
                try {
                    $lastCheck = \Carbon\Carbon::parse($lastAutoCheck);
                } catch (\Exception $e) {
                    $lastCheck = now()->subMinutes($interval);
                }
            }
            
            return $lastCheck->addMinutes($interval);
        } catch (\Exception $e) {
            // Complete fallback if columns don't exist
            \Log::error('SiteSettingSimple::getNextCheckTime error: ' . $e->getMessage());
            return now()->addMinutes(10);
        }
    }

    /**
     * Get the time remaining until next check in seconds.
     */
    public function getTimeUntilNextCheck(): int
    {
        try {
            $nextCheck = $this->getNextCheckTime();
            $remaining = $nextCheck->diffInSeconds(now(), false);
            return max(0, $remaining);
        } catch (\Exception $e) {
            // Fallback to 10 minutes if there's any issue
            \Log::error('SiteSettingSimple::getTimeUntilNextCheck error: ' . $e->getMessage());
            return 600;
        }
    }

    /**
     * Get the last execution time from send_telegram.php log file
     */
    public function getLastTelegramExecutionTime(): ?\Carbon\Carbon
    {
        $logFile = public_path('telegram_web.log');
        
        if (!file_exists($logFile)) {
            return null;
        }
        
        try {
            // Read the last few lines of the log file
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if (empty($lines)) {
                return null;
            }
            
            // Look for MONITORING_START entries (which indicate when monitoring began)
            $lastMonitoringStart = null;
            
            foreach (array_reverse($lines) as $line) {
                if (strpos($line, 'MONITORING_START') !== false) {
                    // Extract timestamp from log line
                    preg_match('/\[([^\]]+)\]/', $line, $matches);
                    if (isset($matches[1])) {
                        $lastMonitoringStart = \Carbon\Carbon::parse($matches[1]);
                        break;
                    }
                }
            }
            
            return $lastMonitoringStart;
            
        } catch (\Exception $e) {
            \Log::error('Error reading telegram log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the execution method of the last monitoring run
     * 
     * @return string|null 'artisan', 'standalone', or null if unknown
     */
    public function getLastExecutionMethod(): ?string
    {
        $logFile = public_path('telegram_web.log');
        
        if (!file_exists($logFile)) {
            return null;
        }
        
        try {
            // Read the last few lines of the log file
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if (empty($lines)) {
                return null;
            }
            
            // Look for the last MONITORING_START entry
            foreach (array_reverse($lines) as $line) {
                if (strpos($line, 'MONITORING_START') !== false) {
                    // Check which method was used
                    if (strpos($line, 'Method: ARTISAN_COMMAND') !== false) {
                        return 'artisan';
                    } elseif (strpos($line, 'Method: STANDALONE_PHP') !== false) {
                        return 'standalone';
                    }
                    // Old format without method specification
                    return 'unknown';
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Error reading execution method from log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get human-readable label for execution method
     * 
     * @return string
     */
    public function getExecutionMethodLabel(): string
    {
        $method = $this->getLastExecutionMethod();
        
        return match($method) {
            'artisan' => 'Artisan Command',
            'standalone' => 'Standalone PHP',
            'unknown' => 'Unknown Method',
            default => 'Not Available',
        };
    }

    /**
     * Update last_auto_check_at from telegram log if available
     */
    public function updateLastCheckFromLog(): bool
    {
        try {
            // Check if the column exists before trying to update
            if (!Schema::hasColumn('site_settings', 'last_auto_check_at')) {
                return false;
            }
            
            $lastExecution = $this->getLastTelegramExecutionTime();
            
            if ($lastExecution) {
                try {
                    $this->setAttribute('last_auto_check_at', $lastExecution);
                    $this->save();
                    return true;
                } catch (\Exception $e) {
                    \Log::error('Error updating last_auto_check_at from log: ' . $e->getMessage());
                    return false;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error('Error in updateLastCheckFromLog: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get or create the singleton site settings instance.
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
