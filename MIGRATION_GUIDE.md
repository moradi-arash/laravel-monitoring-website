# راهنمای اجرای Migration در هاست

## مشکل
ارور 500 در صفحه `/dashboard` به دلیل عدم وجود فیلدهای `check_interval_minutes` و `last_auto_check_at` در جدول `site_settings` در هاست است.

## راه حل

### روش 1: اجرای Migration ها از طریق SSH (توصیه می‌شود)

اگر به SSH دسترسی دارید:

```bash
# به دایرکتوری پروژه بروید
cd /path/to/your/project

# Migration ها را اجرا کنید
php artisan migrate

# Cache را پاک کنید
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### روش 2: اجرای Migration ها از طریق cPanel

اگر cPanel دارید و PHP Selector فعال است:

1. به **cPanel** لاگین کنید
2. به **Terminal** یا **PHP Selector** بروید
3. دستورات زیر را اجرا کنید:

```bash
cd ~/public_html/your-project-folder
/opt/alt/php83/usr/bin/php artisan migrate
/opt/alt/php83/usr/bin/php artisan config:clear
```

### روش 3: اجرای Manual SQL (در صورت عدم دسترسی به Artisan)

اگر به هیچ کدام از روش‌های بالا دسترسی ندارید، می‌توانید SQL را دستی اجرا کنید:

1. به **phpMyAdmin** در cPanel بروید
2. دیتابیس پروژه را انتخاب کنید
3. SQL زیر را اجرا کنید:

```sql
-- بررسی اینکه آیا فیلدها وجود دارند یا نه
SHOW COLUMNS FROM site_settings LIKE 'check_interval_minutes';

-- اگر فیلد وجود نداشت، اضافه کنید:
ALTER TABLE `site_settings` 
ADD COLUMN `check_interval_minutes` INT NOT NULL DEFAULT 10 AFTER `logo_path`,
ADD COLUMN `last_auto_check_at` TIMESTAMP NULL AFTER `check_interval_minutes`;

-- بررسی نتیجه
DESCRIBE site_settings;
```

### روش 4: استفاده از فایل PHP موقت

اگر هیچ کدام از روش‌های بالا کار نکرد، می‌توانید یک فایل PHP موقت بسازید:

1. فایل `migrate.php` را در root پروژه بسازید:

```php
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('migrate', ['--force' => true]);

echo "Migration completed with status: " . $status;
```

2. از طریق مرورگر به آن دسترسی پیدا کنید: `https://yourdomain.com/migrate.php`

3. پس از اجرای موفق، فایل را حذف کنید!

## بررسی نتیجه

پس از اجرای migration ها، می‌توانید از طریق phpMyAdmin بررسی کنید که فیلدها اضافه شده‌اند:

```sql
DESCRIBE site_settings;
```

باید خروجی شبیه این باشد:

```
+---------------------------+--------------+------+-----+---------+----------------+
| Field                     | Type         | Null | Key | Default | Extra          |
+---------------------------+--------------+------+-----+---------+----------------+
| id                        | bigint(20)   | NO   | PRI | NULL    | auto_increment |
| site_name                 | varchar(255) | YES  |     | NULL    |                |
| logo_path                 | varchar(255) | YES  |     | NULL    |                |
| check_interval_minutes    | int(11)      | NO   |     | 10      |                |
| last_auto_check_at        | timestamp    | YES  |     | NULL    |                |
| created_at                | timestamp    | YES  |     | NULL    |                |
| updated_at                | timestamp    | YES  |     | NULL    |                |
+---------------------------+--------------+------+-----+---------+----------------+
```

## تغییرات انجام شده در کد

برای جلوگیری از ارور 500 در آینده، کد را ایمن‌تر کرده‌ام:

- اگر فیلدهای `check_interval_minutes` و `last_auto_check_at` وجود نداشته باشند، سیستم به صورت خودکار از مقادیر پیش‌فرض (10 دقیقه) استفاده می‌کند
- همه خطاها در فایل log ثبت می‌شوند
- کاربر دیگر ارور 500 نمی‌بیند، فقط countdown timer به صورت پیش‌فرض کار می‌کند

## بررسی Log ها

برای دیدن خطاهای احتمالی:

```bash
tail -f storage/logs/laravel.log
```

یا از طریق cPanel به `storage/logs/laravel.log` دسترسی پیدا کنید.

## پشتیبانی

اگر هنوز مشکل دارید:

1. محتوای `storage/logs/laravel.log` را بررسی کنید
2. مطمئن شوید که فیلدها در دیتابیس هاست موجود هستند
3. Cache Laravel را پاک کنید: `php artisan config:clear && php artisan cache:clear`

