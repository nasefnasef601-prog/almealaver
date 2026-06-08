<?php

namespace App\Http\Controllers;

use App\Models\Path;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MockExamController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للاختبارات المحاكية
     */
    public function index()
    {
        $user = Auth::user();
        $canSeeHiddenPaths = in_array($user->role ?? '', ['admin', 'teacher', 'supervisor']);

        // جلب المسارات النشطة
        $paths = Path::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $mockPathsData = [];

        foreach ($paths as $path) {
            $subjectIds = $path->subjects()->pluck('id');

            // جلب الاختبارات المحاكية التابعة لهذا المسار
            $pathMockExams = Quiz::with('questions')
                ->where('is_published', true)
                ->where('quiz_type', 'mock_exam')
                ->whereIn('subject_id', $subjectIds)
                ->get();

            // حساب الإحصائيات
            $totalQuestions = 0;
            $totalTime = 0;
            foreach ($pathMockExams as $quiz) {
                $totalQuestions += $quiz->questions->count();
                $totalTime += $quiz->time_limit ?? 60;
            }

            $mockPathsData[] = [
                'path' => $path,
                'exams_count' => $pathMockExams->count(),
                'total_questions' => $totalQuestions,
                'total_time' => $totalTime,
                'is_ready' => $pathMockExams->count() > 0,
            ];
        }

        return view('student.mock-exams.index', compact('mockPathsData'));
    }
}
