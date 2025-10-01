# Laravel Website Monitoring Tool

A comprehensive Laravel-based website monitoring solution that provides real-time uptime monitoring, error detection, and instant Telegram alerts. This tool continuously monitors your websites for availability, SSL certificate status, DNS resolution, and various error conditions, sending immediate notifications when issues are detected.

## 🚀 Features

- **Real-time Website Monitoring**: Continuous uptime monitoring with configurable check intervals
- **Instant Telegram Alerts**: Immediate notifications via Telegram Bot when issues are detected
- **Comprehensive Error Detection**: Monitors SSL certificates, DNS resolution, timeouts, and HTTP errors
- **Admin Dashboard**: Web-based interface for managing monitored websites
- **Bulk Website Import**: Add multiple websites at once via CSV upload
- **Scheduled Monitoring**: Uses Laravel's task scheduler for reliable background monitoring
- **Detailed Logging**: Complete history of all monitoring activities and alerts
- **Security Focused**: Secure authentication and data protection

## 🔍 Error Types Detected

| Error Type | Description | Detection Method |
|------------|-------------|------------------|
| **SSL Certificate Issues** | Expired, invalid, or self-signed certificates | cURL SSL verification |
| **DNS Resolution Errors** | Domain name cannot be resolved | DNS lookup failure |
| **Connection Timeouts** | Server doesn't respond within timeout period | cURL timeout |
| **Connection Refused** | Server actively refuses connection | Network connection failure |
| **HTTP 4xx Errors** | Client errors (404, 403, 401, etc.) | HTTP status code analysis |
| **HTTP 5xx Errors** | Server errors (500, 502, 503, 504, etc.) | HTTP status code analysis |
| **Redirect Loops** | Infinite redirect chains | Redirect limit exceeded |
| **Empty Response** | Server returns empty content | Response body validation |

## 🔄 Monitoring Methods

This application provides **two methods** for monitoring websites:

### Method 1: Laravel Artisan Command (Recommended)
- Uses Laravel's built-in task scheduler
- Requires Laravel environment
- Better integration with Laravel features
- Easier debugging with Laravel logs
- Command: `php artisan monitor:websites`

### Method 2: Standalone PHP Script
- Independent PHP script (`public/send_telegram.php`)
- Can be called via HTTP or CLI
- No Laravel bootstrap required (faster execution)
- Ideal for external cron services
- Includes IP whitelisting and secret key security
- URL: `https://yourdomain.com/send_telegram.php?key=YOUR_SECRET`

### Comparison Table

| Feature | Laravel Artisan | Standalone Script |
|---------|----------------|-------------------|
| **Laravel Required** | Yes | No (standalone) |
| **Execution Speed** | Slower (full bootstrap) | Faster (minimal overhead) |
| **Security** | Laravel auth | IP whitelist + secret key |
| **Logging** | Laravel logs | Custom log file |
| **Debugging** | Laravel tools | Custom logging |
| **External Cron** | Requires server access | HTTP accessible |
| **Best For** | Server-based cron | External cron services |

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 12.x or higher
- MySQL/SQLite database
- cURL extension enabled
- OpenSSL extension enabled
- Telegram Bot API access

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/moradi-arash/laravel-monitoring-website.git
cd laravel-monitoring-website
```

### 2. Install Dependencies

```bash
composer install
npm install && npm run build
```

### 3. Environment Configuration

Copy the environment file and configure your settings:

```bash
cp .env.example .env
```

Update the `.env` file with your configuration:

```env
APP_NAME="Website Monitor"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=website_monitor
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Database Migrations

```bash
php artisan migrate
```

### 6. Create Admin User

Run the database seeder to create the default admin user:

```bash
php artisan db:seed
```

This will create an admin user with the following credentials:
- **Email**: admin@example.com
- **Password**: password

**Important**: Change these default credentials immediately after first login for security.

Alternatively, you can create a custom admin user using Laravel Tinker:

```bash
php artisan tinker
```

