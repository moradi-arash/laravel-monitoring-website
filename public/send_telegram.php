<?php
/**
 * Standalone Telegram Message Sender
 * 
 * This script can be called via HTTP by cron jobs or internal services.
 * It is protected by IP whitelisting and optional secret key validation.
 * 
 * Usage:
 * - HTTP: https://yourdomain.com/send_telegram.php?key=YOUR_SECRET
 * - CLI: php send_telegram.php
 * 
 * Configuration: Set CRON_ALLOWED_IP and CRON_SECRET_KEY in .env file
 * Logs: All access attempts are logged to telegram_web.log in this directory
 */

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define constants
define('ENV_FILE_PATH', __DIR__ . '/../.env');
define('LOG_FILE_PATH', __DIR__ . '/telegram_web.log');

// Set timezone
date_default_timezone_set('UTC');

/**
 * Parse .env file and return associative array
 */
function parseEnvFile($filePath) {
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
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
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
function getEnvValue($key, $default = null) {
    $env = parseEnvFile(ENV_FILE_PATH);
    return isset($env[$key]) ? $env[$key] : $default;
}

/**
 * Get client IP address
 */
function getClientIp() {
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
 * Check if IP is in allowed list
 */
function isIpAllowed($clientIp, $allowedIps) {
    if (empty($allowedIps)) {
        return false;
    }
    
    $allowedList = array_map('trim', explode(',', $allowedIps));
    
    foreach ($allowedList as $allowedIp) {
        if ($clientIp === $allowedIp) {
            return true;
        }
    }
    
    return false;
}

/**
 * Log message to file
 */
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;
    
    file_put_contents(LOG_FILE_PATH, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Send message to Telegram
 */
function sendTelegramMessage($botToken, $chatId, $message) {
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
function maskToken($token) {
    if (strlen($token) <= 4) {
        return str_repeat('*', strlen($token));
    }
    
    return str_repeat('*', strlen($token) - 4) . substr($token, -4);
}

/**
 * Get database connection
 */
function getDatabaseConnection() {
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
 * Get active websites from database
 */
function getActiveWebsites($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, url, name, last_checked_at, last_status_code, last_error FROM websites WHERE is_active = 1");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logMessage("DATABASE_QUERY_ERROR | " . $e->getMessage());
        return [];
    }
}

/**
 * Update website status in database
 */
function updateWebsiteStatus($pdo, $websiteId, $statusCode, $error) {
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
 * Check website status
 */
function checkWebsite($url) {
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
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($curlErrno !== 0) {
        $errorType = 'connection';
        $errorMessage = "Connection Error: {$curlError}";
        
        // Detect specific error types
        if ($curlErrno === CURLE_SSL_CONNECT_ERROR || 
            $curlErrno === CURLE_SSL_CERTPROBLEM || 
            $curlErrno === CURLE_SSL_CIPHER ||
            $curlErrno === CURLE_SSL_CACERT) {
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
        return [
            'success' => true,
            'status_code' => $statusCode,
            'error' => null,
            'error_type' => null
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
 * Send website down alert to Telegram
 */
function sendWebsiteDownAlert($botToken, $chatId, $url, $error, $statusCode = null) {
    $currentTime = date('Y-m-d H:i:s');
    
    $message = "🚨 <b>Website Down Alert</b>\n\n";
    $message .= "🌐 <b>Website:</b> {$url}\n";
    $message .= "❌ <b>Error:</b> {$error}\n";
    
    if ($statusCode !== null) {
        $message .= "📊 <b>Status Code:</b> {$statusCode}\n";
    }
    
    $message .= "\n⏰ <b>Time:</b> {$currentTime}";
    
    $result = sendTelegramMessage($botToken, $chatId, $message);
    
    return $result['success'];
}

/**
 * Send HTTP response
 */
function sendResponse($statusCode, $data, $isCli = false) {
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
    $secretKey = getEnvValue('CRON_SECRET_KEY');
    $botToken = getEnvValue('TELEGRAM_BOT_TOKEN');
    $chatId = getEnvValue('TELEGRAM_CHAT_ID');
    
    // Validate required configuration
    if (empty($allowedIps)) {
        logMessage("ERROR | Missing CRON_ALLOWED_IP configuration");
        sendResponse(500, ['success' => false, 'error' => 'Server configuration error'], $isCli);
    }
    
    if (empty($botToken)) {
        logMessage("ERROR | Missing TELEGRAM_BOT_TOKEN configuration");
        sendResponse(500, ['success' => false, 'error' => 'Server configuration error'], $isCli);
    }
    
    if (empty($chatId)) {
        logMessage("ERROR | Missing TELEGRAM_CHAT_ID configuration");
        sendResponse(500, ['success' => false, 'error' => 'Server configuration error'], $isCli);
    }
    
    // Get client IP
    $clientIp = $isCli ? 'CLI' : getClientIp();
    
    // Log access attempt
    $requestUrl = $isCli ? 'CLI' : ($_SERVER['REQUEST_URI'] ?? 'unknown');
    logMessage("ACCESS_ATTEMPT | IP: {$clientIp} | URL: {$requestUrl}");
    
    // IP validation (skip for CLI)
    if (!$isCli) {
        if (!isIpAllowed($clientIp, $allowedIps)) {
            logMessage("ACCESS_DENIED | IP: {$clientIp} | Reason: IP not in whitelist");
            sendResponse(403, ['success' => false, 'error' => 'Access Denied: IP not authorized'], $isCli);
        }
    }
    
    // Secret key validation (if configured)
    if (!empty($secretKey)) {
        $providedKey = $isCli ? ($argv[1] ?? '') : ($_GET['key'] ?? '');
        
        if ($providedKey !== $secretKey) {
            $reason = empty($providedKey) ? 'Missing secret key' : 'Invalid secret key';
            logMessage("ACCESS_DENIED | IP: {$clientIp} | Reason: {$reason}");
            sendResponse(403, ['success' => false, 'error' => 'Access Denied: Invalid or missing secret key'], $isCli);
        }
    }
    
    // Log authorized access
    logMessage("ACCESS_GRANTED | IP: {$clientIp} | Status: AUTHORIZED");
    
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

    logMessage("MONITORING_START | Checking {$websiteCount} active websites");

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
        
        logMessage("WEBSITE_CHECK_START | ID: {$websiteId} | Name: {$websiteName} | URL: {$websiteUrl}");
        
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
            
            // Send Telegram alert
            logMessage("TELEGRAM_ALERT_SEND | Sending alert for {$websiteName} ({$websiteUrl})");
            
            $alertSent = sendWebsiteDownAlert(
                $botToken,
                $chatId,
                $websiteUrl,
                $result['error'],
                $result['status_code']
            );
            
            if ($alertSent) {
                logMessage("TELEGRAM_ALERT_SUCCESS | Alert sent for {$websiteName}");
            } else {
                logMessage("TELEGRAM_ALERT_FAILED | Failed to send alert for {$websiteName}");
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
