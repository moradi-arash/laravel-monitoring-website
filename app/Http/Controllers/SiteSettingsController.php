<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
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
        $settings = SiteSetting::getInstance();
        
        return view('admin.site-settings.index', compact('settings'));
    }

    /**
     * Update the site settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:ratio=1/1',
        ], [
            'logo.image' => 'The uploaded file must be an image.',
            'logo.mimes' => 'The logo must be a JPEG, PNG, JPG, or WebP file.',
            'logo.max' => 'The logo file size must not exceed 2MB.',
            'logo.dimensions' => 'The logo must be square (1:1 aspect ratio).',
        ]);

        $settings = SiteSetting::getInstance();

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
        }

        // Update site name
        $settings->site_name = $validated['site_name'];
        $settings->save();

        return redirect()->route('admin.site-settings.index')
            ->with('status', 'settings-updated');
    }
}