Then run:
```php
User::create([
    'name' => 'Your Name',
    'email' => 'your-email@example.com',
    'password' => bcrypt('your-secure-password'),
]);
```

## 🤖 Telegram Bot Setup

### Step 1: Create a Telegram Bot

1. Open Telegram and search for `@BotFather`
2. Start a conversation with BotFather
3. Send `/newbot` command
4. Follow the prompts to:
   - Choose a name for your bot (e.g., "Website Monitor Bot")
   - Choose a username (e.g., "website_monitor_bot")
5. BotFather will provide you with a **Bot Token** - save this securely

### Step 2: Get Your Chat ID

#### Method 1: Using @userinfobot
1. Search for `@userinfobot` on Telegram
2. Start a conversation and send any message
3. The bot will reply with your Chat ID

#### Method 2: Using Telegram API
1. Send a message to your bot
2. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
3. Look for the `chat.id` in the response

#### Method 3: Using @getidsbot
1. Search for `@getidsbot` on Telegram
2. Forward any message from your bot to this bot
3. It will provide you with the Chat ID

### Step 3: Configure Your Bot

1. Add the Bot Token and Chat ID to your `.env` file:
```env
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=123456789
```

2. Test the integration:
```bash
# Test using the standalone script
php public/send_telegram.php

# Or test using Laravel Tinker
php artisan tinker
```
Then run:
```php
$telegram = app(\App\Services\TelegramService::class);
$telegram->sendMessage('🔔 Test message from Laravel Website Monitor');
```

### Step 4: Set Up Bot Commands (Optional)

You can set up custom commands for your bot:

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setMyCommands" \
-H "Content-Type: application/json" \
-d '{
  "commands": [
    {"command": "status", "description": "Check monitoring status"},
    {"command": "websites", "description": "List monitored websites"},
    {"command": "help", "description": "Show available commands"}
  ]
}'
```

## ⚙️ Configuration

### Monitoring Settings

Configure monitoring intervals and timeouts in your `.env` file:

```env
# Monitoring Configuration
MONITORING_INTERVAL=5
MONITORING_TIMEOUT=30
MONITORING_RETRY_ATTEMPTS=3
MONITORING_RETRY_DELAY=60
```

### Alert Settings

```env
# Alert Configuration
ALERT_ENABLED=true
ALERT_RETRY_ATTEMPTS=3
ALERT_RETRY_DELAY=300
ALERT_COOLDOWN=3600
```

### Cron Security Settings (for Standalone Script)

```env
# IP address(es) allowed to access send_telegram.php
# Single IP: CRON_ALLOWED_IP=1.2.3.4
# Multiple IPs: CRON_ALLOWED_IP=1.2.3.4,5.6.7.8
CRON_ALLOWED_IP=127.0.0.1

# Optional secret key for additional security
# Generate with: openssl rand -hex 32
CRON_SECRET_KEY=
```

### Database Configuration

For production, use MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=website_monitor
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 🚀 Usage

### 1. Access the Admin Dashboard

Visit `https://your-domain.com` and log in with your admin credentials.

### 2. Add Websites to Monitor

- **Single Website**: Use the "Add Website" form
- **Bulk Import**: Upload a CSV file with website URLs
- **API Integration**: Use the REST API endpoints

### 3. Monitor Status

- View real-time status of all monitored websites
- Check detailed logs and error history
- Configure alert settings per website

### 4. Telegram Alerts

The system will automatically send alerts when:
- A website goes down
- SSL certificate expires
- DNS resolution fails
- HTTP errors occur
- Connection timeouts happen

## 📅 Scheduled Monitoring Setup

### Method 1: Laravel Scheduler (Recommended for Server-Based Cron)

The Laravel scheduler runs the monitoring command every 10 minutes.

**Step 1: Add Cron Entry**

Add this single cron entry to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Step 2: Verify Scheduler**

Check that the monitoring command is scheduled:

```bash
php artisan schedule:list
```

You should see:
```
0/10 * * * * php artisan monitor:websites
```

**Step 3: Test Manually**

