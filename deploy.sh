#!/bin/bash

###############################################################################
# سكريبت نشر تطبيق Laravel على Hostinger
# الاستخدام: ./deploy.sh
###############################################################################

echo "=========================================="
echo "بدء عملية النشر على Hostinger"
echo "=========================================="

# الألوان للرسائل
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# دالة لطباعة رسالة نجاح
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# دالة لطباعة رسالة خطأ
error() {
    echo -e "${RED}✗ $1${NC}"
}

# دالة لطباعة رسالة تحذير
warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# التحقق من وجود PHP
if ! command -v php &> /dev/null; then
    error "PHP غير مثبت. الرجاء تثبيت PHP 8.2 أو أحدث"
    exit 1
fi
success "تم العثور على PHP"

# التحقق من وجود Composer
if ! command -v composer &> /dev/null; then
    error "Composer غير مثبت. الرجاء تثبيته أولاً"
    exit 1
fi
success "تم العثور على Composer"

# التحقق من إصدار PHP
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
success "إصدار PHP: $PHP_VERSION"

echo ""
echo "=========================================="
echo "1. تثبيت المكتبات"
echo "=========================================="

# تثبيت مكتبات Composer
echo "تثبيت مكتبات PHP..."
composer install --optimize-autoloader --no-dev
if [ $? -eq 0 ]; then
    success "تم تثبيت مكتبات PHP بنجاح"
else
    error "فشل تثبيت مكتبات PHP"
    exit 1
fi

# تثبيت مكتبات NPM
if command -v npm &> /dev/null; then
    echo "تثبيت مكتبات JavaScript..."
    npm install
    if [ $? -eq 0 ]; then
        success "تم تثبيت مكتبات JavaScript بنجاح"
    else
        warning "فشل تثبيت مكتبات JavaScript"
    fi
    
    echo "بناء assets..."
    npm run build
    if [ $? -eq 0 ]; then
        success "تم بناء assets بنجاح"
    else
        warning "فشل بناء assets"
    fi
else
    warning "NPM غير متوفر، تخطي تثبيت مكتبات JavaScript"
fi

echo ""
echo "=========================================="
echo "2. إعداد ملف البيئة"
echo "=========================================="

# التحقق من وجود ملف .env
if [ ! -f .env ]; then
    if [ -f .env.hostinger ]; then
        cp .env.hostinger .env
        success "تم نسخ .env من .env.hostinger"
    elif [ -f .env.example ]; then
        cp .env.example .env
        success "تم نسخ .env من .env.example"
    else
        error "لم يتم العثور على ملف .env.example أو .env.hostinger"
        exit 1
    fi
    warning "الرجاء تحديث معلومات قاعدة البيانات في ملف .env"
else
    success "ملف .env موجود"
fi

# توليد مفتاح التطبيق
if grep -q "APP_KEY=$" .env; then
    echo "توليد مفتاح التطبيق..."
    php artisan key:generate --force
    success "تم توليد مفتاح التطبيق"
else
    success "مفتاح التطبيق موجود مسبقاً"
fi

echo ""
echo "=========================================="
echo "3. إعداد قاعدة البيانات"
echo "=========================================="

# سؤال المستخدم عن تشغيل migrations
read -p "هل تريد تشغيل migrations؟ (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    if [ $? -eq 0 ]; then
        success "تم تشغيل migrations بنجاح"
    else
        error "فشل تشغيل migrations"
    fi
    
    # سؤال عن seeders
    read -p "هل تريد تشغيل seeders؟ (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan db:seed --force
        if [ $? -eq 0 ]; then
            success "تم تشغيل seeders بنجاح"
        else
            warning "فشل تشغيل seeders"
        fi
    fi
fi

echo ""
echo "=========================================="
echo "4. إعداد الصلاحيات"
echo "=========================================="

# تعيين الصلاحيات
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env
success "تم تعيين صلاحيات المجلدات"

echo ""
echo "=========================================="
echo "5. إنشاء Symbolic Link"
echo "=========================================="

# حذف symbolic link القديم إن وجد
if [ -L public/storage ]; then
    rm public/storage
fi

php artisan storage:link
if [ $? -eq 0 ]; then
    success "تم إنشاء symbolic link"
else
    warning "فشل إنشاء symbolic link"
fi

echo ""
echo "=========================================="
echo "6. تحسين الأداء"
echo "=========================================="

# مسح الـ cache القديم
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
success "تم مسح الـ cache القديم"

# تخزين الإعدادات مؤقتاً
php artisan config:cache
php artisan route:cache
php artisan view:cache
success "تم تخزين الإعدادات مؤقتاً"

# تحسين autoloader
composer dump-autoload --optimize
success "تم تحسين autoloader"

# cache Filament components
if command -v php artisan filament:cache-components &> /dev/null; then
    php artisan filament:cache-components
    success "تم cache مكونات Filament"
fi

echo ""
echo "=========================================="
echo "7. نسخ ملف .htaccess للإنتاج"
echo "=========================================="

if [ -f public/.htaccess.production ]; then
    # نسخ احتياطي من .htaccess الحالي
    if [ -f public/.htaccess ]; then
        cp public/.htaccess public/.htaccess.backup
        success "تم إنشاء نسخة احتياطية من .htaccess"
    fi
    
    cp public/.htaccess.production public/.htaccess
    success "تم نسخ إعدادات .htaccess للإنتاج"
else
    warning "ملف .htaccess.production غير موجود"
fi

echo ""
echo "=========================================="
echo "8. التحقق النهائي"
echo "=========================================="

# التحقق من المجلدات المطلوبة
REQUIRED_DIRS=("storage/app" "storage/framework" "storage/logs" "bootstrap/cache")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        success "المجلد $dir موجود"
    else
        error "المجلد $dir غير موجود"
    fi
done

# التحقق من الملفات المطلوبة
REQUIRED_FILES=(".env" "public/index.php" "artisan")
for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        success "الملف $file موجود"
    else
        error "الملف $file غير موجود"
    fi
done

echo ""
echo "=========================================="
echo "✓ اكتملت عملية النشر بنجاح!"
echo "=========================================="
echo ""
echo "الخطوات التالية:"
echo "1. تحديث معلومات قاعدة البيانات في ملف .env"
echo "2. تأكد من تحديث APP_URL في .env"
echo "3. قم بتفعيل SSL من لوحة تحكم Hostinger"
echo "4. أضف Cron Job لتشغيل المهام المجدولة:"
echo "   * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "للوصول إلى لوحة الإدارة:"
echo "https://yourdomain.com/admin"
echo "البريد: admin@newsarh.com"
echo "كلمة المرور: 12345678"
echo ""
warning "تذكر تغيير كلمة مرور المدير فوراً!"
echo ""
