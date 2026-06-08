<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\CourseReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function submit(Request $request, Course $course)
    {
        $user = auth()->user();

        $completed = CourseCompletion::where('user_id', $user->id)
            ->where('course_id', $course->id)->exists();

        if (!$completed) {
            return back()->with('error', 'يجب إتمام الدورة أولاً قبل التقييم.');
        }

        $exists = CourseReview::where('user_id', $user->id)
            ->where('course_id', $course->id)->exists();

        if ($exists) {
            return back()->with('error', 'قمت بتقييم هذه الدورة من قبل.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        CourseReview::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'is_approved' => false,
        ]);

        return back()->with('success', 'تم إرسال تقييمك، بانتظار اعتماد الإدارة.');
    }
}
