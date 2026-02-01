#!/bin/bash

###############################################################################
# سكريبت إنشاء حزمة النشر لـ Hostinger
# يقوم بإنشاء ملف مضغوط جاهز للرفع
###############################################################################

echo "=========================================="
echo "إنشاء حزمة النشر لـ Hostinger"
echo "=========================================="

# الألوان
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# اسم الحزمة
PACKAGE_NAME="newsarh-hostinger-$(date +%Y%m%d-%H%M%S)"
PACKAGE_DIR="./deployment-package"

echo ""
echo "اسم الحزمة: $PACKAGE_NAME"
echo ""

# إنشاء مجلد مؤقت
echo "1. إنشاء مجلد مؤقت..."
rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR"
echo -e "${GREEN}✓ تم إنشاء المجلد المؤقت${NC}"

# نسخ الملفات الأساسية
echo ""
echo "2. نسخ الملفات..."

# المجلدات الأساسية
DIRS_TO_COPY=(
    "app"
    "bootstrap"
    "config"
    "database"
    "public"
    "resources"
    "routes"
    "storage"
)

for dir in "${DIRS_TO_COPY[@]}"; do
    if [ -d "$dir" ]; then
        cp -r "$dir" "$PACKAGE_DIR/"
        echo -e "${GREEN}✓${NC} تم نسخ $dir"
    fi
done

# الملفات الأساسية
FILES_TO_COPY=(
    "artisan"
    "composer.json"
    "composer.lock"
    "package.json"
    "package-lock.json"
    ".env.hostinger"
    ".env.example"
    ".gitignore"
    ".gitattributes"
    ".editorconfig"
)

for file in "${FILES_TO_COPY[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$PACKAGE_DIR/"
        echo -e "${GREEN}✓${NC} تم نسخ $file"
    fi
done

# نسخ ملفات التوثيق
echo ""
echo "3. نسخ ملفات التوثيق..."
DOCS=(
    "README.md"
    "HOSTINGER_DEPLOYMENT.md"
    "DEPLOYMENT_CHECKLIST.md"
    "REQUIREMENTS.md"
    "COMPREHENSIVE_REPORT_AR.md"
)

for doc in "${DOCS[@]}"; do
    if [ -f "$doc" ]; then
        cp "$doc" "$PACKAGE_DIR/"
        echo -e "${GREEN}✓${NC} تم نسخ $doc"
    fi
done

# نسخ السكريبتات
echo ""
echo "4. نسخ السكريبتات..."
cp deploy.sh "$PACKAGE_DIR/"
chmod +x "$PACKAGE_DIR/deploy.sh"
echo -e "${GREEN}✓${NC} تم نسخ deploy.sh"

# نسخ قاعدة البيانات
echo ""
echo "5. نسخ قاعدة البيانات..."
if [ -f "full.sql" ]; then
    cp full.sql "$PACKAGE_DIR/"
    echo -e "${GREEN}✓${NC} تم نسخ full.sql"
fi

# تنظيف المجلدات
echo ""
echo "6. تنظيف الملفات غير الضرورية..."

# حذف ملفات التطوير
rm -rf "$PACKAGE_DIR/node_modules"
rm -rf "$PACKAGE_DIR/vendor"
rm -rf "$PACKAGE_DIR/.git"
rm -rf "$PACKAGE_DIR/tests"
rm -f "$PACKAGE_DIR/.env"

# تنظيف storage
find "$PACKAGE_DIR/storage" -type f -name "*.log" -delete
find "$PACKAGE_DIR/storage/framework/cache" -type f ! -name ".gitignore" -delete
find "$PACKAGE_DIR/storage/framework/sessions" -type f ! -name ".gitignore" -delete
find "$PACKAGE_DIR/storage/framework/views" -type f ! -name ".gitignore" -delete
find "$PACKAGE_DIR/storage/logs" -type f ! -name ".gitignore" -delete

echo -e "${GREEN}✓${NC} تم التنظيف"

# إنشاء ملف README للحزمة
echo ""
echo "7. إنشاء ملف README للحزمة..."
cat > "$PACKAGE_DIR/START_HERE.md" << 'EOF'
# البدء السريع - نشر نظام إدارة الموظفين

## 📦 محتويات الحزمة

هذه الحزمة تحتوي على جميع الملفات اللازمة لنشر نظام إدارة الموظفين على Hostinger.

## 🚀 خطوات النشر السريعة

### 1. رفع الملفات
ارفع جميع الملفات إلى مجلد `public_html` في استضافة Hostinger

### 2. إنشاء قاعدة البيانات
- أنشئ قاعدة بيانات MySQL من لوحة تحكم Hostinger
- استورد ملف `full.sql` عبر phpMyAdmin

### 3. إعداد ملف البيئة
```bash
cp .env.hostinger .env
nano .env  # حدّث معلومات قاعدة البيانات
```

### 4. تشغيل سكريبت النشر
```bash
chmod +x deploy.sh
./deploy.sh
```

## 📚 التوثيق الكامل

- **دليل النشر الشامل:** `HOSTINGER_DEPLOYMENT.md`
- **قائمة المراجعة:** `DEPLOYMENT_CHECKLIST.md`
- **المتطلبات:** `REQUIREMENTS.md`
- **التقرير الشامل:** `COMPREHENSIVE_REPORT_AR.md`

## 🔑 الوصول الافتراضي

**لوحة الإدارة:** https://yourdomain.com/admin
- البريد: admin@newsarh.com
- كلمة المرور: 12345678

⚠️ **مهم:** غيّر كلمة المرور فوراً بعد أول تسجيل دخول!

## 📞 الدعم

للمساعدة، راجع الملفات التوثيقية أو اتصل بالدعم الفني.

---
**تاريخ الإنشاء:** $(date +"%Y-%m-%d %H:%M:%S")
EOF

echo -e "${GREEN}✓${NC} تم إنشاء START_HERE.md"

# ضغط الحزمة
echo ""
echo "8. ضغط الحزمة..."
cd "$PACKAGE_DIR"
zip -r "../$PACKAGE_NAME.zip" . -q
cd ..
echo -e "${GREEN}✓${NC} تم إنشاء $PACKAGE_NAME.zip"

# حساب الحجم
SIZE=$(du -h "$PACKAGE_NAME.zip" | cut -f1)
echo ""
echo -e "${GREEN}=========================================="
echo "✓ تم إنشاء الحزمة بنجاح!"
echo "==========================================${NC}"
echo ""
echo "📦 اسم الملف: $PACKAGE_NAME.zip"
echo "📊 الحجم: $SIZE"
echo "📂 الموقع: $(pwd)/$PACKAGE_NAME.zip"
echo ""
echo "الخطوة التالية:"
echo "1. ارفع ملف $PACKAGE_NAME.zip إلى Hostinger"
echo "2. فك الضغط في public_html"
echo "3. اتبع التعليمات في START_HERE.md"
echo ""
echo -e "${YELLOW}⚠️  لا تنسَ:${NC}"
echo "  - تحديث معلومات قاعدة البيانات في .env"
echo "  - تغيير كلمة مرور المدير"
echo "  - تفعيل SSL"
echo ""

# حذف المجلد المؤقت
rm -rf "$PACKAGE_DIR"

echo "تم!"
