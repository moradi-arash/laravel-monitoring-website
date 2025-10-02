<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCronSettingsRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $settings = auth()->user()->settings;
        
        // If settings don't exist, create an empty instance for the form
        if (!$settings) {
            $settings = new UserSetting();
        }
        
        // Load global cron configuration only for admins
        $globalConfig = null;
        if (auth()->user()->isAdmin()) {
            $globalConfig = [
                'cron_allowed_ip' => config('app.cron_allowed_ip') ?: env('CRON_ALLOWED_IP'),
                'cron_secret_key' => config('app.cron_secret_key') ?: env('CRON_SECRET_KEY'),
            ];
        }
        
        return view('settings.index', compact('settings', 'globalConfig'));
    }

    /**
     * Update the user's settings.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        UserSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );
        
        return redirect()->route('settings.index')
            ->with('status', 'settings-updated');
    }

    /**
     * Update the cron settings.
     */
    public function updateCron(UpdateCronSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        try {
            // Read the current .env file
            $envPath = base_path('.env');
            $envContent = File::get($envPath);
            
            // Update the cron settings in .env content
            $envContent = $this->updateEnvValue($envContent, 'CRON_ALLOWED_IP', $validated['cron_allowed_ip']);
            $envContent = $this->updateEnvValue($envContent, 'CRON_SECRET_KEY', $validated['cron_secret_key']);
            
            // Write the updated content back to .env
            File::put($envPath, $envContent);
            
            return redirect()->route('settings.index')
                ->with('status', 'cron-settings-updated');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['cron' => 'Failed to update cron settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Update a value in .env content.
     */
    private function updateEnvValue(string $content, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*$/m";
        $replacement = "{$key}={$value}";
        
        if (preg_match($pattern, $content)) {
            // Key exists, replace it
            return preg_replace($pattern, $replacement, $content);
        } else {
            // Key doesn't exist, append it
            return $content . "\n{$replacement}";
        }
    }
}
