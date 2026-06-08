# دليل الإنجاز — مشروع منصة المئة (Laravel Filament)

## 1. الهدف
بناء منصة تعليمية متكاملة (بديل Laravel للمشروع المرجعي React SPA) مع:
- رحلة طالب كاملة (تسجيل، كورسات، دروس، اختبارات، نتائج، مهارات)
- لوحة مدير متكاملة (Filament v5)
- أدوار وصلاحيات (Spatie Permission + RoleMiddleware)
- دفع يدوي مع رفع إيصالات
- تقارير متقدمة وتحليلات

## 2. التقنية
| العنصر | الاختيار |
|--------|----------|
| الإطار | Laravel 12 + Filament v5.6.6 |
| PHP | 8.3.30 |
| قاعدة البيانات | SQLite (تطوير) / MySQL (إنتاج) |
| الواجهات | Blade + Alpine.js (Livewire) |
| الصلاحيات | Spatie Permission v6 |
| الخط | Tajawal |
| الاتجاه | RTL |

## 3. الأدوار والصلاحيات
- **admin**: كل الصلاحيات
- **teacher**: كوروسات، دروس، اختبارات، أسئلة، مهارات
- **supervisor**: كورسات، دروس، اختبارات، أسئلة، مستخدمين، مدارس، تقارير
- **student**: فقط مسار الطالب
- **parent**: فقط لوحة ولي الأمر

## 4. الملفات الرئيسية

### الإعدادات
| الملف | الوصف |
|-------|-------|
| `bootstrap/app.php` | تسجيل middleware aliases (role, school.scope) |
| `.env` | إعدادات البيئة (DB, mail, app) |
| `phpunit.xml` | إعدادات الاختبارات (SQLite :memory:) |

### الأدمن (Filament)
| الملف | الوصف |
|-------|-------|
| `app/Providers/Filament/AdminPanelProvider.php` | إعداد لوحة المدير |
| `app/Filament/Resources/UserResource.php` | إدارة المستخدمين |
| `app/Filament/Resources/CourseResource.php` | إدارة الكورسات مع RelationManagers |
| `app/Filament/Resources/CourseResource/RelationManagers/CourseModulesRelationManager.php` | إدارة الموديولات داخل الكورس |
| `app/Filament/Resources/CourseResource/RelationManagers/LessonsRelationManager.php` | إدارة الدروس داخل الكورس |
| `app/Filament/Resources/QuizResource.php` | إدارة الاختبارات |
| `app/Filament/Resources/QuestionResource.php` | إدارة الأسئلة (Repeater للخيارات) |
| `app/Filament/Resources/AccessGrantResource.php` | إدارة صلاحيات الوصول |
| `app/Filament/Resources/NotificationResource.php` | إدارة الإشعارات + ViewNotification page |
| `app/Filament/Resources/PaymentRequestResource.php` | إدارة طلبات الدفع (approve/reject) |
| `app/Filament/Widgets/StatsOverviewWidget.php` | ملخص الإحصائيات |
| `app/Filament/Widgets/UserRegistrationsChart.php` | رسم بياني للتسجيلات (محسّن) |
| `app/Filament/Widgets/QuizCompletionChart.php` | رسم بياني للاختبارات (محسّن) |
| `app/Filament/Widgets/PendingPaymentsWidget.php` | طلبات الدفع المعلقة |
| `app/Filament/Pages/Reports.php` | صفحة التقارير مع 8 KPIs + charts |
| `app/Filament/Pages/ManageHomepage.php` | إعدادات الصفحة الرئيسية |

### الصلاحيات والحماية
| الملف | الوصف |
|-------|-------|
| `app/Http/Middleware/RoleMiddleware.php` | فحص الدور (role column + Spatie) |
| `app/Http/Middleware/SchoolScopeMiddleware.php` | نطاق المدرسة للمشرفين |
| `app/Policies/CoursePolicy.php` | صلاحيات الكورسات |
| `app/Policies/UserPolicy.php` | صلاحيات المستخدمين |
| `app/Policies/QuizPolicy.php` | صلاحيات الاختبارات |
| `app/Providers/AppServiceProvider.php` | تسجيل policies + view composer |

