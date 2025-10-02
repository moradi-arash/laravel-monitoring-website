# Laravel Website Monitoring Tool

A comprehensive **multi-tenant** Laravel-based website monitoring solution with **per-user configuration**, **role-based access control**, and **encrypted credential storage**. Monitor unlimited websites with real-time uptime tracking, SSL certificate monitoring, and instant Telegram alerts sent to each user's personal bot.

## 🚀 Features

- ✨ **Multi-Tenant Architecture**: Each user manages their own websites with isolated data
- 👥 **User Management**: Admin panel for managing users and roles
- 🔐 **Role-Based Access Control**: Admin and regular user roles with different permissions
- 🔒 **Encrypted Credentials**: Telegram tokens stored with Laravel encryption
- ⚙️ **Settings Dashboard**: Web-based interface for configuring Telegram notifications per user
- 📊 **Admin System Configuration**: Global cron security settings (admin-only)
- 🌐 **Per-User Monitoring**: Each user receives alerts via their own Telegram bot
- 🔑 **Secure Authentication**: Laravel Breeze authentication with password protection
- 🗑️ **Cascade Deletion**: Removing a user automatically deletes all their websites and settings
- **Real-time Website Monitoring**: Continuous uptime monitoring with configurable check intervals
- **Instant Telegram Alerts**: Immediate notifications via Telegram Bot when issues are detected
- **Comprehensive Error Detection**: Monitors SSL certificates, DNS resolution, timeouts, and HTTP errors
- **Admin Dashboard**: Web-based interface for managing monitored websites
- **Bulk Website Import**: Add multiple websites at once via CSV upload
- **Scheduled Monitoring**: Uses Laravel's task scheduler for reliable background monitoring
- **Detailed Logging**: Complete history of all monitoring activities and alerts
- **Security Focused**: Secure authentication and data protection

## 🏗️ Multi-Tenant Architecture

This application supports **multiple users**, each with their own:

### User Isolation
- **Separate Websites**: Each user can only view and manage their own websites
- **Personal Telegram Bot**: Each user configures their own Telegram credentials
- **Independent Alerts**: Monitoring alerts are sent to each user's personal Telegram chat
- **Encrypted Storage**: All Telegram credentials are encrypted in the database using Laravel's encryption

### User Roles

| Role | Capabilities |
|------|-------------|
| **Admin** | • Manage all users<br>• View/edit/delete any user<br>• Change user roles<br>• Configure global cron security settings<br>• Access system configuration<br>• Manage own websites and settings |
| **User** | • Manage own websites<br>• Configure own Telegram settings<br>• View own monitoring history<br>• Receive personal alerts |

### Data Security
- **Encrypted Credentials**: Telegram bot tokens and chat IDs are encrypted at rest
- **Route Model Binding**: Automatic user scoping prevents cross-user access
- **Middleware Protection**: Admin routes protected by custom middleware
- **Self-Protection**: Admins cannot delete or demote themselves
- **Cascade Deletes**: Removing a user automatically deletes all associated data

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
- OpenSSL extension (for credential encryption)
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

# Global Cron Security Settings (Admin-only configuration)
CRON_ALLOWED_IP=127.0.0.1
CRON_SECRET_KEY=your_secret_key_here
```

**Note**: Telegram credentials are now configured per-user via the Settings dashboard after login, not in .env.

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

This creates:
- **Admin User**: admin@example.com / password (role: admin)
- **Regular User**: user@example.com / password (role: user)
- Sample websites assigned to the admin user

**⚠️ Important**: Change these default credentials immediately after first login!

#### Manual Admin Creation

Alternatively, create a custom admin user:

```bash
php artisan tinker
```

Then run:
```php
User::create([
    'name' => 'Your Name',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('your-secure-password'),
    'role' => 'admin',  // Important: set role to admin
]);
```

#### Migrate Existing .env Telegram Settings

If you're upgrading from a previous version with Telegram credentials in .env:

```bash
php artisan settings:seed --user-id=1
```

This command:
- Reads TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID from .env
- Creates encrypted user_settings record for the specified user
- Assigns all existing websites to that user

## 🎯 First Login & Configuration

### 1. Login to Dashboard

Visit `https://your-domain.com` and login with your admin credentials.

