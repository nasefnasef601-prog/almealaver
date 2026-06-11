# تحديث تسليم - مركز مراجعة الأسئلة والمراجعة المتباعدة

التاريخ: 2026-06-11

## الهدف

تقريب مركز مراجعة الأسئلة في Laravel من مشروع React/Node القديم، خصوصا نموذج `ReviewCard` الذي كان يدعم المراجعة المتباعدة SM-2.

## ما تم

- توسيع جدول `review_laters` ليصبح بطاقة مراجعة وليس مجرد حفظ سؤال:
  - `ease_factor`
  - `interval_days`
  - `repetitions`
  - `next_review_at`
  - `last_quality`
- إضافة خوارزمية مراجعة متباعدة داخل `ReviewLater::applyReviewQuality()`.
- عند حفظ سؤال للمراجعة لاحقا يتم ضبط `next_review_at = now()` ليظهر كمستحق.
- إضافة مسار طالب لتسجيل جودة المراجعة:
  - `POST /student/review-later/{reviewLater}/answer`
- تحديث لوحة الطالب في تبويب "مركز مراجعة الأسئلة":
  - عداد مستحق اليوم.
  - عداد مستحق هذا الأسبوع.
  - إجمالي بطاقات المراجعة.
  - عرض حالة موعد البطاقة في تبويب "لاحقا".
  - أزرار جودة المراجعة: صعب، جيد، سهل.
- منع الطالب من تحديث بطاقة مراجعة تخص طالبا آخر.

## الملفات الأساسية

- `database/migrations/2026_06_11_000003_add_spaced_repetition_fields_to_review_laters_table.php`
- `app/Models/ReviewLater.php`
- `app/Http/Controllers/StudentDashboardController.php`
- `resources/views/student/dashboard.blade.php`
- `routes/web.php`
- `tests/Feature/ReviewLaterSpacedRepetitionTest.php`

## التحقق المحلي

- `php -l app/Models/ReviewLater.php`: نجح.
- `php -l app/Http/Controllers/StudentDashboardController.php`: نجح.
- `php -l tests/Feature/ReviewLaterSpacedRepetitionTest.php`: نجح.
- `git diff --check`: نجح.
- `php artisan migrate --pretend --no-interaction`: أظهر أعمدة المراجعة المتباعدة الجديدة وفهرس `review_laters_user_due_idx`.
- `php artisan test tests\Feature\ReviewLaterSpacedRepetitionTest.php`: مخرجات PHPUnit الداخلية أظهرت نجاح 3 اختبارات و12 تأكيدا، رغم أن wrapper أعاد exit code 1 كما يحدث في هذه البيئة.
- `php artisan test tests\Feature\CourseDiscussionTest.php`: مخرجات PHPUnit الداخلية أظهرت نجاح 7 اختبارات و21 تأكيدا.

## المتبقي

- فحص بصري فعلي لتبويب مركز مراجعة الأسئلة داخل المتصفح.
- تحسين العرض لاحقا بحيث يمكن إخفاء البطاقات غير المستحقة أو إضافة فلتر "المستحق فقط".
- ربط تلقائي أعمق من نتائج الاختبار: يمكن لاحقا إضافة الأسئلة الخاطئة مباشرة كبطاقات مراجعة، لكن هذه الدفعة أبقت السلوك الحالي "راجع لاحقا" مع جدولة ذكية.
