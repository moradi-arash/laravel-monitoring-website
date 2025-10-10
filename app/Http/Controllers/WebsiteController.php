<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\TelegramService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     * Manually trigger website monitoring check for current user only.
     */
    public function checkNow(): RedirectResponse
    {
        // Get current user's active websites
        $user = auth()->user();
        $websites = $user->websites()->where('is_active', true)->get();
        
        if ($websites->isEmpty()) {
            return redirect()->route('websites.index')
                ->with('status', 'no-websites-to-check');
        }
        
        // Check each website for current user only
        $this->checkUserWebsites($user, $websites);
        
        return redirect()->route('websites.index')
            ->with('status', 'websites-checked');
    }
    
    /**
     * Check websites for a specific user
     */
    private function checkUserWebsites($user, $websites): void
    {
        $telegramService = TelegramService::forUser($user);
        
        if (!$telegramService) {
            session()->flash('warning', 'No Telegram credentials configured. Alerts will not be sent.');
        }
        
        foreach ($websites as $website) {
            $this->checkWebsite($website, $telegramService);
        }
    }
    
    /**
     * Check a single website and update its status.
     */
    private function checkWebsite($website, $telegramService = null): void
    {
        $statusCode = null;
        $error = null;
        $isSuccess = true;

        try {
            // Send HTTP request with 10-second timeout
            $response = Http::timeout(10)->get($website->url);
            
            if ($response->successful()) {
                $statusCode = $response->status();
                Log::info("Website check successful: {$website->name} ({$website->url}) - Status: {$statusCode}");
            } else {
                $statusCode = $response->status();
                $error = "HTTP Error: Received status code {$statusCode}";
                $isSuccess = false;
                Log::warning("Website check failed: {$website->name} ({$website->url}) - Status: {$statusCode}");
            }

        } catch (ConnectionException $exception) {
            // Handle SSL errors, DNS failures, and connection timeouts
            $error = "Connection Error: " . $exception->getMessage();
            $isSuccess = false;
            
            // Check for SSL-specific errors
            if (str_contains(strtolower($error), 'ssl') || str_contains(strtolower($error), 'certificate')) {
                $error = "SSL Error: " . $exception->getMessage();
            }
            
            Log::error("Website connection failed: {$website->name} ({$website->url}) - {$error}");

        } catch (RequestException $exception) {
            // Handle HTTP-level errors (4xx, 5xx responses)
            $statusCode = $exception->response?->status();
            $error = "Request Failed: " . $exception->getMessage();
            $isSuccess = false;
            Log::error("Website request failed: {$website->name} ({$website->url}) - Status: {$statusCode} - {$error}");

        } catch (\Exception $exception) {
            // Handle any other unexpected errors
            $error = "Unexpected Error: " . $exception->getMessage();
            $isSuccess = false;
            Log::error("Website check unexpected error: {$website->name} ({$website->url}) - {$error}");
        }

        // Update database regardless of success/failure
        $website->update([
            'last_checked_at' => now(),
            'last_status_code' => $statusCode,
            'last_error' => $error,
        ]);

        // Send Telegram alert if failed and service is available
        if (!$isSuccess && $telegramService) {
            try {
                $telegramService->sendWebsiteDownAlert(
                    $website->url,
                    $error ?? 'Unknown error',
                    $statusCode
                );
            } catch (\Exception $telegramException) {
                Log::error("Failed to send Telegram alert for {$website->name}: " . $telegramException->getMessage());
            }
        }
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

    /**
     * Show the form for importing websites from CSV.
     */
    public function importForm(): View
    {
        return view('websites.import');
    }

    /**
     * Process CSV file and import websites.
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048', // 2MB max
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        
        if (!$handle) {
            return redirect()->back()
                ->withErrors(['csv_file' => 'Could not read the CSV file.'])
                ->withInput();
        }

        $rowNumber = 0;
        $importedCount = 0;
        $errors = [];
        $headers = null;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            // First row should be headers
            if ($rowNumber === 1) {
                $headers = $data;
                // Validate headers
                if (count($data) < 2 || strtolower(trim($data[0])) !== 'name' || strtolower(trim($data[1])) !== 'url') {
                    fclose($handle);
                    return redirect()->back()
                        ->withErrors(['csv_file' => 'Invalid CSV format. First row must contain "Name" and "URL" columns.'])
                        ->withInput();
                }
                continue;
            }

            // Validate row data
            if (count($data) < 2) {
                $errors[] = "Row {$rowNumber}: Insufficient data (need at least Name and URL)";
                continue;
            }

            $name = trim($data[0]);
            $url = trim($data[1]);
            $isActive = isset($data[2]) ? (trim($data[2]) === '1' || strtolower(trim($data[2])) === 'true') : true;

            // Validate individual fields
            if (empty($name)) {
                $errors[] = "Row {$rowNumber}: Name is required";
                continue;
            }

            if (empty($url)) {
                $errors[] = "Row {$rowNumber}: URL is required";
                continue;
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "Row {$rowNumber}: Invalid URL format";
                continue;
            }

            // Check for duplicate URLs for this user
            if (Website::where('user_id', auth()->id())->where('url', $url)->exists()) {
                $errors[] = "Row {$rowNumber}: URL already exists in your websites";
                continue;
            }

            // Create website
            try {
                Website::create([
                    'user_id' => auth()->id(),
                    'name' => $name,
                    'url' => $url,
                    'is_active' => $isActive,
                ]);
                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: Failed to create website - " . $e->getMessage();
            }
        }

        fclose($handle);

        // Prepare response
        $message = "Successfully imported {$importedCount} websites.";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " rows had errors.";
        }

        return redirect()->route('websites.index')
            ->with('status', 'websites-csv-imported')
            ->with('imported_count', $importedCount)
            ->with('error_count', count($errors))
            ->with('import_errors', $errors);
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate(): Response
    {
        $csvContent = "Name,URL,Is Active\n";
        $csvContent .= "Example Site,https://example.com,1\n";
        $csvContent .= "Test Site,https://test.com,0\n";

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="websites_template.csv"');
    }

    /**
     * Export user's websites to CSV.
     */
    public function exportCsv(): Response
    {
        $websites = auth()->user()->websites()->orderBy('created_at', 'desc')->get();
        
        $csvContent = "Name,URL,Is Active,Last Checked,Status Code,Error\n";
        
        foreach ($websites as $website) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                '"' . str_replace('"', '""', $website->name) . '"',
                '"' . str_replace('"', '""', $website->url) . '"',
                $website->is_active ? '1' : '0',
                $website->last_checked_at ? $website->last_checked_at->format('Y-m-d H:i:s') : '',
                $website->last_status_code ?? '',
                '"' . str_replace('"', '""', $website->last_error ?? '') . '"'
            );
        }

        $filename = 'websites_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
