<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $websites = auth()->user()->websites()->orderBy('created_at', 'desc')->get();
        
        return view('websites.index', compact('websites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('websites.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active');

        Website::create($validated);

        return redirect()->route('websites.index')
            ->with('status', 'website-created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Website $website): View
    {
        if ($website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('websites.edit', compact('website'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Website $website): RedirectResponse
    {
        if ($website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $website->update($validated);

        return redirect()->route('websites.index')
            ->with('status', 'website-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Website $website): RedirectResponse
    {
        if ($website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $website->delete();

        return redirect()->route('websites.index')
            ->with('status', 'website-deleted');
    }

    /**
     * Manually trigger website monitoring check.
     */
    public function checkNow(): RedirectResponse
    {
        // Trigger the monitoring command
        Artisan::call('monitor:websites');
        
        return redirect()->route('websites.index')
            ->with('status', 'websites-checked');
    }

    /**
     * Show the form for creating multiple websites.
     */
    public function bulkCreate(): View
    {
        return view('websites.bulk-create');
    }

    /**
     * Store multiple websites in storage.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        // Filter out empty rows (where both name and url are empty)
        $websites = collect($request->input('websites', []))
            ->filter(function ($website) {
                return !empty($website['name']) || !empty($website['url']);
            })
            ->values()
            ->toArray();

        // Ensure at least one website remains
        if (empty($websites)) {
            return redirect()->back()
                ->withErrors(['websites' => 'At least one website is required.'])
                ->withInput();
        }

        // Merge the filtered data back into the request for validation
        $request->merge(['websites' => $websites]);

        // Validate the filtered data
        $validated = $request->validate([
            'websites' => 'required|array|min:1',
            'websites.*.name' => 'required|string|max:255',
            'websites.*.url' => 'required|url|max:255',
            'websites.*.is_active' => 'nullable|boolean',
        ]);

        $createdCount = 0;
        
        foreach ($validated['websites'] as $websiteData) {
            $websiteData['is_active'] = isset($websiteData['is_active']) ? true : false;
            $websiteData['user_id'] = auth()->id();
            Website::create($websiteData);
            $createdCount++;
        }

        return redirect()->route('websites.index')
            ->with('status', 'websites-bulk-created')
            ->with('count', $createdCount);
    }
}
