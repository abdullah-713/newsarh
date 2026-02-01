# دليل سريع للنشر على Hostinger

## 🎯 نظرة عامة
هذا دليل سريع لنشر نظام إدارة الموظفين على استضافة Hostinger في 10 خطوات فقط.

---

## ✅ قبل البدء

تأكد من توفر:
- حساب Hostinger (باقة Business أو أعلى)
- نطاق مرتبط بالاستضافة
- معلومات وصول SSH (اختياري لكن موصى به)

---

## 🚀 الخطوات السريعة

### 1️⃣ إنشاء الحزمة (محلياً)

```bash
# على جهازك المحلي
cd /path/to/newsarh
./create-package.sh
```

سيتم إنشاء ملف: `newsarh-hostinger-YYYYMMDD-HHMMSS.zip`

### 2️⃣ إنشاء قاعدة البيانات

في لوحة تحكم Hostinger:
1. اذهب إلى **Websites** > **Manage**
2. اختر **Databases** > **MySQL Databases**
3. اضغط **Create Database**
4. احفظ المعلومات:
   ```
   Database: u123456_newsarh
   User: u123456_user
   Password: [كلمة المرور]
   Host: localhost
   ```

### 3️⃣ رفع الملفات

#### الخيار أ: عبر File Manager
1. افتح **File Manager** في لوحة تحكم Hostinger
2. اذهب إلى `public_html`
3. احذف ملف `index.html` الافتراضي
4. ارفع ملف `.zip`
5. اضغط بالزر الأيمن > **Extract**

#### الخيار ب: عبر SSH (أسرع)
```bash
ssh u123456@your-server-ip
cd public_html
wget https://yourdomain.com/newsarh-package.zip
unzip newsarh-package.zip
rm newsarh-package.zip
```

### 4️⃣ استيراد قاعدة البيانات

1. افتح **phpMyAdmin** من لوحة التحكم
2. اختر قاعدة البيانات التي أنشأتها
3. اذهب إلى **Import**
4. اختر ملف `full.sql`
5. اضغط **Go**

### 5️⃣ إعداد ملف .env

```bash
# عبر SSH
cd public_html
cp .env.hostinger .env
nano .env
```

حدّث المتغيرات التالية:
```env
APP_URL=https://yourdomain.com
DB_DATABASE=u123456_newsarh
DB_USERNAME=u123456_user
DB_PASSWORD=your_db_password
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your_email_password
```

### 6️⃣ تشغيل سكريبت النشر

```bash
cd public_html
chmod +x deploy.sh
./deploy.sh
```

اتبع التعليمات على الشاشة.

### 7️⃣ تفعيل SSL

في لوحة تحكم Hostinger:
1. اذهب إلى **SSL**
2. اختر **Setup** بجانب نطاقك
3. انتظر 5-10 دقائق للتفعيل

### 8️⃣ إعداد Cron Jobs

في لوحة التحكم:
1. اذهب إلى **Advanced** > **Cron Jobs**
2. أضف:
   ```
   * * * * * cd /home/u123456/public_html && php artisan schedule:run
   ```

### 9️⃣ اختبار الموقع

افتح المتصفح واذهب إلى:
- الصفحة الرئيسية: `https://yourdomain.com`
- لوحة الإدارة: `https://yourdomain.com/admin`

بيانات الدخول الافتراضية:
```
البريد: admin@newsarh.com
كلمة المرور: 12345678
```

### 🔟 تأمين النظام

1. **غيّر كلمة المرور:**
   - سجل دخول كمدير
   - اذهب إلى الملف الشخصي
   - غيّر كلمة المرور

2. **تحديث .env:**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

3. **مسح الـ cache:**
   ```bash
   php artisan config:cache
   ```

---

## ✨ انتهى!

موقعك الآن يعمل على: `https://yourdomain.com`

---

## 🆘 حل المشاكل السريع

### خطأ 500
```bash
chmod -R 755 storage bootstrap/cache
php artisan cache:clear
php artisan config:clear
```

### الصور لا تظهر
```bash
php artisan storage:link
chmod -R 755 storage/app/public
```

### لا يمكن تسجيل الدخول
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📚 للتفاصيل الكاملة

راجع الملفات:
- `HOSTINGER_DEPLOYMENT.md` - الدليل الشامل
- `DEPLOYMENT_CHECKLIST.md` - قائمة المراجعة
- `REQUIREMENTS.md` - المتطلبات

---

## 📞 الدعم

- دعم Hostinger: https://support.hostinger.com
- توثيق Laravel: https://laravel.com/docs
- توثيق Filament: https://filamentphp.com/docs

---

**وقت النشر المتوقع:** 15-30 دقيقة
**مستوى الصعوبة:** متوسط 🟡
