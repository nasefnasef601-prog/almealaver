<?php

use App\Http\Controllers\AuthController;
use App\Models\HomepageSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = HomepageSetting::getActive();
    return view('public.landing', ['settings' => $settings?->data]);
})->name('home');

Route::get('/courses', function () {
    return view('public.courses');
})->name('courses');

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

Route::get('/search/query', [\App\Http\Controllers\SearchController::class, 'query'])->name('search.query');

// Public path/subject pages
Route::get('/category/{path}', function (App\Models\Path $path) {
    return view('public.category', ['path' => $path]);
})->name('category');

Route::get('/category/{path}/subject/{subject}', function (App\Models\Path $path, App\Models\Subject $subject) {
    return view('public.subject-learning', ['path' => $path, 'subject' => $subject]);
})->name('category.subject');

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
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');

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
        $modelMap = ['course' => \App\Models\Course::class, 'lesson' => \App\Models\Lesson::class];
        $modelClass = $modelMap[$type] ?? null;
        if (!$modelClass) return response()->json(['error' => 'Invalid type'], 400);
        $isFav = \App\Models\Favorite::toggle(auth()->id(), $modelClass, $id);
        return response()->json(['favorited' => $isFav]);
    })->name('favorite.toggle');

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
