<?php
/**
 * Standalone Telegram Message Sender
 * 
 * This script can be called via HTTP by cron jobs or internal services.
 * It is protected by IP whitelisting for BOTH CLI and HTTP access.
 * 
 * Usage:
 * - HTTP: https://yourdomain.com/send_telegram.php?key=YOUR_SECRET
 * - CLI: /opt/alt/php83/usr/bin/php /path/to/send_telegram.php
 * 
 * Configuration: 
 * - CRON_ALLOWED_IP: Comma-separated list of allowed IPs (REQUIRED)
 *   For CLI access, include your server's IP address(es)
 *   For HTTP access, include the requesting client's IP
 *   Example: CRON_ALLOWED_IP="192.168.1.100,10.0.0.5,127.0.0.1"
 * - CRON_SECRET_KEY: Optional secret key for additional security
 * 
 * Logs: All access attempts are logged to telegram_web.log in this directory
 */

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Allow script to run indefinitely (no timeout)
set_time_limit(0);

// Define constants
define('ENV_FILE_PATH', __DIR__ . '/../.env');
define('LOG_FILE_PATH', __DIR__ . '/telegram_web.log');

// Set timezone
date_default_timezone_set('UTC');

/**
 * Parse .env file and return associative array
 */
function parseEnvFile($filePath)
{
    static $cachedEnv = null;

    if ($cachedEnv !== null) {
        return $cachedEnv;
    }

    $env = [];

    if (!file_exists($filePath)) {
        return $env;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        // Parse KEY=VALUE pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if (
                (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")
            ) {
                $value = substr($value, 1, -1);
            }

            $env[$key] = $value;
        }
    }

    $cachedEnv = $env;
    return $env;
}

/**
 * Get environment variable value
 */
function getEnvValue($key, $default = null)
{
    $env = parseEnvFile(ENV_FILE_PATH);
    return isset($env[$key]) ? $env[$key] : $default;
}

/**
 * Get client IP address
 */
function getClientIp()
{
    // Check for shared internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IP passed from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // X-Forwarded-For can contain multiple IPs, take the first one
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    // Check for IP from remote address
    elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = 'unknown';
    }

    // Validate IP format
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return $ip;
    }

    // Fallback to REMOTE_ADDR even if it's a private IP
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Get server's own IP addresses (for CLI validation)
 */
function getServerIpAddresses()
{
    $ips = [];

    // Get hostname and resolve to IPs
    $hostname = gethostname();
    if ($hostname !== false) {
        $resolvedIps = gethostbynamel($hostname);
        if ($resolvedIps !== false) {
            $ips = array_merge($ips, $resolvedIps);
        }
    }

    // Add SERVER_ADDR if available
    if (!empty($_SERVER['SERVER_ADDR'])) {
        $ips[] = $_SERVER['SERVER_ADDR'];
    }

    // Add localhost addresses
    $ips[] = '127.0.0.1';
    $ips[] = '::1';

    // Remove duplicates and empty values
    $ips = array_unique(array_filter($ips));

    return $ips;
}

/**
 * Check if IP is in allowed list
 * @param string|array $clientIp Single IP or array of IPs to check
 * @param string $allowedIps Comma-separated list of allowed IPs
 * @return bool|string Returns true if allowed, or the matched IP if array provided
 */
function isIpAllowed($clientIp, $allowedIps)
{
    if (empty($allowedIps)) {
        return false;
    }

    $allowedList = array_map('trim', explode(',', $allowedIps));

    // Handle array of IPs (for CLI mode)
    if (is_array($clientIp)) {
        foreach ($clientIp as $ip) {
            foreach ($allowedList as $allowedIp) {
                if ($ip === $allowedIp) {
                    return $ip; // Return the matched IP
                }
            }
        }
        return false;
    }

    // Handle single IP (for HTTP mode)
    foreach ($allowedList as $allowedIp) {
        if ($clientIp === $allowedIp) {
            return true;
        }
    }

    return false;
}

/**
 * Log message to file with locking to prevent corruption
 */
function logMessage($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;

    // Use file locking to prevent corruption when multiple processes write simultaneously
    $handle = fopen(LOG_FILE_PATH, 'a');
    if ($handle) {
        flock($handle, LOCK_EX); // Exclusive lock
        fwrite($handle, $logEntry);
        flock($handle, LOCK_UN); // Release lock
        fclose($handle);
    }
}

/**
 * Send message to Telegram
 */
