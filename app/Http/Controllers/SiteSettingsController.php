<?php

namespace App\Http\Controllers;

use App\Models\SiteSettingSimple;
use App\Services\FaviconService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    /**
     * Display the site settings form.
     */
    public function index(): View
    {
        try {
            $settings = SiteSettingSimple::getInstance();
        } catch (\Exception $e) {
            $settings = new SiteSettingSimple();
        }
        
        return view('admin.site-settings.index', compact('settings'));
    }

    /**
     * Update the site settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'check_interval_minutes' => 'nullable|integer|min:1|max:1440',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:ratio=1/1',
        ], [
            'check_interval_minutes.integer' => 'Check interval must be a number.',
            'check_interval_minutes.min' => 'Check interval must be at least 1 minute.',
            'check_interval_minutes.max' => 'Check interval cannot exceed 1440 minutes (24 hours).',
            'logo.image' => 'The uploaded file must be an image.',
            'logo.mimes' => 'The logo must be a JPEG, PNG, JPG, or WebP file.',
            'logo.max' => 'The logo file size must not exceed 2MB.',
            'logo.dimensions' => 'The logo must be square (1:1 aspect ratio).',
        ]);

        try {
            $settings = SiteSettingSimple::getInstance();
        } catch (\Exception $e) {
            $settings = new SiteSettingSimple();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path && Storage::disk('public')->exists('logos/' . $settings->logo_path)) {
                Storage::disk('public')->delete('logos/' . $settings->logo_path);
            }

            // Store new logo
            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('logos', 'public');
            $settings->logo_path = basename($logoPath);
            
            // Generate favicons from the new logo
            $faviconService = new FaviconService();
            $faviconService->generateFavicon($settings->logo_path);
        }

        // Update site name and check interval
        $settings->site_name = $validated['site_name'];
        
        // Only update check_interval_minutes if the field exists in database
        if (isset($validated['check_interval_minutes']) && $validated['check_interval_minutes'] !== null) {
            try {
                $settings->check_interval_minutes = $validated['check_interval_minutes'];
            } catch (\Exception $e) {
                // Field doesn't exist in database, skip it
            }
        }
        
        $settings->save();

        return redirect()->route('admin.site-settings.index')
            ->with('status', 'settings-updated');
    }
}
