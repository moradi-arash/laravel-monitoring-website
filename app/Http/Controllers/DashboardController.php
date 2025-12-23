<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\SiteSettingSimple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get website statistics for the authenticated user
        $totalWebsites = Website::where('user_id', $user->id)->count();
        $activeWebsites = Website::where('user_id', $user->id)->where('is_active', true)->count();
        $inactiveWebsites = Website::where('user_id', $user->id)->where('is_active', false)->count();
        
        // Get websites with errors (only active websites with recent errors)
        $websitesWithErrors = Website::where('user_id', $user->id)
            ->where('is_active', true) // Only show active websites
            ->whereNotNull('last_error')
            ->where('last_error', '!=', '')
            ->orderBy('last_checked_at', 'desc')
            ->get();
        
        // Count total errors
        $totalErrors = $websitesWithErrors->count();
        
        // Get site settings for countdown (with comprehensive fallback)
        try {
            $siteSettings = SiteSettingSimple::getInstance();
            
            // Try to update last_auto_check_at from telegram log
            try {
                $logUpdated = $siteSettings->updateLastCheckFromLog();
                
                if ($logUpdated) {
                    \Log::info('Updated last_auto_check_at from telegram log');
                }
            } catch (\Exception $e) {
                \Log::warning('Could not update from log: ' . $e->getMessage());
            }
            
            // Always try to use the methods (they have fallbacks built-in)
            $nextCheckTime = $siteSettings->getNextCheckTime();
            $timeUntilNextCheck = $siteSettings->getTimeUntilNextCheck();
            
            // Safely get check_interval_minutes (ensure it's an integer)
            try {
                $checkInterval = (int) ($siteSettings->getAttribute('check_interval_minutes') ?? 10);
                if ($checkInterval <= 0) {
                    $checkInterval = 10;
                }
            } catch (\Exception $e) {
                $checkInterval = 10;
            }
            
            // Get execution method information
            $executionMethod = $siteSettings->getLastExecutionMethod();
            $executionMethodLabel = $siteSettings->getExecutionMethodLabel();
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Dashboard countdown error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            // Complete fallback - use current time + 10 minutes
            $siteSettings = null;
            $nextCheckTime = now()->addMinutes(10);
            $timeUntilNextCheck = 600; // 10 minutes in seconds
            $checkInterval = 10;
            $executionMethod = null;
            $executionMethodLabel = 'Not Available';
        }
        
        // Ensure we have valid Carbon instances
        if (!$nextCheckTime instanceof \Carbon\Carbon) {
            $nextCheckTime = now()->addMinutes(10);
        }
        
        return view('dashboard', compact(
            'totalWebsites',
            'activeWebsites', 
            'inactiveWebsites',
            'websitesWithErrors',
            'totalErrors',
            'nextCheckTime',
            'timeUntilNextCheck',
            'siteSettings',
            'checkInterval',
            'executionMethod',
            'executionMethodLabel'
        ));
    }
}
