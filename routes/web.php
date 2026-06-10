<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicBarcodeTestController;
use App\Models\HomepageSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = HomepageSetting::getActive();
    return view('public.landing', ['settings' => $settings?->data]);
})->name('home');

Route::get('/courses', function () {
    return view('public.courses');
})->name('courses');

Route::redirect('/about', '/')->name('legacy.about');
Route::redirect('/blog', '/courses')->name('legacy.blog');
Route::redirect('/privacy', '/')->name('legacy.privacy');
Route::redirect('/terms', '/')->name('legacy.terms');
Route::redirect('/cart', '/pricing')->name('legacy.cart');
Route::redirect('/checkout', '/pricing')->name('legacy.checkout');
Route::redirect('/signup', '/register')->name('legacy.signup');
Route::redirect('/verify-email', '/login')->name('legacy.verify-email');
Route::redirect('/admin-dashboard', '/admin')->name('legacy.admin-dashboard');
Route::redirect('/admin/quiz-gen', '/admin/quizzes/create')->name('legacy.admin.quiz-gen');
Route::redirect('/instructor-dashboard', '/teacher/dashboard')->name('legacy.instructor-dashboard');
Route::redirect('/supervisor-dashboard', '/supervisor/dashboard')->name('legacy.supervisor-dashboard');
Route::redirect('/parent-dashboard', '/parent/dashboard')->name('legacy.parent-dashboard');
Route::redirect('/quiz', '/quizzes')->name('legacy.quiz');
Route::redirect('/quiz/{quizId}', '/student/quiz/{quizId}')->name('legacy.quiz.show');
Route::redirect('/section/{catId}', '/dashboard')->name('legacy.section');
Route::redirect('/category/{pathId}/packages', '/pricing')->name('legacy.category.packages');
Route::get('/barcode-test/{slug}', [PublicBarcodeTestController::class, 'show'])->name('public.barcode-test.show');
Route::post('/barcode-test/{slug}', [PublicBarcodeTestController::class, 'submit'])->name('public.barcode-test.submit');

Route::get('/pricing', function () {
    return view('public.pricing');
})->name('pricing');

Route::get('/faq', function () {
    $faqs = \App\Models\Faq::published()->get()->groupBy('category');
    return view('public.faq', compact('faqs'));
})->name('faq');

Route::get('/contact', function () {
    return view('public.contact');
})->name('contact');

Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');

Route::get('/courses/{course}', function (App\Models\Course $course) {
    return view('public.course-detail', ['course' => $course]);
})->name('course-detail');

// Legacy React route aliases kept during the Node/React -> Laravel transition.
Route::redirect('/dashboard', '/student/dashboard')->name('legacy.dashboard');
Route::redirect('/my-quizzes', '/student/dashboard?tab=quizzes')->name('legacy.my-quizzes');
Route::redirect('/my-requests', '/student/dashboard?tab=payments')->name('legacy.my-requests');
Route::redirect('/reports', '/student/dashboard?tab=reports')->name('legacy.reports');
Route::redirect('/favorites', '/student/dashboard?tab=favorites')->name('legacy.favorites');
Route::redirect('/plan', '/student/dashboard?tab=plan')->name('legacy.plan');
Route::redirect('/qa', '/student/dashboard?tab=favorites')->name('legacy.qa');
Route::redirect('/book-session', '/student/dashboard?tab=sessions')->name('legacy.book-session');
Route::redirect('/live-sessions', '/student/dashboard?tab=sessions')->name('legacy.live-sessions');
Route::redirect('/profile', '/student/profile')->name('legacy.profile');
Route::redirect('/mock-exams', '/student/mock-exams')->name('legacy.mock-exams');
Route::redirect('/quizzes', '/student/quizzes')->name('legacy.quizzes');
Route::redirect('/results', '/student/results')->name('legacy.results');
Route::redirect('/achievements', '/student/dashboard')->name('legacy.achievements');
Route::redirect('/review', '/student/dashboard?tab=favorites')->name('legacy.review');
Route::get('/course/{course}', function (App\Models\Course $course) {
    return redirect()->route('course-detail', $course);
})->name('legacy.course');