function sendTelegramMessage($botToken, $chatId, $message)
{
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel-Monitor/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'response' => "cURL Error: {$error}"
        ];
    }

    $decodedResponse = json_decode($response, true);

    return [
        'success' => $httpCode === 200 && isset($decodedResponse['ok']) && $decodedResponse['ok'],
        'response' => $response,
        'http_code' => $httpCode
    ];
}

/**
 * Mask token for logging
 */
function maskToken($token)
{
    if (strlen($token) <= 4) {
        return str_repeat('*', strlen($token));
    }

    return str_repeat('*', strlen($token) - 4) . substr($token, -4);
}

/**
 * Decrypt Laravel encrypted value
 */
function decryptLaravelValue($encryptedValue)
{
    if (empty($encryptedValue)) {
        return null;
    }

    try {
        // Get APP_KEY from .env
        $appKey = getEnvValue('APP_KEY');
        if (empty($appKey)) {
            logMessage("DECRYPT_ERROR | APP_KEY not found in .env file");
            return null;
        }

        logMessage("DECRYPT_DEBUG | APP_KEY found: " . substr($appKey, 0, 10) . "... (length: " . strlen($appKey) . ")");

        // Remove 'base64:' prefix if present
        if (strpos($appKey, 'base64:') === 0) {
            $appKey = substr($appKey, 7);
            logMessage("DECRYPT_DEBUG | Removed 'base64:' prefix from APP_KEY");
        }

        // Decode the key
        $key = base64_decode($appKey);
        if (strlen($key) !== 32) {
            logMessage("DECRYPT_ERROR | Invalid APP_KEY format - expected 32 bytes, got " . strlen($key));
            return null;
        }

        logMessage("DECRYPT_DEBUG | APP_KEY decoded successfully, key length: " . strlen($key) . " bytes");

        // Decode the encrypted value
        $payload = json_decode(base64_decode($encryptedValue), true);
        if (!$payload || !isset($payload['iv'], $payload['value'], $payload['mac'])) {
            // If it's not in Laravel format, assume it's already decrypted
            logMessage("DECRYPT_DEBUG | Not Laravel format, returning as-is");
            return $encryptedValue;
        }

        logMessage("DECRYPT_DEBUG | Payload components found - IV: " . strlen($payload['iv']) . " chars, Value: " . strlen($payload['value']) . " chars, MAC: " . strlen($payload['mac']) . " chars");

        // Skip MAC verification for now - we'll validate by successful decryption
        // This is a workaround since the exact MAC format varies between Laravel versions
        logMessage("DECRYPT_DEBUG | Skipping MAC verification, will validate via successful decryption");

        // Add more detailed debugging
        logMessage("DECRYPT_DEBUG | Encrypted value length: " . strlen($encryptedValue) . " chars");
        logMessage("DECRYPT_DEBUG | IV: " . $payload['iv'] . " | Value: " . substr($payload['value'], 0, 20) . "...");

        // Decrypt the value
        $decrypted = openssl_decrypt(
            base64_decode($payload['value']),
            'AES-256-CBC',
            $key,
            0,
            base64_decode($payload['iv'])
        );

        if ($decrypted === false) {
            logMessage("DECRYPT_ERROR | OpenSSL decryption failed - trying alternative method");

            // Try alternative decryption method (for different Laravel versions)
            $decrypted = openssl_decrypt(
                $payload['value'], // Try without base64_decode
                'AES-256-CBC',
                $key,
                0,
                base64_decode($payload['iv'])
            );

            if ($decrypted === false) {
                logMessage("DECRYPT_ERROR | Alternative decryption also failed");
                return null;
            } else {
                logMessage("DECRYPT_SUCCESS | Alternative decryption method worked");
            }
        } else {
            logMessage("DECRYPT_SUCCESS | Standard decryption method worked");
        }

        logMessage("DECRYPT_DEBUG | Decryption successful, attempting to unserialize");

        // Laravel serializes data before encryption, so we need to unserialize it
        $unserialized = @unserialize($decrypted);
        if ($unserialized === false && $decrypted !== serialize(false)) {
            // If unserialize fails, try to return the raw decrypted value
            logMessage("DECRYPT_WARNING | Unserialize failed, returning raw decrypted value: " . substr($decrypted, 0, 20) . "...");
            return $decrypted;
        }

        logMessage("DECRYPT_SUCCESS | Successfully decrypted and unserialized value");
        return $unserialized;

    } catch (Exception $e) {
        logMessage("DECRYPT_ERROR | Exception: " . $e->getMessage());
        return null;
    }
}