### 2. Configure Telegram Settings

**For Admin Users:**
1. Navigate to **Settings** in the top menu
2. You'll see two sections:
   - **Telegram Notification Settings** (your personal bot)
   - **System Configuration** (global cron security - admin only)

**For Regular Users:**
1. Navigate to **Settings**
2. Configure your **Telegram Bot Token** and **Chat ID**
3. Click "Save Settings"

### 3. Add Websites to Monitor

1. Navigate to **Websites**
2. Click "Add Website" or "Bulk Import"
3. Enter website details
4. Websites are automatically associated with your user account

### 4. Admin: Manage Users (Admin Only)

1. Navigate to **Users** (visible only to admins)
2. View all registered users
3. Change user roles (User ↔ Admin)
4. Delete users (with confirmation)
5. View user details and their websites

## 🤖 Telegram Bot Setup

> **Note**: In this multi-tenant system, each user configures their own Telegram bot via the Settings dashboard. The steps below explain how to create a bot and get credentials, which you'll then enter in the Settings page.

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

### Step 3: Configure Your Bot in Dashboard

1. **Login to the application**
2. **Navigate to Settings** (top menu)
3. **Enter your credentials**:
   - Telegram Bot Token: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`
   - Telegram Chat ID: `123456789`
4. **Click "Save Settings"**
5. **Test the integration**: Add a website and trigger a test alert

**Security**: Your Telegram credentials are encrypted in the database using Laravel's encryption system.

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

### User Settings (Per-User Configuration)

Each user configures their own settings via the **Settings** dashboard:

**Telegram Notification Settings:**
- Telegram Bot Token (encrypted)
- Telegram Chat ID (encrypted)
- Configured at: `/settings`

**How to access:**
1. Login to your account
2. Click "Settings" in the navigation
3. Enter your Telegram credentials
4. Click "Save Settings"

### Admin Settings (Admin-Only Configuration)

Admins have access to additional **System Configuration** settings:

**Global Cron Security Settings:**
- `CRON_ALLOWED_IP`: IP address(es) allowed to trigger monitoring via HTTP
- `CRON_SECRET_KEY`: Secret key required in URL for HTTP monitoring

**Configuration methods:**

**Method 1: Via Admin Dashboard** (Recommended)
1. Login as admin
2. Navigate to Settings
3. Scroll to "System Configuration" section
4. Update CRON settings
5. Click "Save System Settings"

**Method 2: Via .env File**
```env
# Global Cron Security Settings
CRON_ALLOWED_IP=127.0.0.1,1.2.3.4
CRON_SECRET_KEY=your_secret_key_here
```

**Generate Secret Key:**
```bash
openssl rand -hex 32
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

### Monitoring Configuration

The monitoring schedule is defined in `app/Console/Kernel.php`:
- Default: Every 10 minutes
- Customizable via Laravel scheduler

## 🚀 Usage

### For Regular Users

#### 1. Configure Telegram Settings
- Navigate to **Settings**
- Enter your Telegram Bot Token and Chat ID
- Save settings (credentials are encrypted automatically)

#### 2. Add Websites
- Go to **Websites** → **Add Website**
- Enter website URL and name
- Enable/disable monitoring
- Websites are automatically associated with your account

#### 3. Monitor Your Websites
- View real-time status on the **Websites** page
- Receive Telegram alerts when issues are detected
- Check monitoring history and logs

### For Admin Users

Admins have all regular user capabilities plus:

#### 1. User Management
- Navigate to **Users** (admin-only menu item)
- View all registered users
- See user statistics (websites count, role, join date)
- Change user roles (promote to admin or demote to user)
- Delete users (with cascade delete of all their data)

#### 2. System Configuration
- Navigate to **Settings**
- Configure global cron security settings:
  - CRON_ALLOWED_IP: Whitelist IPs for HTTP monitoring
  - CRON_SECRET_KEY: Secret key for monitoring endpoint
- These settings apply system-wide

