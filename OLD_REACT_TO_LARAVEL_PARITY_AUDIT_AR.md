# تدقيق نقل مشروع React/Node القديم إلى Laravel

تاريخ الجرد: 2026-06-09

## الهدف

تحويل مشروع `C:\ALMEAA MAY - codax` القديم إلى مشروع Laravel الحالي `C:\almeaa lave\almeaa-laravel-new` مع الحفاظ على التصميم والمنطق والوظائف والعلاقات قدر الإمكان، مع أولوية التشغيل الرخيص على Hostinger.

## ملخص الحالة

- Laravel شغال على Hostinger ومربوط بـ GitHub Auto Deploy.
- Laravel يغطي حاليا: الصفحة الرئيسية، التصنيفات، الدورات، دخول الطالب، لوحة طالب متعددة التبويبات، ساهر، الاختبارات، نتائج الطالب، الدفع اليدوي، الشهادات، إدارة Filament أساسية للمحتوى والمستخدمين والمدارس والمجموعات.
- مشروع React/Node القديم أوسع في الإدارة التشغيلية: مدارس/فصول/مشرفين، أكواد دخول، باقات B2B، مكتبة، اختبارات عامة بالباركود، لوحة عمليات، تكاملات AI، إشعارات متقدمة، دفع بخصومات ومزودي دفع، منتدى نقاش، مراجعة متباعدة، مراقبة إنتاج.

## خريطة صفحات React القديمة

| المسار القديم | الحالة في Laravel | قرار النقل |
| --- | --- | --- |
| `/` | موجود | مطابق جزئيا، يحتاج مطابقة بصرية أعمق لاحقا |
| `/dashboard` | موجود كـ `/student/dashboard` | أضيف تحويل قديم إلى لوحة الطالب |
| `/courses` | موجود | يعمل |
| `/course/:courseId` | موجود كـ `/courses/{course}` | أضيف تحويل قديم |
| `/quizzes` | موجود داخل الطالب | أضيف تحويل إلى `/student/quizzes` |
| `/my-quizzes` | موجود داخل تبويب الاختبارات | أضيف تحويل إلى تبويب سجل الاختبارات |
| `/my-requests` | موجود كتبوبيب المدفوعات | أضيف تحويل إلى تبويب المدفوعات |
| `/reports` | موجود داخل الطالب | أضيف تحويل إلى تبويب التقارير |
| `/favorites` | موجود داخل الطالب | أضيف تحويل إلى مركز مراجعة الأسئلة |
| `/plan` | موجود داخل الطالب | أضيف تحويل إلى تبويب خطتي |
| `/qa` | لا يوجد كصفحة مستقلة | مؤقتا يحول إلى مركز المراجعة، ويحتاج تنفيذ مركز أسئلة/نقاش مستقل |
| `/book-session`, `/live-sessions` | موجود كتبوبيب جلساتي | أضيف تحويل إلى تبويب الجلسات |
| `/profile` | موجود كـ `/student/profile` | أضيف تحويل |
| `/mock-exams` | موجود داخل الطالب | أضيف تحويل |
| `/achievements` | غير مكتمل | تحويل مؤقت للوحة الطالب، يحتاج badges/points |
| `/barcode-test/:slug` | غير موجود | فجوة عالية الأثر للمدارس والتسويق |
| `/certificate/:code` | Laravel لديه شهادة حسب course للطالب | يحتاج صفحة تحقق عامة بالكود |
| `/category/:pathId/:subjectId` | موجود بشكل `/category/{path}/subject/{subject}` | أضيف تحويل قديم |
| `/admin-dashboard` | موجود جزئيا عبر Filament `/admin` | يحتاج نقل رحلة الإدارة القديمة إلى صفحات/Widgets واضحة |
| `/supervisor-dashboard`, `/parent-dashboard`, `/instructor-dashboard` | صفحات Laravel بسيطة | فجوة كبيرة في اللوحات حسب الدور |

## خريطة كيانات القديم مقابل Laravel

