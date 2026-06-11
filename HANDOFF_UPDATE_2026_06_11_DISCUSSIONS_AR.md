# تحديث تسليم - نقاشات الدورات ومطابقة جزء من مشروع Node القديم

التاريخ: 2026-06-11

## ما تم

- إضافة نسخة Laravel أولى من نظام النقاشات الموجود في مشروع React/Node القديم.
- إضافة جداول:
  - `discussion_threads`
  - `discussion_replies`
- إضافة نماذج:
  - `App\Models\DiscussionThread`
  - `App\Models\DiscussionReply`
- إضافة صفحة طالب للدورة:
  - `/student/courses/{course}/discussions`
- دعم:
  - عرض نقاشات الدورة.
  - إنشاء نقاش جديد.
  - الرد على نقاش.
  - إغلاق نقاش الطالب كمنتهي.
  - عداد الردود.
  - ترتيب المثبت أولا ثم الأحدث.
- ربط خفيف من صفحة تفاصيل الدورة إلى صفحة النقاشات.
- تصحيح تحقق وصول صفحة تفاصيل الدورة ليستخدم `AccessGrant::userHasCourseAccess()` ويدعم منح `course_ids` المخزنة بصيغة JSON، وليس `course_id` فقط.

## سبب الأهمية

المشروع القديم يحتوي على `DiscussionThread` و`DiscussionReply` كجزء من تجربة التعلم. هذه الدفعة تنقل هذا الجزء إلى Laravel بطريقة مناسبة للاستضافة الرخيصة، بدون الاعتماد على Node أو MongoDB.

## التحقق المحلي

- `php -l app/Http/Controllers/DiscussionController.php`: نجح.
- `php -l app/Models/DiscussionThread.php`: نجح.
- `php -l app/Models/DiscussionReply.php`: نجح.
- `php -l tests/Feature/CourseDiscussionTest.php`: نجح.
- `php artisan migrate --pretend --no-interaction`: أظهر SQL صحيحا لجداول النقاشات على SQLite.
- `.\vendor\bin\phpunit tests/Feature/CourseDiscussionTest.php`: نجح.
  - الاختبارات: 3
  - التأكيدات: 8

## ما بقي قبل إعلان المطابقة الكاملة

- نقل صلاحيات النقاش للمعلم/المشرف في واجهاتهم، وليس الطالب فقط.
- إضافة إدارة Filament للنقاشات إذا كانت الإدارة تريد المراجعة والحذف والتثبيت من اللوحة.
- توسيع النقاشات لتدعم `lesson` و`quiz` مثل المشروع القديم، وليس `course` فقط.
- فحص بصري عبر المتصفح بعد تشغيل السيرفر المحلي أو بعد النشر.

## تحديث إضافي - إدارة Filament

- تمت إضافة مورد Filament لإدارة النقاشات:
  - `App\Filament\Resources\DiscussionThreadResource`
  - `App\Filament\Resources\DiscussionThreadResource\Pages\ListDiscussionThreads`
- تمت إضافة المورد إلى `AdminPanelProvider` ليظهر داخل لوحة `/admin`.
- الإدارة تستطيع الآن:
  - عرض نقاشات الدورات.
  - البحث حسب العنوان أو الدورة أو الكاتب.
  - التصفية حسب الدورة وحالة التثبيت وحالة الحل.
  - تثبيت النقاش وإلغاء تثبيته.
  - تعليم النقاش كمحلول أو إعادة فتحه.
  - إضافة رد إداري يظهر كـ `is_instructor_reply`.
  - حذف النقاش عند الحاجة.

## تحقق إضافي

- `php -l app/Filament/Resources/DiscussionThreadResource.php`: نجح.
- `php -l app/Filament/Resources/DiscussionThreadResource/Pages/ListDiscussionThreads.php`: نجح.
- `php -l app/Providers/Filament/AdminPanelProvider.php`: نجح.
- `git diff --check`: نجح مع تحذير Git عن تحويل CRLF إلى LF في `AdminPanelProvider.php`.
- `php artisan test tests\Feature\CourseDiscussionTest.php`: مخرجات PHPUnit الداخلية أظهرت نجاح 3 اختبارات و8 تأكيدات، رغم أن wrapper أعاد exit code 1 كما حدث سابقا.

## المتبقي بعد هذا التحديث

- واجهة نقاشات مخصصة للمعلم/المشرف خارج Filament إذا كان مطلوبا أن يردوا من لوحة الدور الخاصة بهم.
- دعم نقاشات `lesson` و`quiz` بنفس بنية المشروع القديم.
- فحص بصري للوحة الإدارة وصفحة الطالب بعد تشغيل السيرفر أو النشر.

## تحديث إضافي - دعم الدرس والاختبار

- تم توسيع نظام النقاشات من `course` فقط إلى:
  - `course`
  - `lesson`
  - `quiz`