#### 3. View User Details
- Click on any user in the Users list
- View user information, websites, and settings status
- Change user role or delete user from details page

### Telegram Alerts

Each user receives alerts via their own Telegram bot when:
- Their websites go down
- SSL certificates expire
- DNS resolution fails
- HTTP errors occur (4xx, 5xx)
- Connection timeouts happen

## 👥 User Management (Admin Only)

### Accessing User Management

Only users with the **admin** role can access user management features.

**Navigation**: Click **Users** in the top menu (visible only to admins)

### User List

The user management page displays:
- User ID, Name, Email
- Role (Admin/User) with colored badges
- Number of websites owned
- Account creation date
- Action buttons (View Details, Change Role, Delete)

### Changing User Roles

**From User List:**
1. Find the user in the list
2. Use the role dropdown to select new role
3. Click "Update Role"
4. Confirm the action

**From User Details:**
1. Click on a user to view details
2. Scroll to "Role Management" section
3. Select new role from dropdown
4. Click "Update Role"

**Restrictions:**
- Admins cannot change their own role (prevents accidental lockout)
- At least one admin must exist in the system

### Deleting Users

**⚠️ Warning**: Deleting a user permanently removes:
- User account
- All their websites
- Their Telegram settings
- All monitoring history

**Deletion Process:**
1. Click "Delete" button next to user (or in user details page)
2. A confirmation modal appears showing:
   - User information
   - Number of websites to be deleted
   - Data that will be removed
3. **Type the user's exact name** to confirm
4. Click "Delete User Permanently"

**Security Features:**
- Admins cannot delete themselves
- Requires typing user's name for confirmation
- Server-side validation of confirmation
- All deletions are logged

### User Details Page

Click on any user to view:
- **User Information**: Name, email, role, join date
- **Telegram Settings**: Configuration status (configured/not configured)
- **User's Websites**: List of all websites owned by the user
- **Role Management**: Change user role (if not viewing own account)
- **Danger Zone**: Delete user account (if not viewing own account)

## 📅 Scheduled Monitoring Setup

> **Multi-Tenant Monitoring**: The monitoring system automatically checks all active websites for all users and sends alerts to each user's personal Telegram bot.

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

**How Multi-Tenant Monitoring Works:**
1. Scheduler runs `monitor:websites` command every 10 minutes
2. Command loads all users who have active websites
3. For each user:
   - Loads their Telegram credentials (decrypted automatically)
   - Checks all their active websites
   - Sends alerts to their personal Telegram bot if issues detected
4. Users without Telegram credentials: websites are checked but no alerts sent

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

**Multi-Tenant Support:**
The standalone script now supports per-user Telegram credentials:
- Reads user settings from `user_settings` table
- Decrypts credentials automatically
- Sends alerts to each user's personal bot
- Global security settings (CRON_ALLOWED_IP, CRON_SECRET_KEY) remain in .env

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
│   ├── Console/Commands/
│   │   ├── MonitorWebsites.php        # Main monitoring command (multi-tenant)
│   │   ├── SeedUserSettings.php       # Migrate .env settings to database
│   │   └── DecryptUserSettings.php    # Helper for standalone script
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── WebsiteController.php  # User's website management
│   │   │   ├── SettingsController.php # Per-user settings + admin config
│   │   │   ├── UserController.php     # Admin user management
│   │   │   └── ProfileController.php  # User profile
│   │   ├── Middleware/
│   │   │   └── EnsureUserIsAdmin.php  # Admin role middleware
│   │   └── Requests/
│   │       ├── UpdateSettingsRequest.php      # User Telegram settings
│   │       └── UpdateCronSettingsRequest.php  # Admin cron settings
│   ├── Models/
│   │   ├── User.php                   # User model with role support
│   │   ├── Website.php                # Website model (belongs to user)
│   │   └── UserSetting.php            # Encrypted user settings
│   └── Services/
│       └── TelegramService.php        # Multi-tenant Telegram service
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── add_role_to_users_table.php
│   │   ├── create_user_settings_table.php
│   │   ├── add_user_id_to_websites_table.php
│   │   └── create_websites_table.php
│   └── seeders/
│       └── DatabaseSeeder.php         # Creates admin + regular user
├── resources/views/
│   ├── settings/
│   │   ├── index.blade.php            # Settings dashboard
│   │   └── partials/
│   │       ├── update-telegram-settings-form.blade.php
│   │       └── update-cron-settings-form.blade.php
│   ├── users/
│   │   ├── index.blade.php            # User management (admin)
│   │   └── show.blade.php             # User details (admin)
│   └── websites/
│       ├── index.blade.php            # User's websites
│       ├── create.blade.php
│       └── edit.blade.php
└── public/
    ├── send_telegram.php              # Standalone monitoring script
    └── telegram_web.log               # Standalone script logs
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