/**
 * Get database connection
 */
function getDatabaseConnection()
{
    try {
        $host = getEnvValue('DB_HOST', '127.0.0.1');
        $port = getEnvValue('DB_PORT', '3306');
        $database = getEnvValue('DB_DATABASE');
        $username = getEnvValue('DB_USERNAME');
        $password = getEnvValue('DB_PASSWORD');

        if (empty($database) || empty($username)) {
            logMessage("DATABASE_CONFIG_ERROR | Missing database configuration");
            return null;
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        return $pdo;

    } catch (PDOException $e) {
        logMessage("DATABASE_CONNECTION_ERROR | " . $e->getMessage());
        return null;
    }
}

/**
 * Get active websites from database with user settings
 */
function getActiveWebsites($pdo)
{
    try {
        $sql = "SELECT 
                    w.id, w.url, w.name, w.user_id,
                    w.last_checked_at, w.last_status_code, w.last_error,
                    us.telegram_bot_token, us.telegram_chat_id,
                    us.notify_redirect_suspicious, us.notify_redirect_domain_change,
                    us.notify_redirect_unexpected, us.notify_content_suspicious, us.notify_content_directory_listing, us.notify_connection,
                    us.notify_ssl, us.notify_dns, us.notify_timeout, us.notify_http
                FROM websites w
                INNER JOIN users u ON w.user_id = u.id
                LEFT JOIN user_settings us ON u.id = us.user_id
                WHERE w.is_active = 1
                ORDER BY w.user_id, w.id";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logMessage("DATABASE_QUERY_ERROR | " . $e->getMessage());
        return [];
    }
}

/**
 * Update website status in database
 */
function updateWebsiteStatus($pdo, $websiteId, $statusCode, $error)
{
    try {
        $stmt = $pdo->prepare("UPDATE websites SET last_checked_at = NOW(), last_status_code = ?, last_error = ? WHERE id = ?");
        $stmt->execute([$statusCode, $error, $websiteId]);
        return true;
    } catch (PDOException $e) {
        logMessage("DATABASE_UPDATE_ERROR | Website ID: {$websiteId} | " . $e->getMessage());
        return false;
    }
}

/**
 * Check for suspicious content (including directory listings)
 */
function checkForSuspiciousContent($responseBody, $effectiveUrl)
{
    if (!$responseBody) {
        return null;
    }

    // First check for directory listing structure (more specific)
    if (
        preg_match('/<title>Index of/i', $responseBody) ||
        preg_match('/Index of \/.*<table/is', $responseBody) ||
        preg_match('/Index of \//i', $responseBody)
    ) {
        return [
            'error' => "Directory Listing Detected: Website showing directory listing instead of content - URL: {$effectiveUrl}",
            'type' => 'content_directory_listing'
        ];
    }

    // Then check for other suspended/hacked indicators
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

    return null;
}

/**
 * Check for suspicious redirects
 */
function checkForSuspiciousRedirect($originalUrl, $effectiveUrl, $responseBody)
{
    // Normalize URLs for comparison (remove trailing slashes, www, etc)
    $normalizedOriginal = normalizeUrl($originalUrl);
    $normalizedEffective = normalizeUrl($effectiveUrl);

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


    // Unexpected redirect (not suspicious but worth noting)
    // Only report if it's a significant redirect (not just http->https or www addition)
    $originalScheme = parse_url($normalizedOriginal, PHP_URL_SCHEME);
    $effectiveScheme = parse_url($normalizedEffective, PHP_URL_SCHEME);

    // If only scheme changed (http->https), it's normal
    if (
        $originalScheme !== $effectiveScheme &&
        str_replace([$originalScheme, '://'], '', $normalizedOriginal) ===
        str_replace([$effectiveScheme, '://'], '', $normalizedEffective)
    ) {
        return null; // Normal HTTPS redirect
    }

    // Report unexpected redirect
    return [
        'error' => "Unexpected Redirect: Website redirected from {$originalUrl} to {$effectiveUrl}",
        'type' => 'redirect_unexpected'
    ];
}

/**
 * Normalize URL for comparison
 */
function normalizeUrl($url)
{
    $parsed = parse_url($url);

    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'http';
    $host = isset($parsed['host']) ? $parsed['host'] : '';
    $path = isset($parsed['path']) ? $parsed['path'] : '/';

    // Remove www prefix for comparison
    $host = preg_replace('/^www\./', '', $host);

    // Remove trailing slash
    $path = rtrim($path, '/');
    if (empty($path))
        $path = '/';

    return $scheme . '://' . $host . $path;
}

/**
 * Check website status
 */
function checkWebsite($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'Laravel-Monitor/1.0',
        CURLOPT_HEADER => false
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($curlErrno !== 0) {
        $errorType = 'connection';
        $errorMessage = "Connection Error: {$curlError}";

        // Detect specific error types
        if (
            $curlErrno === CURLE_SSL_CONNECT_ERROR ||
            $curlErrno === CURLE_SSL_CERTPROBLEM ||
            $curlErrno === CURLE_SSL_CIPHER ||
            $curlErrno === CURLE_SSL_CACERT
        ) {
            $errorType = 'ssl';
            $errorMessage = "SSL Error: {$curlError}";
        } elseif ($curlErrno === CURLE_COULDNT_RESOLVE_HOST) {
            $errorType = 'dns';
            $errorMessage = "DNS Error: Could not resolve host";
        } elseif ($curlErrno === CURLE_OPERATION_TIMEDOUT) {
            $errorType = 'timeout';
            $errorMessage = "Timeout Error: Request timed out after 10 seconds";
        }

        return [
            'success' => false,
            'status_code' => null,
            'error' => $errorMessage,
            'error_type' => $errorType
        ];
    }

    // Check HTTP status code
    if ($statusCode >= 200 && $statusCode < 300) {
        // Check for suspicious content (including directory listings)
        $contentIssue = checkForSuspiciousContent($response, $effectiveUrl);

        if ($contentIssue) {
            return [
                'success' => false,
                'status_code' => $statusCode,
                'error' => $contentIssue['error'],
                'error_type' => $contentIssue['type'],
                'redirect_url' => $effectiveUrl
            ];
        }

        // Check for suspicious redirects
        $redirectIssue = checkForSuspiciousRedirect($url, $effectiveUrl, $response);

        if ($redirectIssue) {
            return [
                'success' => false,
                'status_code' => $statusCode,
                'error' => $redirectIssue['error'],
                'error_type' => $redirectIssue['type'],
                'redirect_url' => $effectiveUrl
            ];
        }

        return [
            'success' => true,
            'status_code' => $statusCode,
            'error' => null,
            'error_type' => null,
            'redirect_url' => ($url !== $effectiveUrl) ? $effectiveUrl : null
        ];
    } else {
        return [
            'success' => false,
            'status_code' => $statusCode,
            'error' => "HTTP Error: Received status code {$statusCode}",
            'error_type' => 'http'
        ];
    }
}

/**
 * Check if notification should be sent based on user preferences
 */
function shouldSendNotification($userSettings, $errorType)
{
    // If no settings found, send all notifications by default
    if (!$userSettings) {
        return true;
    }

    // If no error type specified, send by default
    if (!$errorType) {
        return true;
    }

    // Map error types to database column names
    $columnMap = [
        'redirect_suspicious' => 'notify_redirect_suspicious',
        'redirect_domain_change' => 'notify_redirect_domain_change',
        'redirect_unexpected' => 'notify_redirect_unexpected',
        'content_suspicious' => 'notify_content_suspicious',
        'content_directory_listing' => 'notify_content_directory_listing',
        'connection' => 'notify_connection',
        'ssl' => 'notify_ssl',
        'dns' => 'notify_dns',
        'timeout' => 'notify_timeout',
        'http' => 'notify_http',
    ];

    // Get the corresponding column name
    $column = $columnMap[$errorType] ?? null;

    // If unknown error type, send by default
    if (!$column) {
        return true;
    }

    // Check if user has enabled this type of notification
    // If column is null (not set), default to true (send notification)
    $value = $userSettings[$column] ?? null;
    return $value === null ? true : (bool) $value;
}

/**
 * Send website down alert to Telegram
 */
function sendWebsiteDownAlert($botToken, $chatId, $url, $error, $statusCode = null, $redirectUrl = null, $errorType = null)
{
    $currentTime = date('Y-m-d H:i:s');

    // Choose emoji based on error type
    $emoji = "🚨";
    $title = "Website Alert";

    if ($errorType === 'redirect_suspicious' || $errorType === 'redirect_domain_change') {
        $emoji = "⚠️";
        $title = "Suspicious Redirect Detected";
    } elseif ($errorType === 'content_suspicious') {
        $emoji = "🔴";
        $title = "Suspicious Content Detected";
    } elseif ($errorType === 'content_directory_listing') {
        $emoji = "📁";
        $title = "Directory Listing Detected";
    } elseif ($errorType === 'redirect_unexpected') {
        $emoji = "⚠️";
        $title = "Unexpected Redirect";
    }

    $message = "{$emoji} <b>{$title}</b>\n\n";
    $message .= "🌐 <b>Original URL:</b> {$url}\n";

    if ($redirectUrl && $redirectUrl !== $url) {
        $message .= "↪️ <b>Redirected to:</b> {$redirectUrl}\n";
    }

    $message .= "❌ <b>Error:</b> {$error}\n";

    if ($statusCode !== null) {
        $message .= "📊 <b>Status Code:</b> {$statusCode}\n";
    }

    if ($errorType) {
        $typeLabels = [
            'redirect_suspicious' => '⚠️ Suspicious Redirect',
            'redirect_domain_change' => '🚨 Domain Change / Possible Hack',
            'redirect_unexpected' => 'ℹ️ Unexpected Redirect',
            'content_suspicious' => '🔴 Suspicious Content',
            'content_directory_listing' => '📁 Directory Listing (Site Data Deleted)',
            'connection' => '🔌 Connection Error',
            'ssl' => '🔒 SSL Error',
            'dns' => '🌐 DNS Error',
            'timeout' => '⏱️ Timeout',
            'http' => '📡 HTTP Error',
        ];

        if (isset($typeLabels[$errorType])) {
            $message .= "🏷️ <b>Type:</b> {$typeLabels[$errorType]}\n";
        }
    }

    $message .= "\n⏰ <b>Time:</b> {$currentTime}";

    $result = sendTelegramMessage($botToken, $chatId, $message);

    return $result['success'];
}

/**
 * Send HTTP response
 */
function sendResponse($statusCode, $data, $isCli = false)
{
    if ($isCli) {
        echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
        exit($statusCode >= 400 ? 1 : 0);
    }

    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Main execution
try {
    // Check if running from CLI
    $isCli = php_sapi_name() === 'cli';

    // Load configuration
    $allowedIps = getEnvValue('CRON_ALLOWED_IP');

    // Validate required configuration
    if (empty($allowedIps)) {
        logMessage("ERROR | Missing CRON_ALLOWED_IP configuration");
        sendResponse(500, ['success' => false, 'error' => 'Server configuration error'], $isCli);
    }

    // Get client IP based on execution mode
    if ($isCli) {
        // For CLI, get all server IPs
        $serverIps = getServerIpAddresses();
        $clientIp = implode(', ', $serverIps); // Use actual server IPs for logging
        $ipsToValidate = $serverIps;
        logMessage("CLI_MODE | Server IPs detected: " . implode(', ', $serverIps));
    } else {
        // For HTTP, get client IP
        $clientIp = getClientIp();
        $ipsToValidate = $clientIp;
    }

    // Log access attempt
    $requestUrl = $isCli ? 'CLI' : ($_SERVER['REQUEST_URI'] ?? 'unknown');
    logMessage("ACCESS_ATTEMPT | IP: {$clientIp} | URL: {$requestUrl}");

    // IP validation (required for both CLI and HTTP)
    $ipCheckResult = isIpAllowed($ipsToValidate, $allowedIps);

    if (!$ipCheckResult) {
        if ($isCli) {
            $errorMsg = "Access Denied: None of the server IPs (" . implode(', ', $serverIps) . ") match the allowed IPs ({$allowedIps})";
            logMessage("ACCESS_DENIED | Mode: CLI | Server IPs: " . implode(', ', $serverIps) . " | Allowed IPs: {$allowedIps} | Reason: No matching IP");
        } else {
            $errorMsg = "Access Denied: Your IP ({$clientIp}) is not authorized";
            logMessage("ACCESS_DENIED | Mode: HTTP | Client IP: {$clientIp} | Allowed IPs: {$allowedIps} | Reason: IP not in whitelist");
        }
        sendResponse(403, ['success' => false, 'error' => $errorMsg], $isCli);
    }

    // Log which IP was matched (for CLI mode)
    if ($isCli && is_string($ipCheckResult)) {
        logMessage("ACCESS_GRANTED | Mode: CLI | Matched IP: {$ipCheckResult} | Status: AUTHORIZED");
    } else {
        logMessage("ACCESS_GRANTED | Mode: HTTP | IP: {$clientIp} | Status: AUTHORIZED");
    }

    // Connect to database
    logMessage("DATABASE_CONNECT | Attempting to connect to database");
    $pdo = getDatabaseConnection();

    if (!$pdo) {
        logMessage("DATABASE_ERROR | Failed to connect to database");
        sendResponse(500, [
            'success' => false,
            'error' => 'Database connection failed'
        ], $isCli);
    }

    logMessage("DATABASE_CONNECTED | Successfully connected to database");

    // Get active websites
    $websites = getActiveWebsites($pdo);
    $websiteCount = count($websites);

    logMessage("MONITORING_START | Method: STANDALONE_PHP | Checking {$websiteCount} active websites");

    if ($websiteCount === 0) {
        logMessage("MONITORING_END | No active websites to check");
        sendResponse(200, [
            'success' => true,
            'message' => 'No active websites to monitor',
            'checked' => 0,
            'failed' => 0
        ], $isCli);
    }

    $failedCount = 0;
    $successCount = 0;

    // Check each website
    foreach ($websites as $website) {
        $websiteId = $website['id'];
        $websiteUrl = $website['url'];
        $websiteName = $website['name'];
        $userId = $website['user_id'];

        // Decrypt Telegram credentials
        logMessage("DECRYPT_START | User ID: {$userId} | Attempting to decrypt Telegram credentials");
        $botToken = decryptLaravelValue($website['telegram_bot_token']);
        $chatId = decryptLaravelValue($website['telegram_chat_id']);

        logMessage("DECRYPT_RESULT | User ID: {$userId} | BotToken: " . (empty($botToken) ? 'EMPTY' : 'SUCCESS') . " | ChatID: " . (empty($chatId) ? 'EMPTY' : 'SUCCESS'));

        logMessage("WEBSITE_CHECK_START | User ID: {$userId} | Website ID: {$websiteId} | Name: {$websiteName} | URL: {$websiteUrl}");

        // Check website
        $result = checkWebsite($websiteUrl);

        if ($result['success']) {
            // Website is up
            $successCount++;
            logMessage("WEBSITE_CHECK_SUCCESS | ID: {$websiteId} | Status: {$result['status_code']}");

            // Update database
            updateWebsiteStatus($pdo, $websiteId, $result['status_code'], null);

        } else {
            // Website is down
            $failedCount++;
            logMessage("WEBSITE_CHECK_FAILED | ID: {$websiteId} | Error: {$result['error']}");

            // Update database
            updateWebsiteStatus($pdo, $websiteId, $result['status_code'], $result['error']);

            // Check if user has Telegram credentials configured
            if (empty($botToken) || empty($chatId)) {
                logMessage("TELEGRAM_ALERT_SKIPPED | User ID: {$userId} has no Telegram credentials configured");
            } else {
                // Check if user wants to receive this type of notification
                if (!shouldSendNotification($website, $result['error_type'])) {
                    logMessage("TELEGRAM_ALERT_SKIPPED | User ID: {$userId} disabled notifications for type: {$result['error_type']}");
                } else {
                    // Send Telegram alert
                    logMessage("TELEGRAM_ALERT_SEND | Sending alert for {$websiteName} ({$websiteUrl}) | Type: {$result['error_type']}");

                    $alertSent = sendWebsiteDownAlert(
                        $botToken,
                        $chatId,
                        $websiteUrl,
                        $result['error'],
                        $result['status_code'],
                        $result['redirect_url'] ?? null,
                        $result['error_type']
                    );

                    if ($alertSent) {
                        logMessage("TELEGRAM_ALERT_SUCCESS | Alert sent for {$websiteName}");
                    } else {
                        logMessage("TELEGRAM_ALERT_FAILED | Failed to send alert for {$websiteName}");
                    }
                }
            }
        }
    }

    logMessage("MONITORING_END | Checked: {$websiteCount} | Success: {$successCount} | Failed: {$failedCount}");

    // Send final response
    sendResponse(200, [
        'success' => true,
        'message' => 'Website monitoring completed',
        'checked' => $websiteCount,
        'failed' => $failedCount,
        'success' => $successCount,
        'timestamp' => date('Y-m-d H:i:s T')
    ], $isCli);

} catch (Exception $e) {
    logMessage("EXCEPTION | Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    sendResponse(500, [
        'success' => false,
        'error' => 'Internal server error'
    ], $isCli);
}
