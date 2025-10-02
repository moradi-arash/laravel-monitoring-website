<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Website;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorWebsites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:websites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor all active websites and send alerts for failures';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Fetch users who have active websites
        $users = User::whereHas('websites', function($query) {
            $query->where('is_active', true);
        })->with(['websites' => function($query) {
            $query->where('is_active', true);
        }, 'settings'])->get();
        
        $this->info("Starting website monitoring for {$users->count()} users");

        // Loop through each user
        foreach ($users as $user) {
            $this->checkUserWebsites($user);
        }

        $this->info("Website monitoring completed.");
        
        return Command::SUCCESS;
    }

    /**
     * Check all websites for a specific user
     */
    private function checkUserWebsites(User $user): void
    {
        $telegramService = TelegramService::forUser($user);
        
        if (!$telegramService) {
            $this->warn("User {$user->name} (ID: {$user->id}) has no Telegram credentials configured. Skipping alerts.");
        }
        
        $this->info("Checking {$user->websites->count()} websites for user {$user->name}");
        
        foreach ($user->websites as $website) {
            $this->checkWebsite($website, $telegramService);
        }
    }

    /**
     * Check a single website and update its status.
     */
    private function checkWebsite(Website $website, ?TelegramService $telegramService = null): void
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

        // Output CLI feedback
        if ($isSuccess) {
            $this->info("✓ {$website->name} ({$website->url}) - OK");
        } else {
            $this->error("✗ {$website->name} ({$website->url}) - FAILED: {$error}");
        }
    }
}



