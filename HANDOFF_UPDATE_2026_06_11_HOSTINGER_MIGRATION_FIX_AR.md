# تحديث إصلاح Hostinger بعد آخر نشر - 2026-06-11

## المشكلة

بعد رفع آخر دفعات GitHub، كان Laravel يعمل على الدومين، لكن صفحة:

- `/category/1/subject/1`

كانت ترجع 500.

## السبب

تشغيل `php artisan migrate --force` على MySQL في Hostinger فشل بسبب أسماء indexes طويلة في بعض migrations:

- `public_barcode_submissions_public_barcode_test_id_created_at_index`
- `library_items_path_id_subject_id_section_id_show_on_platform_index`

كما أن أول محاولة migration أنشأت بعض الجداول جزئيا قبل فشل تسجيل migration.

## الإصلاح

- تم تقصير اسم index في:
  - `database/migrations/2026_06_09_000003_create_public_barcode_submissions_table.php`
  - `database/migrations/2026_06_10_000006_create_library_items_table.php`
- تم جعل migration تتجاوز الجدول إذا كان موجودا مسبقا بسبب محاولة migration جزئية.
- تم رفع الإصلاح إلى GitHub:
  - `13e57b7 Shorten public barcode submission index`
  - `db43ba7 Handle existing barcode submissions table`
  - `2fcd5f9 Shorten library item migration indexes`
- تم رفع ملفات migration المصححة مباشرة إلى Hostinger عبر FTP لضمان التطبيق الفوري.
- تم تشغيل أوامر الإنتاج عبر ملف PHP مؤقت محمي ثم حذفه:
  - `php artisan migrate --force`
  - `php artisan optimize:clear`
  - `php artisan optimize`

## نتيجة التشغيل على Hostinger

- `2026_06_10_000006_create_library_items_table`: DONE
- `2026_06_10_000007_create_study_plans_table`: DONE
- `optimize:clear`: DONE
- `optimize`: DONE
- تم حذف ملف التشغيل المؤقت، ولا توجد ملفات `_codex*` متبقية داخل `public`.

## الفحص الحي بعد الإصلاح

- `/`: 200
- `/admin/login`: 200
- `/student/dashboard`: 302 إلى `/login` لغير المسجل، وهذا متوقع
- `/category/1`: 200
- `/category/1/subject/1`: 200
- `/.env`: 403
- `/app`: 403
- `/vendor`: 403
- `/storage/logs/laravel.log`: 404 ولا يظهر للمتصفح

## ملاحظة أمان

مفاتيح GitHub وبيانات Hostinger التي ظهرت أثناء العمل يجب تدويرها بعد انتهاء التسليم النهائي.
