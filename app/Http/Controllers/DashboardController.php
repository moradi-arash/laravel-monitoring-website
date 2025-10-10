<?php

namespace App\Http\Controllers;

use App\Models\Website;
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
        
        // Get the last 2 websites with errors (most recently checked)
        $websitesWithErrors = Website::where('user_id', $user->id)
            ->whereNotNull('last_error')
            ->where('last_error', '!=', '')
            ->orderBy('last_checked_at', 'desc')
            ->limit(2)
            ->get();
        
        return view('dashboard', compact(
            'totalWebsites',
            'activeWebsites', 
            'inactiveWebsites',
            'websitesWithErrors'
        ));
    }
}