```bash
# Run the monitoring command manually
php artisan monitor:websites

# Run the scheduler in foreground (for testing)
php artisan schedule:work
```

**Advantages:**
- Single cron entry manages all scheduled tasks
- Better integration with Laravel
- Easier debugging
- Access to Laravel features

---

### Method 2: Standalone Script (Recommended for External Cron Services)

The standalone script (`public/send_telegram.php`) can be called via HTTP or CLI without Laravel bootstrap.

#### Configuration

**Step 1: Configure Security Settings**

Add these to your `.env` file:

```env
# IP address(es) allowed to access the script
# Single IP: CRON_ALLOWED_IP=1.2.3.4
# Multiple IPs: CRON_ALLOWED_IP=1.2.3.4,5.6.7.8,10.0.0.1
CRON_ALLOWED_IP=127.0.0.1

# Optional secret key for additional security
# Generate: openssl rand -hex 32
CRON_SECRET_KEY=your_secret_key_here
```

**Step 2: Get Your Server IP**

Find your server's public IP address:

```bash
# Linux/Mac
curl ifconfig.me

# Or
wget -qO- ifconfig.me
```

Add this IP to `CRON_ALLOWED_IP` in your `.env` file.

**Step 3: Generate Secret Key**

```bash
openssl rand -hex 32
```

Add the generated key to `CRON_SECRET_KEY` in your `.env` file.

#### Usage Options

**Option A: CLI Execution**

Run directly from command line:

```bash
# Without secret key
php public/send_telegram.php

# With secret key (if configured)
php public/send_telegram.php YOUR_SECRET_KEY
```

**Option B: HTTP Execution**

Call via HTTP (requires IP whitelisting):

```bash
# Using curl
curl https://yourdomain.com/send_telegram.php?key=YOUR_SECRET_KEY

# Using wget
wget -qO- https://yourdomain.com/send_telegram.php?key=YOUR_SECRET_KEY
```

#### Cron Setup Examples

**Example 1: Local Server Cron (CLI)**

```bash
# Check every 10 minutes
*/10 * * * * /usr/bin/php /path-to-your-project/public/send_telegram.php >> /dev/null 2>&1

# Check every 5 minutes
*/5 * * * * /usr/bin/php /path-to-your-project/public/send_telegram.php >> /dev/null 2>&1
```

**Example 2: External Cron Service (HTTP)**

For services like cron-job.org, EasyCron, or similar:

```
URL: https://yourdomain.com/send_telegram.php?key=YOUR_SECRET_KEY
Interval: Every 10 minutes
Method: GET
```

**Example 3: cPanel Cron Job**

```bash
*/10 * * * * /usr/local/bin/php /home/username/public_html/send_telegram.php
```

**Example 4: Using curl in Cron**

```bash
*/10 * * * * /usr/bin/curl -s https://yourdomain.com/send_telegram.php?key=YOUR_SECRET_KEY >> /dev/null 2>&1
```

#### Security Features

**IP Whitelisting:**
- Only specified IPs can access the script
- Supports single or multiple IPs (comma-separated)
- Automatically bypassed when running via CLI
- All unauthorized attempts are logged

**Secret Key Validation:**
- Optional additional security layer
- Required in URL: `?key=YOUR_SECRET`
- Use strong random keys (32+ characters)
- Generate with: `openssl rand -hex 32`

**Logging:**
- All access attempts logged to `public/telegram_web.log`
- Includes: timestamp, IP, action, result
- Unauthorized attempts are recorded
- Monitoring results are logged

#### Log File Location

```
public/telegram_web.log
```