| المجال | القديم React/Node | Laravel الحالي | الفجوة |
| --- | --- | --- | --- |
| المستخدمون | User مع أدوار، نقاط، badges، اشتراكات، مدارس، مجموعات، ولي أمر | User مع role, school, Spatie | ينقص points/badges/subscription/managed scopes بشكل واضح |
| المدارس والفصول | Group بأنواع SCHOOL/CLASS/PRIVATE_GROUP + supervisorIds/studentIds | School + Group أبسط | يحتاج رحلة موحدة: مدرسة -> فصول -> طلاب -> مشرفين -> باقة/مسارات -> أكواد -> تقرير |
| أكواد الدخول | AccessCode مستقل | غير موجود كنموذج مستقل | فجوة عالية |
| الباقات B2B | B2BPackage + contentTypes/pathIds/subjectIds | لا يوجد نموذج مكافئ واضح | فجوة عالية |
| صلاحيات الوصول | AccessGrant غني بمصدر ومنتجات ومسارات وانتهاء | AccessGrant موجود أبسط | يحتاج توسيع ليدعم package/contentTypes/pathIds/subjectIds/idempotency |
| الدفع | PaymentRequest مع خصومات ومحافظ وبوابات وwebhook | دفع يدوي ورفع إيصال | يحتاج DiscountCode ومزودات دفع لاحقا |
| المحتوى | Course فيه modules/assessments/files/packages/approval/owner | Course + Module + Lesson + Quiz | يحتاج ملفات الدورة، حزم، approval workflow، owner/teacher revenue |
| بنك الأسئلة | Question + Quiz settings/placements/mock | موجود جزئيا | يحتاج mock sections وplacements أوسع |
| الاختبارات العامة | PublicBarcodeTest + submissions | غير موجود | فجوة عالية للمدارس والاختبارات العامة |
| المكتبة | LibraryItem | غير موجود | فجوة متوسطة/عالية |
| النقاش | DiscussionThread/Reply | LessonQuestion فقط | يحتاج منتدى نقاش مرتبط بالدرس/الكورس/الاختبار |
| المراجعة المتباعدة | ReviewCard | Favorite/ReviewLater فقط | يحتاج خوارزمية spaced repetition |
| الإشعارات | Templates/Deliveries/Queue | Notification بسيط | يحتاج قوالب وتسليم وتتبع |
| العمليات والمراقبة | OperationsCommandCenter + health/readiness/client events | widgets وسجلات نشاط | يحتاج لوحة تشغيل عملية داخل Filament |
| AI | AiAssistantManager + routes/providers | غير موجود تقريبا | يحتاج إدارة مزود AI ومساعد داخل الإدارة/الطالب |

## أولويات التنفيذ

1. تثبيت توافق الروابط القديمة حتى لا تضيع أي روابط منشورة أو محفوظة.
2. نقل رحلة المدارس كاملة بشكل واضح داخل Laravel/Filament، لأن هذا أكبر فرق تجاري بين القديم والجديد.
3. إضافة أكواد الدخول والباقات B2B وربطها بـ AccessGrant.
4. إضافة الاختبارات العامة بالباركود لأنها مهمة للتسويق والمدارس.
5. توسيع الدفع: خصومات، محافظ/تحويل، سجل مراجعة، وإثباتات.
6. نقل المكتبة وملفات الدورة.
7. نقل لوحة العمليات والمراقبة بما يناسب Hostinger.
8. نقل AI لاحقا بطريقة اختيارية لا تكسر الاستضافة الرخيصة.

## ما تم في هذه الجولة

- تم جرد مسارات React القديمة الأساسية.
- تم جرد نماذج Node/Mongo الأساسية ومقارنتها بنماذج Laravel.
- تم إضافة تحويلات للروابط القديمة الأكثر استخداما داخل `routes/web.php`.
- هذا التقرير هو مرجع التنفيذ القادم، وليس إعلان اكتمال النقل الكامل.

## بوابات التحقق القادمة

- `php -l routes/web.php`
- `php artisan route:list` للتأكد من عدم كسر المسارات.
- فحص حي بعد النشر:
  - `/dashboard` يحول إلى `/student/dashboard`.
  - `/my-quizzes` يحول إلى تبويب الاختبارات.
  - `/course/1` يحول إلى `/courses/1`.
  - `/category/1/1` يحول إلى `/category/1/subject/1`.
