# متطلبات النظام

## متطلبات السيرفر الأساسية

### PHP
- **الإصدار:** PHP 8.2 أو أحدث
- **الإصدار الموصى به:** PHP 8.3

### قاعدة البيانات
- **MySQL:** 8.0 أو أحدث
- **MariaDB:** 10.11 أو أحدث (بديل)

### Composer
- **الإصدار:** Composer 2.x
- **الإصدار الموصى به:** أحدث إصدار مستقر

### Node.js & npm
- **Node.js:** 18.x أو أحدث
- **npm:** 9.x أو أحدث
- مطلوب لبناء assets (CSS, JavaScript)

---

## امتدادات PHP المطلوبة

### امتدادات أساسية (ضرورية)
- ✓ **BCMath** - للعمليات الحسابية الدقيقة
- ✓ **Ctype** - للتحقق من أنواع الأحرف
- ✓ **cURL** - لطلبات HTTP
- ✓ **DOM** - لمعالجة XML/HTML
- ✓ **Fileinfo** - للحصول على معلومات الملفات
- ✓ **Filter** - لتصفية البيانات
- ✓ **Hash** - لدوال التشفير
- ✓ **Mbstring** - لمعالجة النصوص متعددة البايت
- ✓ **OpenSSL** - للتشفير وSSL
- ✓ **PCRE** - للتعابير النمطية
- ✓ **PDO** - لاتصال قاعدة البيانات
- ✓ **PDO_MySQL** - لـ MySQL/MariaDB
- ✓ **Session** - لإدارة الجلسات
- ✓ **Tokenizer** - للمحلل اللغوي
- ✓ **XML** - لمعالجة XML
- ✓ **XMLWriter** - لكتابة XML
- ✓ **JSON** - لمعالجة JSON

### امتدادات إضافية (موصى بها)
- ✓ **GD** أو **Imagick** - لمعالجة الصور
- ✓ **ZIP** - لضغط وفك ضغط الملفات
- ✓ **Exif** - لقراءة معلومات الصور
- ✓ **Intl** - للتدويل
- ✓ **Redis** - للـ cache (اختياري)

---

## إعدادات PHP الموصى بها

### في ملف `php.ini`:

```ini
# حدود الذاكرة
memory_limit = 256M

# حدود الرفع
upload_max_filesize = 10M
post_max_size = 12M

# وقت التنفيذ
max_execution_time = 300
max_input_time = 300

# إعدادات الجلسة
session.gc_maxlifetime = 7200

# المنطقة الزمنية
date.timezone = Asia/Riyadh

# عرض الأخطاء (للتطوير فقط)
display_errors = Off
error_reporting = E_ALL

# تسجيل الأخطاء
log_errors = On
error_log = /path/to/php_errors.log
```

---

## متطلبات الاستضافة (Hostinger)

### الباقة الموصى بها
- **Business** أو **Premium** على الأقل
- **Cloud Hosting** للأداء الأفضل

### المواصفات المطلوبة
- **المساحة:** 10 GB على الأقل
- **الذاكرة:** 2 GB RAM على الأقل
- **النطاق الترددي:** غير محدود
- **قواعد البيانات:** دعم MySQL 8.0+
- **SSL:** شهادة SSL مجانية أو مدفوعة

### الصلاحيات المطلوبة
- ✓ وصول SSH (موصى به بشدة)
- ✓ Cron Jobs
- ✓ Git (للتحديثات)
- ✓ Composer
- ✓ Node.js & npm

---

## مكتبات Laravel المطلوبة