Route::get('/certificate/{code}', function (string $code) {
    $completion = \App\Models\CourseCompletion::with(['course', 'user'])
        ->where('certificate_code', $code)
        ->firstOrFail();

    $studentName = e($completion->user?->name ?? 'غير معروف');
    $courseName = e($completion->course?->title_ar ?? $completion->course?->title ?? 'غير معروف');
    $certificateCode = e($completion->certificate_code);
    $completedAt = optional($completion->completed_at)->format('Y-m-d') ?? '—';

    return response()->make(<<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>التحقق من الشهادة</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f9fafb; color: #111827; }
        main { max-width: 900px; margin: 0 auto; padding: 48px 16px; }
        section { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .eyebrow { color: #059669; font-size: 14px; font-weight: 700; }
        h1 { margin: 8px 0 12px; font-size: 34px; line-height: 1.2; }
        p { margin: 0; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 24px; background: #f9fafb; padding: 20px; border-radius: 20px; }
        .item p:first-child { font-size: 12px; color: #6b7280; font-weight: 700; }
        .item p:last-child { margin-top: 4px; font-size: 18px; font-weight: 800; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } h1 { font-size: 28px; } }
    </style>
</head>
<body>
<main>
    <section>
        <p class="eyebrow">شهادة مؤكدة</p>
        <h1>التحقق من رمز الشهادة</h1>
        <p>هذه الصفحة تعرض التحقق العام من رمز الشهادة الموجود في النظام.</p>
        <div class="grid">
            <div class="item"><p>رمز الشهادة</p><p>{$certificateCode}</p></div>
            <div class="item"><p>الطالب</p><p>{$studentName}</p></div>
            <div class="item"><p>الدورة</p><p>{$courseName}</p></div>
            <div class="item"><p>تاريخ الإكمال</p><p>{$completedAt}</p></div>
        </div>
    </section>
</main>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
})->name('certificate.verify');

Route::get('/search/query', [\App\Http\Controllers\SearchController::class, 'query'])->name('search.query');

// Public path/subject pages
Route::get('/category/{path}', function (App\Models\Path $path) {
    return view('public.category', ['path' => $path]);
})->name('category');

Route::get('/category/{path}/subject/{subject}', function (App\Models\Path $path, App\Models\Subject $subject) {
    return view('public.subject-learning', ['path' => $path, 'subject' => $subject]);
})->name('category.subject');

Route::get('/category/{path}/{subject}', function (App\Models\Path $path, App\Models\Subject $subject) {
    return redirect()->route('category.subject', ['path' => $path, 'subject' => $subject]);
})->name('legacy.category.subject');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/access-code', [\App\Http\Controllers\AccessCodeController::class, 'show'])->name('access-code.show');
    Route::post('/access-code', [\App\Http\Controllers\AccessCodeController::class, 'redeem'])->name('access-code.redeem');

    Route::get('/profile', [\App\Http\Controllers\AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [\App\Http\Controllers\AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\AuthController::class, 'updatePassword'])->name('profile.password');

    Route::get('/courses', function () {
        return view('student.courses');
    })->name('courses');

    Route::get('/courses/{course}', function (\App\Models\Course $course) {
        return view('student.course-detail', ['courseId' => $course->id]);
    })->name('course-detail');

    Route::post('/payment-request', [\App\Http\Controllers\PaymentController::class, 'requestPurchase'])
        ->name('payment-request');

    Route::get('/payments', function () {
        return view('student.payments');
    })->name('payments');

    Route::get('/reports', function () {
        return view('student.reports');
    })->name('reports');

    Route::get('/export/progress', [\App\Http\Controllers\ExportController::class, 'progressCsv'])->name('export.progress');

    Route::get('/results', function () {
        return view('student.results');
    })->name('results');

    Route::get('/skills', function () {
        return view('student.skills');
    })->name('skills');

    Route::get('/skills/{skill}', function (\App\Models\Skill $skill) {
        return view('student.skill-detail', ['skill' => $skill]);
    })->name('skill.detail');

    Route::get('/leaderboard', function () {
        return view('student.leaderboard');
    })->name('leaderboard');

    Route::post('/favorite/toggle', function (\Illuminate\Http\Request $request) {
        $type = $request->input('type');
        $id = $request->input('id');
        $modelMap = [
            'course' => \App\Models\Course::class,
            'lesson' => \App\Models\Lesson::class,
            'question' => \App\Models\Question::class
        ];
        $modelClass = $modelMap[$type] ?? null;
        if (!$modelClass) return response()->json(['error' => 'Invalid type'], 400);
        $isFav = \App\Models\Favorite::toggle(auth()->id(), $modelClass, $id);
        return response()->json(['favorited' => $isFav]);
    })->name('favorite.toggle');

    Route::post('/review-later/toggle', [\App\Http\Controllers\StudentDashboardController::class, 'toggleReviewLater'])->name('review-later.toggle');
    Route::post('/saher/generate', [\App\Http\Controllers\StudentDashboardController::class, 'generateSaherQuiz'])->name('saher.generate');
    Route::post('/sessions/book', [\App\Http\Controllers\StudentDashboardController::class, 'bookPrivateSession'])->name('sessions.book');

    Route::get('/quizzes', [\App\Http\Controllers\QuizController::class, 'index'])->name('quiz.list');
    Route::get('/quiz/{quiz}', [\App\Http\Controllers\QuizController::class, 'show'])->name('quiz.show');
    Route::get('/quiz/{quiz}/start', [\App\Http\Controllers\QuizController::class, 'start'])->name('quiz.start');
    Route::get('/quiz/attempt/{attempt}', [\App\Http\Controllers\QuizController::class, 'take'])->name('quiz.take');
    Route::post('/quiz/attempt/{attempt}/submit', [\App\Http\Controllers\QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz/{quiz}/result', [\App\Http\Controllers\QuizController::class, 'result'])->name('quiz.result');
    Route::get('/quiz/{quiz}/result/{attempt}', [\App\Http\Controllers\QuizController::class, 'result'])->name('quiz.result.attempt');
    Route::get('/mock-exams', [\App\Http\Controllers\MockExamController::class, 'index'])->name('mock-exams');

    Route::post('/courses/{course}/review', [\App\Http\Controllers\ReviewController::class, 'submit'])->name('review.submit');

    Route::get('/courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'show'])->name('lesson.show');
    Route::post('/courses/{course}/lessons/{lesson}/complete', [\App\Http\Controllers\LessonController::class, 'complete'])->name('lesson.complete');
    Route::post('/courses/{course}/lessons/{lesson}/question', [\App\Http\Controllers\LessonQuestionController::class, 'ask'])->name('lesson.question.ask');
    Route::post('/courses/{course}/lessons/{lesson}/question/{question}/answer', [\App\Http\Controllers\LessonQuestionController::class, 'answer'])->name('lesson.question.answer');

    // Notifications
    Route::get('/certificate/{course}', function (\App\Models\Course $course) {
        $completion = \App\Models\CourseCompletion::where('user_id', auth()->id())
            ->where('course_id', $course->id)->firstOrFail();
        return view('student.certificate', compact('course', 'completion'));
    })->name('certificate');

    Route::get('/receipt/{payment}', function (\App\Models\PaymentRequest $payment) {
        if ($payment->user_id !== auth()->id()) abort(403);
        return view('student.payment-receipt', compact('payment'));
    })->name('receipt');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::get('/notifications/dropdown', [\App\Http\Controllers\NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Teacher routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', function () {
        return view('teacher.dashboard');
    })->name('dashboard');
});

// Supervisor routes
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', function () {
        return view('supervisor.dashboard');
    })->name('dashboard');
});

// Parent routes
Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', function () {
        return view('parent.dashboard');
    })->name('dashboard');
});