- تمت إضافة مسارات الطالب:
  - `/student/courses/{course}/lessons/{lesson}/discussions`
  - `/student/quiz/{quiz}/discussions`
  - `/student/discussions/{thread}/replies`
  - `/student/discussions/{thread}/resolve`
- صفحة النقاشات أصبحت مشتركة وتختار مسار الإنشاء والعودة حسب نوع المحتوى الحالي.
- تمت إضافة رابط "نقاشات الدرس" داخل صفحة الدرس.
- تمت إضافة رابط "نقاشات الاختبار" داخل صفحة معلومات الاختبار.
- تمت إضافة علاقات `discussionThreads()` إلى:
  - `Lesson`
  - `Quiz`
- تم تصحيح import ناقص في `LessonController` لـ `CourseCompletion` لأنه قد يسبب خطأ عند فتح درس مرتبط بإكمال دورة.

## تحقق إضافي بعد دعم الدرس والاختبار

- `php -l app/Http/Controllers/DiscussionController.php`: نجح.
- `php -l app/Http/Controllers/LessonController.php`: نجح.
- `php -l app/Models/Lesson.php`: نجح.
- `php -l app/Models/Quiz.php`: نجح.
- `php -l tests/Feature/CourseDiscussionTest.php`: نجح.
- `git diff --check`: نجح مع نفس تحذير CRLF/LF في `AdminPanelProvider.php`.
- `php artisan test tests\Feature\CourseDiscussionTest.php`: مخرجات PHPUnit الداخلية أظهرت نجاح 5 اختبارات و12 تأكيدا، رغم أن wrapper أعاد exit code 1 كما حدث سابقا.

## المتبقي بعد دعم الدرس والاختبار

- فحص بصري فعلي عبر المتصفح لصفحات:
  - نقاشات الدورة.
  - نقاشات الدرس.
  - نقاشات الاختبار.
  - مورد Filament لإدارة النقاشات.
- إذا كان مطلوبا مطابقة أعمق للمشروع القديم: إضافة upvotes وقبول إجابة محددة `is_accepted_answer` من واجهة الطالب/الإدارة.

## تحديث إضافي - التصويت وقبول الإجابة

- تمت إضافة تصويت للنقاشات والردود بطريقة خفيفة مناسبة لـ SQLite وMySQL:
  - `discussion_threads.upvoter_ids`
  - `discussion_replies.upvoter_ids`
  - تحديث `upvotes_count` تلقائيا عند التصويت أو إلغائه.
- تمت إضافة مسارات الطالب:
  - `POST /student/discussions/{thread}/upvote`
  - `POST /student/discussion-replies/{reply}/upvote`
  - `POST /student/discussion-replies/{reply}/accept`
- تمت إضافة أزرار التصويت داخل صفحة النقاشات.
- تمت إضافة قبول الرد كإجابة:
  - يسمح لصاحب النقاش أو الإدارة/المعلم المرتبط بالدورة.
  - يجعل الرد `is_accepted_answer = true`.
  - يجعل النقاش `is_resolved = true`.
  - يلغي قبول أي رد آخر في نفس النقاش قبل قبول الرد الجديد.
- تمت إضافة عمود `upvotes_count` داخل مورد Filament لنقاشات الدورات.

## تحقق إضافي بعد التصويت وقبول الإجابة

- `php -l app/Http/Controllers/DiscussionController.php`: نجح.
- `php -l app/Models/DiscussionThread.php`: نجح.
- `php -l app/Models/DiscussionReply.php`: نجح.
- `php -l tests/Feature/CourseDiscussionTest.php`: نجح.
- `php -l app/Filament/Resources/DiscussionThreadResource.php`: نجح.
- `git diff --check`: نجح مع نفس تحذير CRLF/LF في `AdminPanelProvider.php`.
- `php artisan migrate --pretend --no-interaction`: أظهر أعمدة `upvoter_ids` في جداول النقاشات والردود.
- `php artisan test tests\Feature\CourseDiscussionTest.php`: مخرجات PHPUnit الداخلية أظهرت نجاح 7 اختبارات و21 تأكيدا، رغم أن wrapper أعاد exit code 1 كما حدث سابقا.

## حالة فجوة النقاشات بعد هذا التحديث

- فجوة نقاشات مشروع Node القديم أصبحت مغطاة كنسخة Laravel أولى في:
  - الدورة.
  - الدرس.
  - الاختبار.
  - الردود التعليمية.
  - التثبيت والحل من الإدارة.
  - التصويت.
  - قبول الإجابة.
- المتبقي قبل إعلانها مغلقة بالكامل: فحص بصري فعلي عبر Browser/Chrome أو بعد النشر، والتأكد أن تجربة التصميم مطابقة بصريا بما يكفي.