### تثبت تلقائياً عبر Composer

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "filament/filament": "^3.0",
        "livewire/livewire": "^3.0",
        "spatie/laravel-permission": "^6.0",
        "maatwebsite/excel": "^3.1"
    }
}
```

---

## متطلبات قاعدة البيانات

### هيكل قاعدة البيانات
- **الترميز:** `utf8mb4_unicode_ci`
- **المحرك:** InnoDB
- **الحجم المتوقع:** 50-100 MB (بداية)
- **النمو المتوقع:** 1-5 GB (سنوياً)

### الجداول الرئيسية
- 40+ جدول رئيسي
- علاقات معقدة بين الجداول
- فهارس محسّنة للأداء

---

## متطلبات التخزين

### المساحة المطلوبة
- **الكود:** ~150 MB
- **المكتبات (vendor):** ~200 MB
- **node_modules:** ~300 MB (للتطوير فقط)
- **Assets المبنية:** ~20 MB
- **قاعدة البيانات:** ~100 MB (بداية)
- **الملفات المرفوعة:** متغير (حسب الاستخدام)

### الإجمالي الموصى به
- **الحد الأدنى:** 2 GB
- **الموصى به:** 5 GB+

---

## متطلبات الأمان

### SSL/TLS
- ✓ شهادة SSL فعّالة (HTTPS)
- ✓ TLS 1.2 أو أحدث

### جدار الحماية
- ✓ تفعيل جدار حماية التطبيقات (WAF)
- ✓ حماية من DDoS

### النسخ الاحتياطي
- ✓ نسخ احتياطي يومي لقاعدة البيانات
- ✓ نسخ احتياطي أسبوعي للملفات

---

## متطلبات الأداء

### للاستخدام الخفيف (< 100 مستخدم)
- **CPU:** 2 Cores
- **RAM:** 2 GB
- **Storage:** 10 GB SSD

### للاستخدام المتوسط (100-500 مستخدم)
- **CPU:** 4 Cores
- **RAM:** 4 GB
- **Storage:** 20 GB SSD

### للاستخدام الثقيل (> 500 مستخدم)
- **CPU:** 8+ Cores
- **RAM:** 8+ GB
- **Storage:** 50+ GB SSD
- **Cache:** Redis أو Memcached

---

## أدوات التطوير (للمطورين فقط)

### محلياً
- **Git:** لإدارة النسخ
- **VS Code** أو **PHPStorm:** للبرمجة
- **TablePlus** أو **phpMyAdmin:** لإدارة قواعد البيانات
- **Postman:** لاختبار API

### البيئة المحلية
- **Laravel Valet** (Mac)
- **Laravel Homestead** (جميع الأنظمة)
- **XAMPP/WAMP** (Windows)
- **Docker** (جميع الأنظمة)

---

## التحقق من المتطلبات

### عبر سطر الأوامر

```bash
# التحقق من إصدار PHP
php -v

# التحقق من الامتدادات المثبتة
php -m

# التحقق من إصدار Composer
composer --version

# التحقق من إصدار Node.js
node -v
npm -v

# التحقق من إصدار MySQL
mysql --version
```

### عبر PHP

قم بإنشاء ملف `check.php`:

```php
<?php
// التحقق من إصدار PHP
echo "PHP Version: " . PHP_VERSION . "\n\n";

// التحقق من الامتدادات المطلوبة
$required_extensions = [
    'bcmath', 'ctype', 'curl', 'dom', 'fileinfo',
    'filter', 'hash', 'mbstring', 'openssl', 'pcre',
    'pdo', 'pdo_mysql', 'session', 'tokenizer', 'xml',
    'json', 'zip', 'gd'
];

echo "Required Extensions:\n";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✓' : '✗';
    echo "$status $ext\n";
}

// التحقق من إعدادات PHP
echo "\nPHP Settings:\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
?>
```

ثم قم بتشغيله:
```bash
php check.php
```

---

## استكشاف المشاكل

### PHP Version Too Low
```bash
# تحديث PHP على Hostinger
# اتصل بالدعم الفني أو استخدم لوحة التحكم لتغيير الإصدار
```

### Missing Extensions
```bash
# على Ubuntu/Debian
sudo apt-get install php8.2-{bcmath,curl,gd,mbstring,mysql,xml,zip}

# على CentOS/RHEL
sudo yum install php82-{bcmath,gd,mbstring,mysqlnd,xml}
```

### Composer Not Found
```bash
# تثبيت Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

---

## ملاحظات مهمة

1. **لا تستخدم** PHP 8.0 أو أقدم - غير مدعوم
2. **تأكد** من تفعيل جميع الامتدادات المطلوبة قبل التثبيت
3. **استخدم** SSD للأداء الأفضل
4. **فعّل** OPcache في بيئة الإنتاج
5. **راقب** استخدام الذاكرة والمعالج بانتظام
6. **حدّث** PHP والمكتبات بانتظام للأمان

---

**آخر تحديث:** 1 فبراير 2026
**التوافق:** Laravel 11.x | Filament 3.x | PHP 8.2+