**Log Format:**
```
[2025-01-15 10:30:45] ACCESS_ATTEMPT | IP: 1.2.3.4 | URL: /send_telegram.php?key=***
[2025-01-15 10:30:45] ACCESS_GRANTED | IP: 1.2.3.4 | Status: AUTHORIZED
[2025-01-15 10:30:45] DATABASE_CONNECT | Attempting to connect to database
[2025-01-15 10:30:45] DATABASE_CONNECTED | Successfully connected to database
[2025-01-15 10:30:45] MONITORING_START | Checking 5 active websites
[2025-01-15 10:30:46] WEBSITE_CHECK_START | ID: 1 | Name: Google | URL: https://www.google.com
[2025-01-15 10:30:46] WEBSITE_CHECK_SUCCESS | ID: 1 | Status: 200
[2025-01-15 10:30:47] WEBSITE_CHECK_START | ID: 2 | Name: Example | URL: https://example.com
[2025-01-15 10:30:47] WEBSITE_CHECK_FAILED | ID: 2 | Error: Connection Error
[2025-01-15 10:30:47] TELEGRAM_ALERT_SEND | Sending alert for Example (https://example.com)
[2025-01-15 10:30:48] TELEGRAM_ALERT_SUCCESS | Alert sent for Example
[2025-01-15 10:30:50] MONITORING_END | Checked: 5 | Success: 4 | Failed: 1
```

#### Testing the Standalone Script

**Test 1: CLI Execution**

```bash
php public/send_telegram.php
```

Expected output:
```json
{
    "success": true,
    "message": "Website monitoring completed",
    "checked": 5,
    "failed": 1,
    "success": 4,
    "timestamp": "2025-01-15 10:30:50 UTC"
}
```

**Test 2: HTTP Execution**

```bash
curl https://yourdomain.com/send_telegram.php?key=YOUR_SECRET_KEY
```

**Test 3: Check Logs**

```bash
tail -f public/telegram_web.log
```

#### Troubleshooting

**Issue: 403 Forbidden (IP not authorized)**

Solution:
```bash
# Check your server's IP
curl ifconfig.me

# Add it to .env
CRON_ALLOWED_IP=YOUR_SERVER_IP
```

**Issue: 403 Forbidden (Invalid secret key)**

Solution:
```bash
# Verify the key in .env matches the URL parameter
# Or remove CRON_SECRET_KEY from .env to disable this check
```

**Issue: Database connection failed**

Solution:
```bash
# Verify database credentials in .env
# Check MySQL/MariaDB is running
# Check logs: tail -f public/telegram_web.log
```

**Issue: No websites checked**

Solution:
```bash
# Verify websites are marked as active in database
# Check: SELECT * FROM websites WHERE is_active = 1;
```

---

### Which Method Should You Use?

**Use Laravel Scheduler (Method 1) if:**
- You have direct server access
- You want better Laravel integration
- You need advanced debugging tools
- You're running other Laravel scheduled tasks

**Use Standalone Script (Method 2) if:**
- You're using external cron services (cron-job.org, etc.)
- You want faster execution (no Laravel bootstrap)
- You need HTTP-accessible monitoring
- You want independent monitoring (separate from Laravel)
- You need IP-based security

**Best Practice:**
Use **both methods** for redundancy:
- Primary: Laravel Scheduler (every 10 minutes)
- Backup: Standalone Script via external cron (every 15 minutes)

This ensures monitoring continues even if one method fails.

## 📱 Telegram Alert Format

### Website Down Alert
```
🚨 WEBSITE DOWN ALERT
━━━━━━━━━━━━━━━━━━━━
🌐 Website: https://example.com
⏰ Time: 2025-01-15 14:30:25 UTC
❌ Status: Connection Timeout
🔄 Attempt: 1/3
📊 Response Time: N/A
```

### Website Recovery Alert
```
✅ WEBSITE RECOVERED
━━━━━━━━━━━━━━━━━━━━
🌐 Website: https://example.com
⏰ Time: 2025-01-15 14:35:42 UTC
✅ Status: OK
📊 Response Time: 1.2s
🔄 Total Downtime: 5m 17s
```

### SSL Certificate Alert
```
⚠️ SSL CERTIFICATE WARNING
━━━━━━━━━━━━━━━━━━━━
🌐 Website: https://example.com
⏰ Time: 2025-01-15 14:30:25 UTC
🔒 Certificate: Expires in 7 days
📅 Expiry Date: 2025-01-22 12:00:00 UTC
```

