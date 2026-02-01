# دليل نشر نظام إدارة الموظفين على Hostinger

## المتطلبات الأساسية

### متطلبات السيرفر
- PHP 8.2 أو أحدث
- MySQL 8.0 أو أحدث
- Composer 2.x
- Node.js 18+ و npm
- Git (اختياري)

### امتدادات PHP المطلوبة
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML
- GD
- ZIP
- cURL
```

---

## خطوات النشر على Hostinger

### 1. إعداد قاعدة البيانات

1. سجل الدخول إلى لوحة تحكم Hostinger
2. انتقل إلى **Databases** > **MySQL Databases**
3. أنشئ قاعدة بيانات جديدة:
   - اسم قاعدة البيانات: `newsarh_db`
   - اسم المستخدم: (سيتم إنشاؤه تلقائياً)
   - كلمة المرور: (احفظها بشكل آمن)
4. احفظ معلومات الاتصال:
   ```
   DB_HOST=localhost
   DB_DATABASE=اسم_قاعدة_البيانات
   DB_USERNAME=اسم_المستخدم
   DB_PASSWORD=كلمة_المرور
   ```

### 2. رفع الملفات

#### الطريقة الأولى: عبر File Manager

1. افتح **File Manager** في لوحة تحكم Hostinger
2. انتقل إلى مجلد `public_html`
3. احذف ملف `index.html` الافتراضي
4. ارفع جميع ملفات المشروع إلى `public_html`

#### الطريقة الثانية: عبر Git (موصى بها)

```bash
# عبر SSH
cd public_html
git clone https://github.com/abdullah-713/newsarh.git .
```

### 3. تثبيت المكتبات

```bash
# تثبيت مكتبات PHP
composer install --optimize-autoloader --no-dev

# تثبيت مكتبات JavaScript
npm install
npm run build
```

### 4. إعداد ملف البيئة

```bash
# انسخ ملف .env.example
cp .env.example .env

# حرر ملف .env بالمعلومات الصحيحة
nano .env
```

محتوى ملف `.env`:

```env
APP_NAME="نظام إدارة الموظفين"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Riyadh
APP_URL=https://yourdomain.com
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
APP_FAKER_LOCALE=ar_SA

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=file
CACHE_PREFIX=newsarh_cache

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### 5. توليد مفتاح التطبيق

```bash
php artisan key:generate
```

### 6. إنشاء هيكل قاعدة البيانات

#### الخيار الأول: استيراد ملف SQL

```bash
# عبر phpMyAdmin في لوحة تحكم Hostinger
# قم برفع ملف full.sql واستيراده
```

#### الخيار الثاني: تشغيل Migrations

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 7. إعداد الصلاحيات

```bash
# تعيين الصلاحيات الصحيحة
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

# تعيين المالك (إذا كان لديك صلاحيات SSH)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 8. تحسين الأداء

```bash
# تخزين الإعدادات مؤقتاً
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# تحسين autoloader
composer dump-autoload --optimize
```

### 9. إنشاء Symbolic Link

```bash
php artisan storage:link
```

### 10. إعداد Cron Jobs

في لوحة تحكم Hostinger، انتقل إلى **Advanced** > **Cron Jobs**:

```bash
# تشغيل كل دقيقة
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 11. إعداد Queue Worker (اختياري)

```bash
# تشغيل Queue Worker كل 5 دقائق
*/5 * * * * cd /home/username/public_html && php artisan queue:work --stop-when-empty
```

---

## إعدادات .htaccess

تأكد من وجود ملف `.htaccess` في المجلد `public` مع المحتوى التالي:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## نقل المجلد Public (موصى به للأمان)

إذا كانت بنية Hostinger تسمح بذلك:

1. انقل محتويات مجلد `public` إلى `public_html`
2. انقل باقي ملفات Laravel إلى مجلد خارج `public_html` (مثل `/home/username/laravel`)
3. حدّث ملف `index.php` في `public_html`:

```php
require __DIR__.'/../laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';
```

---

## الوصول إلى لوحة الإدارة

### حساب المدير الرئيسي (تم إنشاؤه عبر Seeder)

```
الرابط: https://yourdomain.com/admin
البريد: admin@newsarh.com
كلمة المرور: 12345678
```

### لوحة الموظفين

```
الرابط: https://yourdomain.com/employee
```

---

## إعداد SSL/HTTPS

1. في لوحة تحكم Hostinger، انتقل إلى **SSL**
2. قم بتفعيل **Free SSL Certificate**
3. انتظر بضع دقائق حتى يتم التفعيل
4. حدّث `APP_URL` في ملف `.env` ليبدأ بـ `https://`
5. أضف في `.htaccess`:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## حل المشاكل الشائعة

### خطأ 500 - Internal Server Error

```bash
# تحقق من سجلات الأخطاء
cat storage/logs/laravel.log

# تأكد من صلاحيات المجلدات
chmod -R 755 storage bootstrap/cache

# امسح الـ cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### خطأ "No input file specified"

تحقق من إعدادات `.htaccess` وتأكد من تفعيل `mod_rewrite`

### الصور لا تظهر

```bash
# أنشئ symbolic link
php artisan storage:link

# تحقق من صلاحيات مجلد storage
chmod -R 755 storage/app/public
```

### مشاكل في قاعدة البيانات

```bash
# تحقق من الاتصال
php artisan tinker
>>> DB::connection()->getPdo();

# امسح cache الإعدادات
php artisan config:clear
```

---

## تحديثات مستقبلية

```bash
# سحب آخر التحديثات من Git
git pull origin main

# تحديث المكتبات
composer install --no-dev
npm install && npm run build

# تشغيل migrations الجديدة
php artisan migrate --force

# تحديث الـ cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## النسخ الاحتياطي

### نسخ احتياطي لقاعدة البيانات

```bash
# عبر SSH
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# عبر phpMyAdmin
# اذهب إلى قاعدة البيانات > Export > Go
```

### نسخ احتياطي للملفات

```bash
# ضغط المشروع بالكامل
tar -czf backup_$(date +%Y%m%d).tar.gz /home/username/public_html

# نسخ مجلد storage فقط
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app
```

---

## الأمان

### تأمين ملف .env

```bash
# تأكد من عدم إمكانية الوصول إليه
chmod 644 .env
```

### تعطيل عرض الأخطاء في الإنتاج

في `.env`:
```
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=error
```

### تحديث المكتبات بانتظام

```bash
composer update --no-dev
npm update
```

---

## الدعم الفني

للمساعدة والدعم:
- التوثيق الرسمي: [Laravel Documentation](https://laravel.com/docs)
- دعم Hostinger: [Hostinger Help Center](https://support.hostinger.com)
- ملف التقرير الشامل: `COMPREHENSIVE_REPORT_AR.md`

---

## ملاحظات مهمة

1. **لا تنشر** ملف `.env` على Git أبداً
2. **احفظ** نسخة احتياطية من قاعدة البيانات قبل أي تحديث
3. **اختبر** جميع المميزات بعد النشر
4. **راقب** سجلات الأخطاء بانتظام
5. **حدّث** كلمات المرور الافتراضية فوراً
6. **فعّل** الـ HTTPS دائماً للأمان
7. **استخدم** Cron Jobs لتشغيل المهام المجدولة

---

**تاريخ آخر تحديث:** 1 فبراير 2026
