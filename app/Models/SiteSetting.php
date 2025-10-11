<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_name',
        'logo_path',
        'check_interval_minutes',
        'last_auto_check_at',
    ];

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_auto_check_at' => 'datetime',
        ];
    }

    /**
     * Get the next check time based on last check and interval.
     */
    public function getNextCheckTime(): \Carbon\Carbon
    {
        $interval = $this->check_interval_minutes ?? 10;
        $lastCheck = $this->last_auto_check_at ?? now()->subMinutes($interval);
        return $lastCheck->addMinutes($interval);
    }

    /**
     * Get the time remaining until next check in seconds.
     */
    public function getTimeUntilNextCheck(): int
    {
        $nextCheck = $this->getNextCheckTime();
        $remaining = $nextCheck->diffInSeconds(now(), false);
        return max(0, $remaining);
    }

    /**
     * Get or create the singleton site settings instance.
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
