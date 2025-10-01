# مستندات نسخه‌های اسکریپت Telegram

## گزینه 1: پیام تست ساده (نسخه اولیه)

**توضیحات:**
- این نسخه فقط یک پیام تست به تلگرام می‌فرستد
- هیچ منطق مانیتورینگی ندارد
- برای تست اتصال تلگرام و امنیت IP مناسب است
- پیام ارسالی: "🔔 Cron HTTP Test"

**کد کامل نسخه 1:**
```php
// Prepare Telegram message (Simple Test Version)
$currentTime = date('Y-m-d H:i:s T');
$message = "🔔 <b>Cron HTTP Test</b>\n\n";
$message .= "⏰ <b>Time:</b> {$currentTime}\n";
$message .= "🌐 <b>IP:</b> {$clientIp}\n";
$message .= "✅ <b>Status:</b> Authorized";

// Send Telegram message
logMessage("TELEGRAM_SEND | IP: {$clientIp} | Token: " . maskToken($botToken));

$result = sendTelegramMessage($botToken, $chatId, $message);

if ($result['success']) {
    logMessage("TELEGRAM_SUCCESS | IP: {$clientIp} | Response: " . substr($result['response'], 0, 100) . "...");
    sendResponse(200, [
        'success' => true,
        'message' => 'Telegram message sent successfully',
        'timestamp' => $currentTime
    ], $isCli);
} else {
    logMessage("TELEGRAM_FAILED | IP: {$clientIp} | Error: " . $result['response']);
    sendResponse(500, [
        'success' => false,
        'error' => 'Failed to send Telegram message',
        'details' => $result['response']
    ], $isCli);
}
```

**مزایا:**
- ساده و سریع
- بدون نیاز به اتصال دیتابیس
- مناسب برای تست

**معایب:**
- وبسایت‌ها را چک نمی‌کند
- فقط پیام تست می‌فرستد

---

## گزینه 2: مانیتورینگ کامل وبسایت‌ها (نسخه پیشرفته)

**توضیحات:**
- این نسخه تمام وبسایت‌های فعال را از دیتابیس می‌خواند
- هر وبسایت را چک می‌کند (HTTP request)
- خطاها را تشخیص می‌دهد (SSL, timeout, non-200)
- برای هر خطا پیام جداگانه به تلگرام می‌فرستد
- دیتابیس را با نتایج آپدیت می‌کند
- پیام ارسالی: "🚨 Website Down Alert" با جزئیات خطا

**ویژگی‌های اضافی:**
- اتصال به دیتابیس Laravel با PDO
- تشخیص انواع خطا (SSL, DNS, timeout, HTTP)
- آپدیت فیلدهای `last_checked_at`, `last_status_code`, `last_error`
- ارسال پیام جداگانه برای هر وبسایت خراب
- لاگ کامل تمام عملیات

**مزایا:**
- مانیتورینگ کامل وبسایت‌ها
- پیام‌های دقیق با جزئیات خطا
- آپدیت خودکار دیتابیس
- قابل استفاده به جای `php artisan monitor:websites`

**معایب:**
- نیاز به اتصال دیتابیس
- کمی پیچیده‌تر
- زمان اجرا بیشتر (بسته به تعداد وبسایت‌ها)

---

## نحوه برگشت به گزینه 1

اگر گزینه 2 مشکل داشت و می‌خواهید به گزینه 1 برگردید:

1. فایل `send_telegram.php` را باز کنید
2. بخش "Prepare Telegram message" (حدود خط 265) را پیدا کنید
3. کد مانیتورینگ را حذف کنید
4. کد زیر را جایگزین کنید:

