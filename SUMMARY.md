## Goal
- مطابقة المشروع الجديد (Laravel Blade) للمشروع القديم (React SPA) بصريًا ووظيفيًا، مع محتوى ديناميكي من DB

## Constraints & Preferences
- لا تعديل المشروع القديم
- لا حذف أو إعادة إنشاء من الصفر
- SQLite للتطوير المحلي مؤقتًا
- Filament للأدمن فقط
- كل المحتوى ديناميكي من DB
- Alpine.js للتفاعل في الـ Modal والـ Sidebar
- Tajawal font + RTL

## Progress

### ✅ Completed
- إصلاح خطأ `Class "Filament\Tables\Actions\EditAction" not found` في 10 Resources — تم تغيير الاستيراد إلى `Filament\Actions\EditAction`
- إعادة كتابة `landing.blade.php` لتطابق تصميم القديم (Hero بصورة + فلووتنج كارد، Stats بـ 4 أعمدة، Paths cards، كورسات، Why Choose، Testimonials، CTA)
- إنشاء `HomepageSetting` model + migration (JSON column) + seeder (بقيم افتراضية من القديم)
- تحديث `routes/web.php` لتمرير `$settings` من DB إلى landing view
- تحديث `app.blade.php` Header: قائمة تنقل، يوزر مينو dropdown، Login modal، زر بحث/سلة
- إضافة أنيميشن CSS (blob, float, bounce-slow) في `app.css`
- Vite build يمر بنجاح
- Landing page ترجع 200
- إعادة كتابة student dashboard + sidebar لتطابق تصميم القديم (8 tabs: الرئيسية/دوراتي/اختبارات/تقارير/نتائج/مدفوعات/خطة/مفضلة)
- إصلاح query الطالب (source_id → course_id في access_grants)
- إنشاء Filament Page `ManageHomepage` (إدارة محتوى اللاندينج من الأدمن)
- إضافة route للـ course-detail العام

### ⏳ In Progress
- (none)

### 📋 Next Steps
1. تجربة فتح http://localhost:8000 والتأكد من كل الصفحات
2. إنشاء صفحة `public.course-detail` إذا كانت مفقودة
3. فتح /admin والتأكد من ظهور صفحة ManageHomepage
4. تشغيل seeder: `php artisan db:seed --class=HomepageSettingSeeder`

## Key Decisions
- استخدام model واحد (`HomepageSetting`) مع JSON column لتخزين كل محتوى اللاندينج
- الاعتماد على `HomepageSetting::getActive()` لجلب الإعدادات النشطة
- الاحتفاظ بقيم افتراضية في الـ Blade إذا كانت الـ DB فاضية

## Relevant Files
- `resources/views/public/landing.blade.php` — الصفحة الرئيسية (ديناميكية)
- `resources/views/layouts/app.blade.php` — Header عام + Login modal
- `resources/views/layouts/student.blade.php` — Layout الطالب مع Sidebar
- `resources/views/student/dashboard.blade.php` — لوحة الطالب (8 tabs)
- `resources/css/app.css` — أنيميشنات + Tailwind
- `app/Models/HomepageSetting.php` — Model المحتوى الديناميكي
- `app/Filament/Pages/ManageHomepage.php` — صفحة إدارة المحتوى في الأدمن
- `database/seeders/HomepageSettingSeeder.php` — Seeder
- `routes/web.php` — كل المسارات
