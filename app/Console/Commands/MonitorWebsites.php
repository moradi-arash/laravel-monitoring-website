<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Website;
use App\Models\SiteSettingSimple;
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
        // Check if we should run based on the configured interval
        $siteSettings = SiteSettingSimple::getInstance();
        
        // If last check was recent, skip this run
        if ($siteSettings->last_auto_check_at) {
            $minutesSinceLastCheck = $siteSettings->last_auto_check_at->diffInMinutes(now());
            $interval = $siteSettings->check_interval_minutes ?? 10;
            if ($minutesSinceLastCheck < $interval) {
                $this->info("Skipping check - only {$minutesSinceLastCheck} minutes since last check (interval: {$interval} minutes)");
                return Command::SUCCESS;
            }
        }

        // Record the start time of automatic check
        try {
            $siteSettings->last_auto_check_at = now();
            $siteSettings->save();
            
            // Log to telegram_web.log for consistency with standalone PHP
            $logFile = public_path('telegram_web.log');
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "[{$timestamp}] MONITORING_START | Method: ARTISAN_COMMAND | Checking websites\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Field doesn't exist, continue without recording
            $this->warn("Could not record check time - field may not exist in database");
        }

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
        $redirectUrl = null;
        $errorType = null;

        try {
            // Send HTTP request with 10-second timeout and allow redirects
            $response = Http::timeout(10)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 5,
                        'track_redirects' => true
                    ]
                ])
                ->get($website->url);
            
            if ($response->successful()) {
                $statusCode = $response->status();
                
                // Get effective URL after redirects
                $effectiveUrl = $response->effectiveUri();
                if ($effectiveUrl) {
                    $effectiveUrl = (string) $effectiveUrl;
                }
                
                // Check for suspicious redirects even on successful response
                $redirectIssue = $this->checkForSuspiciousRedirect(
                    $website->url, 
                    $effectiveUrl, 
                    $response->body()
                );
                
                if ($redirectIssue) {
                    $error = $redirectIssue['error'];
                    $errorType = $redirectIssue['type'];
                    $redirectUrl = $effectiveUrl;
                    $isSuccess = false;
                    Log::warning("Website redirect issue: {$website->name} ({$website->url}) - {$error}");
                } else {
                    Log::info("Website check successful: {$website->name} ({$website->url}) - Status: {$statusCode}");
                }
            } else {
                $statusCode = $response->status();
                $error = "HTTP Error: Received status code {$statusCode}";
                $errorType = 'http';
                $isSuccess = false;
                Log::warning("Website check failed: {$website->name} ({$website->url}) - Status: {$statusCode}");
            }

        } catch (ConnectionException $exception) {
            // Handle SSL errors, DNS failures, and connection timeouts
            $error = "Connection Error: " . $exception->getMessage();
            $errorType = 'connection';
            $isSuccess = false;
            
            // Check for SSL-specific errors
            if (str_contains(strtolower($error), 'ssl') || str_contains(strtolower($error), 'certificate')) {
                $error = "SSL Error: " . $exception->getMessage();
                $errorType = 'ssl';
            }
            
            Log::error("Website connection failed: {$website->name} ({$website->url}) - {$error}");

        } catch (RequestException $exception) {
            // Handle HTTP-level errors (4xx, 5xx responses)
            $statusCode = $exception->response?->status();
            $error = "Request Failed: " . $exception->getMessage();
            $errorType = 'http';
            $isSuccess = false;
            Log::error("Website request failed: {$website->name} ({$website->url}) - Status: {$statusCode} - {$error}");

        } catch (\Exception $exception) {
            // Handle any other unexpected errors
            $error = "Unexpected Error: " . $exception->getMessage();
            $errorType = 'unknown';
            $isSuccess = false;
            Log::error("Website check unexpected error: {$website->name} ({$website->url}) - {$error}");
        }

        // Update database regardless of success/failure
        $updateData = [
            'last_checked_at' => now(),
            'last_status_code' => $statusCode,
        ];
        
        // Only set last_error if there's an actual error
        if (!$isSuccess && $error) {
            $updateData['last_error'] = $error;
        } else {
            // Clear the error if the website is now working
            $updateData['last_error'] = null;
        }
        
        $website->update($updateData);

        // Send Telegram alert if failed and service is available
        if (!$isSuccess && $telegramService) {
            try {
                $telegramService->sendWebsiteDownAlert(
                    $website->url,
                    $error ?? 'Unknown error',
                    $statusCode,
                    $redirectUrl,
                    $errorType
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

    /**
     * Check for suspicious redirects
     */
    private function checkForSuspiciousRedirect(string $originalUrl, ?string $effectiveUrl, ?string $responseBody): ?array
    {
        if (!$effectiveUrl) {
            return null;
        }

        // Normalize URLs for comparison
        $normalizedOriginal = $this->normalizeUrl($originalUrl);
        $normalizedEffective = $this->normalizeUrl($effectiveUrl);
        
        // If URLs are the same after normalization, no redirect issue
        if ($normalizedOriginal === $normalizedEffective) {
            return null;
        }
        
        // List of suspicious redirect patterns
        $suspiciousPatterns = [
            '/cgi-sys/suspendedpage.cgi' => 'Suspended Account',
            '/suspended.page' => 'Suspended Account',
            '/account_suspended' => 'Suspended Account',
            '/site-suspended' => 'Suspended Account',
            '/suspended' => 'Suspended Account',
            '/defaultwebpage.cgi' => 'Default cPanel Page',
            '/cpanel' => 'cPanel Login',
            '/404' => '404 Error Page',
            '/maintenance' => 'Maintenance Mode',
            '/coming-soon' => 'Coming Soon Page',
            '/under-construction' => 'Under Construction',
        ];
        
        // Check if redirected to suspicious page
        foreach ($suspiciousPatterns as $pattern => $description) {
            if (stripos($effectiveUrl, $pattern) !== false) {
                return [
                    'error' => "Suspicious Redirect: Website redirected to {$description} ({$effectiveUrl})",
                    'type' => 'redirect_suspicious'
                ];
            }
        }
        
        // Check for domain change (possible hack or DNS hijacking)
        $originalDomain = parse_url($normalizedOriginal, PHP_URL_HOST);
        $effectiveDomain = parse_url($normalizedEffective, PHP_URL_HOST);
        
        if ($originalDomain !== $effectiveDomain) {
            return [
                'error' => "Domain Change Detected: Redirected from {$originalDomain} to {$effectiveDomain} - Possible hack or DNS hijacking!",
                'type' => 'redirect_domain_change'
            ];
        }
        
        // Check response body for suspended/hacked indicators
        if ($responseBody) {
            $suspiciousContent = [
                'account has been suspended' => 'Account Suspended',
                'this account is suspended' => 'Account Suspended',
                'bandwidth limit exceeded' => 'Bandwidth Exceeded',
                'hacked by' => 'Website Hacked',
                'defaced by' => 'Website Defaced',
                'your site has been suspended' => 'Site Suspended',
                'temporarily unavailable' => 'Site Unavailable',
            ];
            
            $lowerBody = strtolower($responseBody);
            foreach ($suspiciousContent as $phrase => $description) {
                if (strpos($lowerBody, $phrase) !== false) {
                    return [
                        'error' => "Suspicious Content Detected: Page contains '{$description}' - URL: {$effectiveUrl}",
                        'type' => 'content_suspicious'
                    ];
                }
            }
        }
        
        // Check if only scheme changed (http->https) - this is normal
        $originalScheme = parse_url($normalizedOriginal, PHP_URL_SCHEME);
        $effectiveScheme = parse_url($normalizedEffective, PHP_URL_SCHEME);
        
        if ($originalScheme !== $effectiveScheme && 
            str_replace([$originalScheme, '://'], '', $normalizedOriginal) === 
            str_replace([$effectiveScheme, '://'], '', $normalizedEffective)) {
            return null; // Normal HTTPS redirect
        }
        
        // Report unexpected redirect (less critical)
        return [
            'error' => "Unexpected Redirect: Website redirected from {$originalUrl} to {$effectiveUrl}",
            'type' => 'redirect_unexpected'
        ];
    }

    /**
     * Normalize URL for comparison
     */
    private function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '/';
        
        // Remove www prefix for comparison
        $host = preg_replace('/^www\./', '', $host);
        
        // Remove trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) $path = '/';
        
        return $scheme . '://' . $host . $path;
    }
}