```php
// Prepare Telegram message (Simple Test Version)
$currentTime = date('Y-m-d H:i:s T');
$message = "🔔 <b>Cron HTTP Test</b>\n\n";
$message .= "⏰ <b>Time:</b> {$currentTime}\n";
$message .= "🌐 <b>IP:</b> {$clientIp}\n";
$message .= "✅ <b>Status:</b> Authorized";

// Send Telegram message
logMessage("TELEGRAM_SEND | IP: {$clientIp} | Token: " . maskToken($botToken));

$result = sendTelegramMessage($botToken, $chatId, $message);

if ($result['success']) {
    logMessage("TELEGRAM_SUCCESS | IP: {$clientIp} | Response: " . substr($result['response'], 0, 100) . "...");
    sendResponse(200, [
        'success' => true,
        'message' => 'Telegram message sent successfully',
        'timestamp' => $currentTime
    ], $isCli);
} else {
    logMessage("TELEGRAM_FAILED | IP: {$clientIp} | Error: " . $result['response']);
    sendResponse(500, [
        'success' => false,
        'error' => 'Failed to send Telegram message',
        'details' => $result['response']
    ], $isCli);
}
```

---

## تنظیمات مورد نیاز برای گزینه 2

در فایل `.env` مطمئن شوید این مقادیر تنظیم شده‌اند:

```env
# Database Configuration (برای اتصال به دیتابیس)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Telegram Configuration
TELEGRAM_BOT_TOKEN=1167448253:AAEkv-Fdx3ep_TuDU8brrMeDUOYrY9XUrGI
TELEGRAM_CHAT_ID=324791521

# Cron Security
CRON_ALLOWED_IP=127.0.0.1
CRON_SECRET_KEY=your_secret_key
```

---

## تست هر نسخه

**تست گزینه 1 (پیام ساده):**
```bash
php public/send_telegram.php
```
باید پیام "🔔 Cron HTTP Test" دریافت کنید.

**تست گزینه 2 (مانیتورینگ کامل):**
```bash
php public/send_telegram.php
```
باید برای هر وبسایت خراب پیام "🚨 Website Down Alert" دریافت کنید.

---

## لاگ‌ها

تمام عملیات در فایل `public/telegram_web.log` ثبت می‌شوند:

```
[2025-01-15 10:30:45] ACCESS_ATTEMPT | IP: CLI | URL: CLI
[2025-01-15 10:30:45] ACCESS_GRANTED | IP: CLI | Status: AUTHORIZED
[2025-01-15 10:30:45] MONITORING_START | Checking 5 active websites
[2025-01-15 10:30:46] WEBSITE_CHECK | URL: https://google.com | Status: 200 | Result: SUCCESS
[2025-01-15 10:30:47] WEBSITE_CHECK | URL: https://4mdesign.org | Status: NULL | Result: FAILED | Error: Connection Error
[2025-01-15 10:30:47] TELEGRAM_SEND | Sending alert for https://4mdesign.org
[2025-01-15 10:30:48] TELEGRAM_SUCCESS | Alert sent successfully
[2025-01-15 10:30:50] MONITORING_END | Checked 5 websites | Failed: 1 | Success: 4
```

---

## پشتیبانی و عیب‌یابی

**مشکل: دیتابیس متصل نمی‌شود**
- بررسی کنید `.env` صحیح است
- بررسی کنید MySQL/MariaDB در حال اجرا است
- لاگ `telegram_web.log` را چک کنید

**مشکل: پیام تلگرام ارسال نمی‌شود**
- بررسی کنید `TELEGRAM_BOT_TOKEN` و `TELEGRAM_CHAT_ID` صحیح است
- لاگ `telegram_web.log` را چک کنید
- با گزینه 1 تست کنید

**مشکل: IP مسدود است (403)**
- `CRON_ALLOWED_IP` را در `.env` تنظیم کنید
- برای تست CLI، IP checking خودکار skip می‌شود

---

## توصیه نهایی

**برای محیط Production:**
- از گزینه 2 استفاده کنید (مانیتورینگ کامل)
- `CRON_SECRET_KEY` قوی تنظیم کنید
- لاگ‌ها را به صورت دوره‌ای پاک کنید
- از HTTPS استفاده کنید

**برای تست و Debug:**
- از گزینه 1 استفاده کنید (پیام ساده)
- لاگ‌ها را بررسی کنید
- با CLI تست کنید (بدون نیاز به IP whitelisting)
