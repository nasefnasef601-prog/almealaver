<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\PaymentRequest;
use App\Models\LessonCompletion;
use App\Models\AccessGrant;
use App\Models\Favorite;
use App\Models\ReviewLater;
use App\Models\SkillProgress;
use App\Models\ActivityLog;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Path;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'overview');

        // Enrolled Course IDs
        $enrolledCourseIds = AccessGrant::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('course_id')
            ->toArray();

        // Enrolled & Free Courses
        $courses = Course::whereIn('id', $enrolledCourseIds)
            ->orWhere('price', 0)
            ->withCount(['lessons' => fn($q) => $q->where('is_published', true)])
            ->with(['lessonCompletions' => fn($q) => $q->where('user_id', $user->id)])
            ->get();

        // --- OVERVIEW DATA ---
        $totalCourses = Course::whereIn('id', $enrolledCourseIds)->orWhere('price', 0)->count();
        $completedCourses = \App\Models\CourseCompletion::where('user_id', $user->id)->count();
        $attemptsCount = QuizAttempt::where('user_id', $user->id)->count();
        $avgScore = QuizResult::where('user_id', $user->id)->avg('score_percentage');
        $completedLessons = LessonCompletion::where('user_id', $user->id)->count();
        
        // Streak Tracking
        $activityDates = LessonCompletion::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as d')
            ->groupBy('d')
            ->pluck('d')
            ->merge(
                QuizAttempt::where('user_id', $user->id)
                    ->selectRaw('DATE(created_at) as d')
                    ->groupBy('d')
                    ->pluck('d')
            )->unique()->sort()->values();

        $streak = 0;
        $checkDate = now()->format('Y-m-d');
        foreach ($activityDates->reverse() as $ad) {
            if ($ad === $checkDate) {
                $streak++;
                $checkDate = Carbon::parse($checkDate)->subDay()->format('Y-m-d');
            } elseif ($ad === Carbon::parse($checkDate)->subDay()->format('Y-m-d')) {
                $streak++;
                $checkDate = Carbon::parse($ad)->format('Y-m-d');
            } else {
                break;
            }
        }

        // Last 7 days calendar
        $weekDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $weekDays[] = [
                'date' => $date,
                'day' => now()->subDays($i)->format('D'),
                'active' => $activityDates->contains($date),
                'isToday' => $i === 0,
            ];
        }

        $todayCompleted = LessonCompletion::where('user_id', $user->id)
            ->whereDate('created_at', now())->count();
        $todayQuizzes = QuizAttempt::where('user_id', $user->id)
            ->whereDate('created_at', now())->count();

        // Past 7 days quiz scores for mini chart
        $weeklyScores = QuizResult::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn($r) => $r->created_at->format('Y-m-d'))
            ->map(fn($day) => round($day->avg('score_percentage'), 1));

        // Weakest skills (max 3 for overview)
        $weakSkills = SkillProgress::where('user_id', $user->id)
            ->where('mastery', '<', 75)
            ->with('skill')
            ->orderBy('mastery')
            ->take(3)
            ->get();

        // --- SMART PATH DATA ---
        $smartPathRecommendations = [];
        if ($tab === 'smart-path' || $tab === 'overview') {
            $studentSkillGaps = SkillProgress::where('user_id', $user->id)
                ->where('mastery', '<', 75)
                ->with(['skill.section.subject'])
                ->orderBy('mastery')
                ->take(12)
                ->get();

            foreach ($studentSkillGaps as $idx => $sp) {
                $skill = $sp->skill;
                if (!$skill) continue;

                $priority = $sp->mastery < 50 ? 'high' : 'medium';
                
                // Find a lesson for this skill
                // We map skill to courses or lessons targeting it
                $lesson = Lesson::where('is_published', true)
                    ->where(function($q) use ($skill) {
                        $q->whereHas('course', function($c) use ($skill) {
                            $c->where('skill_id', $skill->id);
                        });
                    })
                    ->first();

                if ($lesson) {
                    $smartPathRecommendations[] = [
                        'id' => 'rec_l_' . $skill->id,
                        'type' => 'lesson',
                        'title' => 'مراجعة درس: ' . ($lesson->title_ar ?? $lesson->title),
                        'duration' => ($lesson->duration_minutes ?? 15) . ' دقيقة',
                        'reason' => 'لأن مستوى إتقانك لهذه المهارة (' . ($skill->name_ar ?? $skill->name) . ') هو ' . round((float)$sp->mastery) . '% وهو بحاجة لدعم.',
                        'skillTargeted' => $skill->name_ar ?? $skill->name,
                        'priority' => $priority,
                        'actionLabel' => 'ابدأ الدرس',
                        'link' => route('student.lesson.show', ['course' => $lesson->course_id, 'lesson' => $lesson->id]),
                    ];
                }

                // Find a quiz for this skill
                $quiz = Quiz::where('is_published', true)
                    ->where(function($q) use ($skill) {
                        $q->where('skill_id', $skill->id)
                          ->orWhereHas('questions', function($q2) use ($skill) {
                              $q2->where('skill_id', $skill->id);
                          });
                    })
                    ->first();

                if ($quiz) {
                    $smartPathRecommendations[] = [
                        'id' => 'rec_q_' . $skill->id,
                        'type' => 'quiz',
                        'title' => 'اختبار مهارة: ' . ($quiz->title_ar ?? $quiz->title),
                        'duration' => ($quiz->time_limit ?? 10) . ' دقيقة',
                        'reason' => 'قياس مدى تحسن مهارة ' . ($skill->name_ar ?? $skill->name) . ' بعد المذاكرة.',
                        'skillTargeted' => $skill->name_ar ?? $skill->name,
                        'priority' => $priority,
                        'actionLabel' => 'ابدأ الاختبار',
                        'link' => route('student.quiz.show', $quiz->id),
                    ];
                }

                // If no direct lesson or quiz is found, create a fallback recommendation
                if (!$lesson && !$quiz) {
                    $smartPathRecommendations[] = [
                        'id' => 'rec_f_' . $skill->id,
                        'type' => 'remediation',
                        'title' => 'مذاكرة وتأسيس: ' . ($skill->name_ar ?? $skill->name),
                        'duration' => '20 دقيقة',
                        'reason' => 'مراجعة المفاهيم الأساسية للمهارة وحل تدريبات كتابية.',
                        'skillTargeted' => $skill->name_ar ?? $skill->name,
                        'priority' => $priority,
                        'actionLabel' => 'مراجعة المهارة',
                        'link' => route('student.skills'),
                    ];
                }
            }
        }

        // --- SESSIONS DATA ---
        $bookedSessions = [];
        $liveSessions = [];
        if ($tab === 'sessions' || $tab === 'overview') {
            // Booked private sessions (stored as activity logs of action 'session_booked')
            $bookedSessions = ActivityLog::where('user_id', $user->id)
                ->where('action', 'session_booked')
                ->orderBy('created_at', 'desc')
                ->get();

            // Upcoming live meetings (lessons with live video types)
            $liveSessions = Lesson::whereIn('content_type', ['live_youtube', 'zoom', 'google_meet', 'teams'])
                ->where('is_published', true)
                ->where(function($q) use ($enrolledCourseIds) {
                    $q->whereIn('course_id', $enrolledCourseIds)
                      ->orWhereHas('course', function($c) {
                          $c->where('price', 0);
                      });
                })
                ->orderBy('meeting_date', 'asc')
                ->get();
        }

        // --- SAHER (TEST CENTER) DATA ---
        $saherQuizzes = [];
        $paths = [];
        $subjects = [];
        $sections = [];
        $skills = [];
        if ($tab === 'saher') {
            $saherQuizzes = Quiz::where('is_published', true)
                ->where('mode', 'saher')
                ->with('course', 'questions')
                ->latest()
                ->get();

            $paths = Path::where('is_active', true)->get();
            $subjects = Subject::where('is_active', true)->get();
            $sections = Section::where('is_active', true)->get();
            $skills = Skill::where('is_active', true)->get();
        }

        // --- ATTEMPTS/QUIZZES DATA ---
        $attemptsGrouped = [];
        if ($tab === 'quizzes') {
            $allAttempts = QuizAttempt::where('user_id', $user->id)
                ->with(['quiz.course', 'result'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Group attempts by Quiz ID
            $grouped = [];
            foreach ($allAttempts as $att) {
                $quiz = $att->quiz;
                $key = $quiz ? (string)$quiz->id : 'custom_' . $att->id;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'key' => $key,
                        'quiz_id' => $quiz?->id,
                        'quiz_title' => $quiz ? ($quiz->title_ar ?? $quiz->title) : 'اختبار مخصص',
                        'category' => ($quiz && $quiz->mode === 'mock_exam') ? 'mock' : 'regular',
                        'attempts' => [],
                        'latest_attempt' => null,
                        'best_score' => 0,
                    ];
                }
                $grouped[$key]['attempts'][] = $att;
                if ($att->result && $att->result->score_percentage > $grouped[$key]['best_score']) {
                    $grouped[$key]['best_score'] = $att->result->score_percentage;
                }
            }

            foreach ($grouped as $k => $data) {
                $data['latest_attempt'] = $data['attempts'][0];
                $attemptsGrouped[] = $data;
            }
        }

        // --- FAVORITES / REVIEW CENTER DATA ---
        $favQuestions = [];
        $reviewLaterQuestions = [];
        $mistakeQuestions = [];
        if ($tab === 'favorites') {
            // 1. Favorited Questions
            $favQuestionIds = Favorite::where('user_id', $user->id)
                ->where('favoriteable_type', Question::class)
                ->pluck('favoriteable_id')
                ->toArray();
            $favQuestions = Question::whereIn('id', $favQuestionIds)->get();

            // 2. Review Later Questions
            $reviewLaterIds = ReviewLater::where('user_id', $user->id)
                ->pluck('question_id')
                ->toArray();
            $reviewLaterQuestions = Question::whereIn('id', $reviewLaterIds)->get();

            // 3. Mistakes (Incorrect Questions)
            $completedAttempts = QuizAttempt::where('user_id', $user->id)
                ->where('status', 'completed')
                ->get(['answers', 'quiz_id']);

            $mistakeQuestionIds = [];
            foreach ($completedAttempts as $attempt) {
                $answers = $attempt->answers; // cast to array already
                if (!is_array($answers)) continue;

                // Load all questions of this quiz to verify correctness
                $quizQuestions = Question::where('quiz_id', $attempt->quiz_id)->get(['id', 'options', 'correct_answer']);
                foreach ($quizQuestions as $q) {
                    $selected = $answers[$q->id] ?? null;
                    if ($selected === null) continue;

                    $isCorrect = false;
                    if (isset($q->options[$selected])) {
                        $option = $q->options[$selected];
                        if (is_array($option)) {
                            $isCorrect = (!empty($option['is_correct']) && ($option['is_correct'] === true || $option['is_correct'] === 1 || $option['is_correct'] === '1' || $option['is_correct'] === 'true'));
                            if (!$isCorrect && isset($option['text'])) {
                                $isCorrect = (string)$option['text'] === (string)$q->correct_answer;
                            }
                            if (!$isCorrect && isset($option['text_ar'])) {
                                $isCorrect = (string)$option['text_ar'] === (string)$q->correct_answer;
                            }
                        } else {
                            $isCorrect = (string)$option === (string)$q->correct_answer;
                        }
                    } else {
                        $isCorrect = (string)$selected === (string)$q->correct_answer;
                    }

                    if (!$isCorrect) {
                        $mistakeQuestionIds[] = $q->id;
                    }
                }
            }
            $mistakeQuestionIds = array_unique($mistakeQuestionIds);
            $mistakeQuestions = Question::whereIn('id', $mistakeQuestionIds)->get();
        }

        // --- REQUESTS DATA ---
        $paymentRequests = [];
        if ($tab === 'requests' || $tab === 'payments') {
            $paymentRequests = PaymentRequest::where('user_id', $user->id)
                ->with('course')
                ->latest()
                ->get();
        }

        return view('student.dashboard', compact(
            'tab',
            'courses',
            'totalCourses',
            'completedCourses',
            'attemptsCount',
            'avgScore',
            'completedLessons',
            'streak',
            'weekDays',
            'todayCompleted',
            'todayQuizzes',
            'weeklyScores',
            'weakSkills',
            'smartPathRecommendations',
            'bookedSessions',
            'liveSessions',
            'saherQuizzes',
            'paths',
            'subjects',
            'sections',
            'skills',
            'attemptsGrouped',
            'favQuestions',
            'reviewLaterQuestions',
            'mistakeQuestions',
            'paymentRequests'
        ));
    }

    public function toggleReviewLater(Request $request)
    {
        $questionId = (int)$request->input('question_id');
        $question = Question::find($questionId);
        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        $marked = ReviewLater::toggle(auth()->id(), $questionId);
        return response()->json(['marked' => $marked]);
    }

    public function generateSaherQuiz(Request $request)
    {
        $user = auth()->user();
        
        $pathId = $request->input('path_id');
        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $skillId = $request->input('skill_id');
        $difficulty = $request->input('difficulty', 'medium');
        $questionCount = (int)$request->input('question_count', 15);
        $timeLimit = (int)$request->input('time_limit', 20);

        // Build pool query
        $query = Question::query();
        if ($pathId) {
            $query->whereHas('subject', function($q) use ($pathId) {
                $q->where('path_id', $pathId);
            });
        }
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        if ($skillId) {
            $query->where('skill_id', $skillId);
        }
        if ($difficulty) {
            $query->where('difficulty', strtolower($difficulty));
        }

        $questions = $query->inRandomOrder()->take($questionCount)->get();

        if ($questions->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد أسئلة كافية مطابقة للخيارات المحددة لتوليد الاختبار.');
        }

        // Dynamically create a custom Quiz for this attempt
        $quiz = Quiz::create([
            'title' => 'ساهر ذاتي - ' . ($difficulty === 'easy' ? 'سهل' : ($difficulty === 'hard' ? 'صعب' : 'متوسط')),
            'title_ar' => 'ساهر ذاتي - ' . ($difficulty === 'easy' ? 'سهل' : ($difficulty === 'hard' ? 'صعب' : 'متوسط')),
            'description' => 'اختبار ساهر تم توليده تلقائياً بناءً على مهاراتك المحددة.',
            'time_limit' => $timeLimit,
            'max_attempts' => 1,
            'is_published' => true,
            'mode' => 'saher',
        ]);

        // Attach questions
        foreach ($questions as $idx => $q) {
            $q->update(['quiz_id' => $quiz->id]);
        }

        // Create the attempt
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'attempt_number' => 1,
        ]);

        return redirect()->route('student.quiz.take', $attempt->id);
    }

    public function bookPrivateSession(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
        ]);

        $target = $request->input('target');
        $date = $request->input('date');
        $time = $request->input('time');
        $notes = $request->input('notes', '');

        // Log this booking in ActivityLog
        ActivityLog::log(
            'session_booked',
            "حجز حصة خاصة: {$target}",
            null,
            null,
            [
                'target' => $target,
                'date' => $date,
                'time' => $time,
                'notes' => $notes,
            ]
        );

        return redirect()->route('student.dashboard', ['tab' => 'sessions'])
            ->with('success', 'تم حجز الحصة الخاصة وإرسال الطلب للمدرس بنجاح.');
    }
}