### رحلة الطالب
| الملف | الوصف |
|-------|-------|
| `app/Http/Controllers/AuthController.php` | تسجيل الدخول/خروج، استعادة كلمة المرور |
| `app/Http/Controllers/QuizController.php` | عرض، بدء، تقديم، نتائج الاختبارات |
| `app/Http/Controllers/LessonController.php` | عرض وإكمال الدروس |
| `app/Http/Controllers/PaymentController.php` | طلب شراء مع رفع إيصال |
| `app/Http/Controllers/NotificationController.php` | الإشعارات |
| `routes/web.php` | جميع مسارات الموقع |
| `resources/views/student/dashboard.blade.php` | لوحة الطالب (stats, courses, quizzes, reports, favorites, streak) |
| `resources/views/student/course-detail.blade.php` | تفاصيل الكورس مع الدفع |
| `resources/views/student/lesson-show.blade.php` | عرض الدرس (فيديو/نص/PDF) |
| `resources/views/student/quiz-show.blade.php` | معلومات الاختبار |
| `resources/views/student/quiz-take.blade.php` | حل الاختبار (Alpine.js quizApp) |
| `resources/views/student/quiz-result.blade.php` | نتيجة الاختبار مع confetti إذا ≥80% |
| `resources/views/student/skills.blade.php` | مركز المهارات (محسّن لـ SQLite) |
| `resources/views/student/reports.blade.php` | تقارير الطالب |
| `resources/views/layouts/student.blade.php` | الهيكل الأساسي للطالب |

### Seeders
| الملف | الوصف |
|-------|-------|
| `database/seeders/RoleAndPermissionSeeder.php` | أدوار وصلاحيات (findOrCreate — idempotent) |
| `database/seeders/DemoDataSeeder.php` | بيانات تجريبية (يتخطى إن وُجد admin) |
| `database/seeders/DatabaseSeeder.php` | المستدعي الرئيسي |

## 5. حسابات تجريبية
| الدور | البريد | كلمة المرور |
|-------|--------|-------------|
| Admin | admin@demo.local | Demo123456! |
| Student | student@demo.local | Demo123456! |
| Teacher | teacher@demo.local | Demo123456! |
| Supervisor | supervisor@demo.local | Demo123456! |
| Parent | parent@demo.local | Demo123456! |

## 6. الإصلاحات الرئيسية

### a. N+1 Query في الرسوم البيانية
- **المشكلة**: `UserRegistrationsChart` و `QuizCompletionChart` يرسلان 30 استعلام منفصل (واحد لكل يوم)
- **الحل**: استعلام `GROUP BY DATE(created_at)` واحد
- **ملفات**: `app/Filament/Widgets/UserRegistrationsChart.php`, `app/Filament/Widgets/QuizCompletionChart.php`

### b. مكون Filament v3 قديم
- **المشكلة**: `manage-homepage.blade.php` يستخدم `<x-filament-panels::form.actions>` (غير موجود في v5)
- **الحل**: إزالة المرجع القديم
- **ملف**: `resources/views/filament/pages/manage-homepage.blade.php`

### c. استيراد Path مفقود
- **المشكلة**: `AppServiceProvider.php` يستخدم `Path::where(...)` بدون استيراد
- **النتيجة**: `Class "App\Providers\Path" not found` — كل الصفحات 500
- **الحل**: إضافة `use App\Models\Path;`
- **ملف**: `app/Providers/AppServiceProvider.php`

### d. Namespaces Filament v3 → v5
- **المشكلة**: 19 ملف تستخدم `Filament\Tables\Actions\*` (غير موجود في v5)
- **الحل**: تغيير إلى `Filament\Actions\*`
- **الملفات**: جميع Resources تحت `app/Filament/Resources/` و RelationManagers

### e. MySQL FIELD() على SQLite
- **المشكلة**: `skills.blade.php` يستخدم `FIELD(status, 'weak', 'average', ...)` (MySQL only)
- **الحل**: استبدال بـ `CASE WHEN ... THEN ... ELSE ... END`
- **ملف**: `resources/views/student/skills.blade.php`

## 7. المستندات
| الملف | الوصف |
|-------|-------|
| `GAP_ANALYSIS_AR.md` | تحليل الفجوات الكامل |
| `EVIDENCE_INDEX.md` | دليل الإنجاز (هذا الملف) |
