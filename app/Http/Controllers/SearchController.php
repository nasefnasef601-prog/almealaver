<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function query(Request $request)
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json([
                'courses' => [],
                'lessons' => [],
                'quizzes' => []
            ]);
        }

        $courses = Course::where('is_published', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('title_ar', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('description_ar', 'like', "%{$q}%");
            })
            ->take(5)
            ->get(['id', 'title', 'title_ar', 'price', 'thumbnail']);

        $lessons = Lesson::where('is_published', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('title_ar', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('description_ar', 'like', "%{$q}%");
            })
            ->take(5)
            ->get(['id', 'course_id', 'title', 'title_ar', 'content_type']);

        $quizzes = Quiz::where('is_published', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('title_ar', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('description_ar', 'like', "%{$q}%");
            })
            ->take(5)
            ->get(['id', 'course_id', 'title', 'title_ar', 'quiz_type']);

        return response()->json([
            'courses' => $courses,
            'lessons' => $lessons,
            'quizzes' => $quizzes
        ]);
    }
}
