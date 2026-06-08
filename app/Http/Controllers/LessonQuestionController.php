<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use Illuminate\Http\Request;

class LessonQuestionController extends Controller
{
    public function ask(Request $request, Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) abort(404);

        $validated = $request->validate([
            'question' => 'required|string|max:2000',
        ]);

        LessonQuestion::create([
            'lesson_id' => $lesson->id,
            'user_id' => auth()->id(),
            'question' => $validated['question'],
        ]);

        return back()->with('success', 'تم إرسال سؤالك.');
    }

    public function answer(Request $request, Course $course, Lesson $lesson, LessonQuestion $question)
    {
        if ($lesson->course_id !== $course->id) abort(404);
        if ($question->lesson_id !== $lesson->id) abort(404);

        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || $user->hasRole('admin');
        $isTeacher = $user->role === 'teacher' && $course->assigned_teacher_id === $user->id;

        if (!$isAdmin && !$isTeacher) {
            return back()->with('error', 'فقط المدرس أو المشرف يمكنه الإجابة.');
        }

        $validated = $request->validate([
            'answer' => 'required|string|max:5000',
        ]);

        $question->update([
            'answer' => $validated['answer'],
            'answered_by' => $user->id,
            'answered_at' => now(),
        ]);

        return back()->with('success', 'تم إرسال الإجابة.');
    }
}