### Test Multi-Tenant Features

**Test User Isolation:**
```bash
php artisan tinker
```
```php
// Create two users
$user1 = User::find(1);
$user2 = User::find(2);

// Verify user1 can only see their websites
$user1->websites; // Should only show user1's websites

// Verify settings are isolated
$user1->settings; // User1's Telegram settings
$user2->settings; // User2's Telegram settings (different)
```

**Test Admin Features:**
1. Login as admin user
2. Navigate to Users page (should be visible)
3. Try changing a user's role
4. Try deleting a user (with confirmation)
5. Verify cascade delete removed user's websites

**Test Regular User:**
1. Login as regular user
2. Verify Users menu is not visible
3. Try accessing `/admin/users` directly (should get 403)
4. Verify can only see own websites

**Test Telegram Per-User:**
```bash
php artisan monitor:websites
```
Verify:
- Each user's websites are checked
- Alerts sent to correct Telegram bot
- Users without settings are skipped

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

#### 6. User Cannot Access Admin Features
- Verify user role is 'admin': `User::find($id)->role`
- Check middleware is registered in `bootstrap/app.php`
- Clear route cache: `php artisan route:clear`
- Check browser console for JavaScript errors

#### 7. Telegram Credentials Not Saving
- Verify APP_KEY is set in .env
- Check database connection
- Verify user_settings table exists: `php artisan migrate:status`
- Check Laravel logs: `tail -f storage/logs/laravel.log`

#### 8. User Deletion Not Working
- Verify foreign key constraints exist in migrations
- Check cascade delete is configured: `onDelete('cascade')`
- Verify admin is not trying to delete themselves
- Check validation errors in browser console

#### 9. Monitoring Not Sending Per-User Alerts
- Verify users have configured Telegram settings
- Check settings are encrypted properly: `UserSetting::all()`
- Test TelegramService: `TelegramService::forUser($user)`
- Check monitoring logs: `php artisan monitor:websites`

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

### Multi-Tenant Security

1. **Data Isolation**: Users can only access their own websites and settings
2. **Route Model Binding**: Automatic scoping prevents cross-user access
3. **Encrypted Credentials**: All Telegram tokens encrypted with Laravel encryption
4. **Role-Based Access**: Admin middleware protects sensitive routes
5. **Self-Protection**: Admins cannot delete or demote themselves
6. **Cascade Deletes**: Database-level cascade ensures data consistency

### Credential Encryption

- **Algorithm**: AES-256-CBC (Laravel default)
- **Key**: Uses APP_KEY from .env
- **Storage**: Encrypted values stored in database
- **Decryption**: Automatic via Eloquent model casting
- **Rotation**: Change APP_KEY requires re-encryption of all credentials

### Admin Access Control

- **Middleware**: `EnsureUserIsAdmin` protects admin routes
- **Authorization**: FormRequest validation for sensitive actions
- **Audit Trail**: All role changes and deletions logged
- **Session Security**: Laravel session management with CSRF protection

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
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Breeze
- **Encryption**: Laravel Encryption (AES-256-CBC)
- **Frontend**: Tailwind CSS
- **Icons**: Heroicons
- **Monitoring**: Custom Laravel implementation

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/moradi-arash/laravel-monitoring-website/issues)
- **Discussions**: [GitHub Discussions](https://github.com/moradi-arash/laravel-monitoring-website/discussions)
- **Email**: [Your Email Address]

---

**Made with ❤️ for reliable website monitoring**