## 📁 Project Structure

```
laravel-monitoring-website/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Http/Controllers/          # Web controllers
│   ├── Models/                    # Eloquent models
│   └── Services/                  # Business logic services
├── config/                        # Configuration files
├── database/
│   ├── migrations/                # Database migrations
│   └── seeders/                   # Database seeders
├── public/
│   ├── send_telegram.php         # ⭐ Standalone monitoring script (HTTP/CLI)
│   ├── telegram_web.log          # Standalone script logs
│   └── build/                     # Compiled assets
├── resources/
│   ├── views/                     # Blade templates
│   └── js/                        # JavaScript files
├── routes/                        # Route definitions
└── storage/
    └── logs/                      # Application logs
```

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Telegram Integration

```bash
# Test using the standalone script
php public/send_telegram.php

# Test using Laravel Tinker
php artisan tinker
```
Then run:
```php
$telegram = app(\App\Services\TelegramService::class);
$telegram->sendMessage('🔔 Test message from Laravel Website Monitor');
```

### Test Website Monitoring

```bash
# Run the monitoring command
php artisan monitor:websites
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Telegram Bot Not Responding
- Verify Bot Token is correct
- Check Chat ID is valid
- Ensure bot is not blocked
- Test with: `php public/send_telegram.php`

#### 2. Websites Not Being Monitored
- Check Laravel scheduler is running
- Verify cron job is set up correctly
- Check application logs: `tail -f storage/logs/laravel.log`

#### 3. Database Connection Issues
- Verify database credentials in `.env`
- Check database server is running
- Run migrations: `php artisan migrate`

#### 4. SSL Certificate Warnings
- Update cURL certificates
- Check OpenSSL extension is enabled
- Verify system time is correct

#### 5. Standalone Script Issues

**Problem: Script returns 403 Forbidden**
- Check `CRON_ALLOWED_IP` in `.env` includes your IP
- Verify `CRON_SECRET_KEY` matches URL parameter
- Check logs: `tail -f public/telegram_web.log`
- For CLI execution, IP checking is automatically bypassed

**Problem: Script not executing via cron**
- Verify cron job syntax is correct
- Check cron service is running: `systemctl status cron`
- Test manually first: `php public/send_telegram.php`
- Check cron logs: `grep CRON /var/log/syslog`

**Problem: Telegram alerts not sent from standalone script**
- Verify `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` in `.env`
- Check logs: `tail -f public/telegram_web.log`
- Test Telegram connection manually
- Ensure cURL extension is enabled

### Debug Mode

Enable debug mode for detailed error information:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Log Files

Check these log files for issues:
- `storage/logs/laravel.log` - Application logs
- `public/telegram_web.log` - Telegram integration logs
- System logs (var/log/syslog on Linux)

## 🔒 Security Considerations

### Production Security

1. **Environment Variables**: Never commit `.env` file
2. **Database Security**: Use strong passwords and limit access
3. **HTTPS**: Always use HTTPS in production
4. **Bot Token Security**: Keep Telegram Bot Token secure
5. **Access Control**: Use strong admin passwords
6. **Regular Updates**: Keep Laravel and dependencies updated

### Security Headers

The application includes security headers:
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security: max-age=31536000

### Rate Limiting

- API endpoints are rate-limited
- Telegram alerts have cooldown periods
- Database queries are optimized

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Areas for Contribution

- Additional monitoring features
- New alert channels (Email, Slack, Discord)
- Performance optimizations
- Documentation improvements
- Bug fixes and testing

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Credits

- **Author**: Arash Moradi
- **Framework**: Laravel 10.x
- **Frontend**: Tailwind CSS
- **Icons**: Heroicons
- **Monitoring**: Custom Laravel implementation

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/moradi-arash/laravel-monitoring-website/issues)
- **Discussions**: [GitHub Discussions](https://github.com/moradi-arash/laravel-monitoring-website/discussions)
- **Email**: [Your Email Address]

---

**Made with ❤️ for reliable website monitoring**