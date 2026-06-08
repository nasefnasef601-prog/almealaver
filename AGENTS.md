# Project Guide for AI Agents — منصة المئة

## Stack
- Laravel 12 + Filament v5.6.6
- PHP 8.3.30
- Blade + Alpine.js (Livewire)
- SQLite (dev), MySQL (prod)
- Spatie Permission v6
- RTL + Tajawal font

## Directory Structure
- `app/Providers/Filament/AdminPanelProvider.php` — Admin panel config
- `app/Filament/Resources/` — Admin CRUD resources
- `app/Filament/Pages/` — Custom admin pages
- `app/Filament/Widgets/` — Admin dashboard widgets
- `resources/views/student/` — Student frontend views
- `resources/views/layouts/` — Layout components
- `routes/web.php` — All routes

## Key Conventions
- **Filament v5** uses `Filament\Actions\*` NOT `Filament\Tables\Actions\*`
- **Filament v5** uses `Filament\Schemas\Schema` NOT `Filament\Forms\Form`
- RoleMiddleware checks both `$user->role` column AND `$user->hasRole()` Spatie
- All database queries must work on SQLite (no MySQL-only functions like `FIELD()`)
- Use `CASE WHEN ... THEN ... ELSE ... END` instead of `FIELD()` for custom ordering

## Roles
- `admin`: Full access to Filament panel
- `student`: Student dashboard only
- `teacher`: Teacher dashboard + some content management
- `supervisor`: Supervisor dashboard + school scope
- `parent`: Parent dashboard only

## Admin Panel Access
- Path: `/admin`
- Login: `->login()` with email/password
- Auth middleware: `['role:admin']`
- Non-admin users get 403

## Course Completion System
- `CourseCompletion` model + `course_completions` table tracks completed courses
- `CourseCompletionService::checkAndComplete()` auto-detects when all lessons + quizzes done
- Called after lesson completion (`LessonController::complete`) and quiz submission (`QuizController::submit`)
- Requires ALL published lessons completed AND all quizzes passed (score >= passing_score)
- Generates unique `certificate_code` (16 chars uppercase)
- Sends `CourseCompletedMail` on completion
- Certificate page at `student/certificate/{course}` (HTML, browser-printable)
- Receipt page at `student/receipt/{payment}` (HTML, browser-printable)

## Email System
- Mail driver: `log` (emails in `storage/logs/laravel.log`)
- Mailables: `PaymentApproved`, `PaymentRejected`, `CourseCompletedMail`
- Sent from: `PaymentRequestResource` (approve/reject actions) and `CourseCompletionService`
- To switch to SMTP: change `MAIL_MAILER` in `.env`

## Feature Tests
- `tests/Feature/CourseCompletionTest.php` — Tests auto-completion, idempotency, quiz requirement
- `tests/Feature/PaymentFlowTest.php` — Tests request purchase, receipt upload, isolation
- `tests/Feature/QuizFlowTest.php` — Tests quiz start, submit, scoring, attempt limits
- Also tests lesson completion triggers course check
- NOTE: Very slow on SQLite `:memory:` due to RefreshDatabase + seeding. Use MySQL for testing.

## Common Issues & Fixes
- **Dashboard timeout**: Check widgets for N+1 queries (UserRegistrationsChart, QuizCompletionChart)
- **500 on all pages**: Check `AppServiceProvider.php` for missing `use App\Models\Path;`
- **Filament Component not found**: Check for deprecated `filament-panels::form.actions` in blade files
- **SQLite FIELD() error**: Replace with `CASE WHEN`
- **php artisan slow on SQLite**: Normal. Views caching can take 50+ seconds. Use MySQL for production.

## Test Commands
```bash
# Lint
php -l app/.../File.php

# Test (slow on SQLite :memory: — use MySQL for speed)
.\vendor\bin\phpunit tests/Feature/SmokeTest.php

# Run all tests
.\vendor\bin\phpunit

# Optimize
php artisan optimize
php -d max_execution_time=120 artisan serve --port=8000

# Health check
php artisan check:health

# Check app
php artisan tinker
```

## Important Files
- `EVIDENCE_INDEX.md` — Full evidence index
- `FINAL_DELIVERY_REPORT_AR.md` — Final report
- `GAP_ANALYSIS_AR.md` — Gap analysis (Arabic)
- `DEPLOYMENT_GUIDE.md` — Production deployment guide
- `PRODUCTION_CHECKLIST.md` — Production checklist
