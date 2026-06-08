<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Services\CourseCompletionService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        $user = auth()->user();
        $hasAccess = $course->is_free || \App\Models\AccessGrant::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess && !$lesson->is_free) {
            return redirect()->route('student.course-detail', $course->id)
                ->with('error', 'عليك الاشتراك في الكورس أولاً.');
        }

        $isCompleted = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        $course->load(['modules.lessons' => fn($q) => $q->where('is_published', true)->orderBy('sort_order')]);
        $allLessons = $course->modules->flatMap(fn($m) => $m->lessons);
        $currentIndex = $allLessons->search(fn($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        $courseCompletion = CourseCompletion::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        return view('student.lesson', compact('course', 'lesson', 'isCompleted', 'prevLesson', 'nextLesson', 'currentIndex', 'allLessons', 'courseCompletion'));
    }

    public function complete(Course $course, Lesson $lesson, CourseCompletionService $completionService)
    {
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        $user = auth()->user();

        $existing = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'تم إلغاء إكمال الدرس.');
        }

        LessonCompletion::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'course_id' => $course->id,
        ]);

        $completionService->checkAndComplete($user->id, $course->id);

        return back()->with('success', 'تم إكمال الدرس ✓');
    }
}
