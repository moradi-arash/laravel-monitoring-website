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
     * Get or create the singleton site settings instance.
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
