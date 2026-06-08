# قائمة الفحص للإنتاج — منصة المئة

## ✅ البيئة
- [ ] `APP_ENV=production` في `.env`
- [ ] `APP_DEBUG=false` في `.env`
- [ ] `APP_URL` مضبوط على النطاق الحقيقي
- [ ] MySQL مستخدم (ليس SQLite)
- [ ] `DB_DATABASE` تم إنشاؤه
- [ ] PHP 8.3+ مثبت مع extensions: `bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenxml, xml`

## ✅ الأمان
- [ ] `APP_KEY` تم توليده (`php artisan key:generate`)
- [ ] مجلد `storage/` غير قابل للوصول من المتصفح
- [ ] `.env` محمي من الوصول العام
- [ ] HTTPS مفعل عبر Let's Encrypt
- [ ] Rate limiting مفعل (`php artisan route:list | grep throttle`)

## ✅ الأداء
- [ ] `php artisan optimize` تم تشغيله
- [ ] `php artisan filament:optimize` تم تشغيله
- [ ] OpCache مفعل في php.ini
- [ ] Queue connection = `database` (أو Redis)
- [ ] Session = `file` (أو Redis) — ليس `array`

## ✅ قاعدة البيانات
- [ ] `php artisan migrate --force` تم تشغيله
- [ ] `php artisan db:seed --force` تم تشغيله
- [ ] MySQL مهيأ مع `utf8mb4` و `utf8mb4_unicode_ci`
- [ ] Backup مجدول

## ✅ الملفات
- [ ] `php artisan storage:link` تم تشغيله
- [ ] مجلد `storage/app/public/payment-receipts/` موجود
- [ ] أذونات `storage/` و `bootstrap/cache/` = 775

## ✅ المهام المجدولة
- [ ] Cron entry: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`

## ✅ الإشعارات
- [ ] Mail driver مضبوط (SMTP, Mailgun, etc.)
- [ ] `MAIL_FROM_ADDRESS` مضبوط
- [ ] `php artisan queue:work` للمهام غير المتزامنة

## ✅ الاختبارات
- [ ] جميع الصفحات العامة تفتح (login, register, pricing, courses)
- [ ] تسجيل الدخول كمدير يعمل (`/admin/login`)
- [ ] لوحة المدير تفتح (`/admin`)
- [ ] المستخدمين (admin/student/teacher/supervisor/parent) معزولين
- [ ] رحلة الطالب كاملة: تسجيل → dashboard → كورس → درس → اختبار → نتيجة

## ✅ بعد النشر
- [ ] اختبار مسار الطالب الكامل
- [ ] اختبار رفع إيصال الدفع
- [ ] اختبار قبول/رفض الدفع من المدير
- [ ] اختبار الإشعارات
- [ ] اختبار لوحة التقارير
