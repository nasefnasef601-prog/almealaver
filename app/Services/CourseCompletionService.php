<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\CourseCompletedMail;
use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\QuizResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CourseCompletionService
{
    public function checkAndComplete(int $userId, int $courseId): ?CourseCompletion
    {
        $exists = CourseCompletion::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($exists) {
            return $exists;
        }

        $course = Course::with(['modules.lessons', 'quizzes'])->findOrFail($courseId);

        if (!$course->is_published) {
            return null;
        }

        $totalLessons = $course->modules->flatMap(fn($m) => $m->lessons)->count();

        if ($totalLessons === 0) {
            return null;
        }

        $completedLessons = $course->lessonCompletions()
            ->where('user_id', $userId)
            ->count();

        if ($completedLessons < $totalLessons) {
            return null;
        }

        $totalQuizzes = $course->quizzes()->where('is_published', true)->count();

        if ($totalQuizzes > 0) {
            $passedQuizzes = QuizResult::where('user_id', $userId)
                ->whereIn('quiz_id', $course->quizzes()->pluck('id'))
                ->where('passed', true)
                ->distinct('quiz_id')
                ->count('quiz_id');

            if ($passedQuizzes < $totalQuizzes) {
                return null;
            }
        }

        $completion = CourseCompletion::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'completed_at' => now(),
            'certificate_code' => strtoupper(Str::random(16)),
        ]);

        try {
            $user = $completion->user;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new CourseCompletedMail($completion));
            }
        } catch (\Exception $e) {
            report($e);
        }

        return $completion;
    }
}
