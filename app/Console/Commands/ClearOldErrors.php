<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;

class ClearOldErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websites:clear-old-errors {--days=7 : Number of days to keep errors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old errors from websites that are no longer active or have been fixed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("Clearing errors older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})");
        
        // Clear errors from inactive websites
        $inactiveCleared = Website::where('is_active', false)
            ->whereNotNull('last_error')
            ->update(['last_error' => null]);
        
        $this->info("Cleared errors from {$inactiveCleared} inactive websites");
        
        // Clear errors from websites that haven't been checked recently
        $oldErrorsCleared = Website::where('is_active', true)
            ->whereNotNull('last_error')
            ->where('last_checked_at', '<', $cutoffDate)
            ->update(['last_error' => null]);
        
        $this->info("Cleared errors from {$oldErrorsCleared} websites with old errors");
        
        // Show current error count
        $currentErrors = Website::where('is_active', true)
            ->whereNotNull('last_error')
            ->where('last_error', '!=', '')
            ->count();
        
        $this->info("Current active errors: {$currentErrors}");
        
        return 0;
    }
}


