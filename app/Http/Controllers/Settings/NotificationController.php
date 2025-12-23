<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display the notification preferences form.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get or create user settings
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notify_redirect_suspicious' => true,
                'notify_redirect_domain_change' => true,
                'notify_redirect_unexpected' => true,
                'notify_content_suspicious' => true,
<<<<<<< HEAD
                'notify_content_directory_listing' => true,
=======
>>>>>>> origin/main
                'notify_connection' => true,
                'notify_ssl' => true,
                'notify_dns' => true,
                'notify_timeout' => true,
                'notify_http' => true,
            ]
        );
        
        $errorTypeLabels = UserSetting::getErrorTypeLabels();
        
        return view('settings.notifications', compact('settings', 'errorTypeLabels'));
    }

    /**
     * Update the notification preferences.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'notify_redirect_suspicious' => 'boolean',
            'notify_redirect_domain_change' => 'boolean',
            'notify_redirect_unexpected' => 'boolean',
            'notify_content_suspicious' => 'boolean',
<<<<<<< HEAD
            'notify_content_directory_listing' => 'boolean',
=======
>>>>>>> origin/main
            'notify_connection' => 'boolean',
            'notify_ssl' => 'boolean',
            'notify_dns' => 'boolean',
            'notify_timeout' => 'boolean',
            'notify_http' => 'boolean',
        ]);
        
        // Convert checkbox values (present = true, absent = false)
        $preferences = [
            'notify_redirect_suspicious' => $request->has('notify_redirect_suspicious'),
            'notify_redirect_domain_change' => $request->has('notify_redirect_domain_change'),
            'notify_redirect_unexpected' => $request->has('notify_redirect_unexpected'),
            'notify_content_suspicious' => $request->has('notify_content_suspicious'),
<<<<<<< HEAD
            'notify_content_directory_listing' => $request->has('notify_content_directory_listing'),
=======
>>>>>>> origin/main
            'notify_connection' => $request->has('notify_connection'),
            'notify_ssl' => $request->has('notify_ssl'),
            'notify_dns' => $request->has('notify_dns'),
            'notify_timeout' => $request->has('notify_timeout'),
            'notify_http' => $request->has('notify_http'),
        ];
        
        // Update or create settings
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $preferences
        );
        
        return redirect()
            ->route('settings.notifications')
            ->with('success', 'Notification preferences updated successfully!');
    }
}