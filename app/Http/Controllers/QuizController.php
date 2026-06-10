<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\AccessGrant;
use App\Services\CourseCompletionService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $enrolledCourseIds = AccessGrant::activeCourseIdsForUser($user->id);

        $quizzes = Quiz::with('course', 'questions')
            ->where('is_published', true)
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds)
                  ->orWhereNull('course_id');
            })
            ->latest()
            ->get();

        return view('student.quizzes', compact('quizzes'));
    }

    public function show(Quiz $quiz)
    {
        if (!$quiz->is_published) {
            return redirect()->route('student.dashboard')->with('error', 'هذا الاختبار غير متاح.');
        }

        $user = auth()->user();
        $attemptsCount = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        $lastAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        $canRetake = is_null($quiz->max_attempts) || $attemptsCount < $quiz->max_attempts;
        $bestScore = QuizResult::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->max('score_percentage');

        return view('student.quiz-info', compact('quiz', 'attemptsCount', 'lastAttempt', 'canRetake', 'bestScore'));
    }

    public function start(Quiz $quiz)
    {
        if (!$quiz->is_published) {
            return redirect()->route('student.dashboard')->with('error', 'هذا الاختبار غير متاح.');
        }

        $user = auth()->user();
        $attemptsCount = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        if ($quiz->max_attempts && $attemptsCount >= $quiz->max_attempts) {
            return redirect()->route('student.quiz.result', $quiz->id)
                ->with('error', 'لقد استنفذت محاولاتك لهذا الاختبار.');
        }

        $activeAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if ($activeAttempt) {
            return redirect()->route('student.quiz.take', $activeAttempt->id);
        }

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'attempt_number' => $attemptsCount + 1,
        ]);

        return redirect()->route('student.quiz.take', $attempt->id);
    }

    public function take(QuizAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($attempt->status === 'completed') {
            return redirect()->route('student.quiz.result', $attempt->quiz_id);
        }

        $quiz = $attempt->quiz()->with('questions')->firstOrFail();
        $questions = $quiz->randomize_questions ? $quiz->questions->shuffle() : $quiz->questions;
        $timeLimit = $quiz->time_limit;

        return view('student.quiz-take', compact('quiz', 'questions', 'attempt', 'timeLimit'));
    }

    public function submit(Request $request, QuizAttempt $attempt, CourseCompletionService $completionService)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($attempt->status === 'completed') {
            return redirect()->route('student.quiz.result', $attempt->quiz_id);
        }

        $quiz = $attempt->quiz()->with(['questions.skill.section.subject'])->firstOrFail();
        $answers = $request->input('answers');
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        } else {
            $answers = $answers ?? [];
        }
        $timeTaken = $request->input('time_taken_seconds', 0);

        $score = 0;
        $total = $quiz->questions->count();
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;

        $skillStats = [];

        $reviewData = [];
        foreach ($quiz->questions as $question) {
            $selected = $answers[$question->id] ?? null;
            $isCorrect = false;
            if ($selected !== null) {
                if (isset($question->options[$selected])) {
                    $option = $question->options[$selected];
                    if (is_array($option)) {
                        $isCorrect = (!empty($option['is_correct']) && ($option['is_correct'] === true || $option['is_correct'] === 1 || $option['is_correct'] === '1' || $option['is_correct'] === 'true'));
                        if (!$isCorrect && isset($option['text'])) {
                            $isCorrect = (string)$option['text'] === (string)$question->correct_answer;
                        }
                        if (!$isCorrect && isset($option['text_ar'])) {
                            $isCorrect = (string)$option['text_ar'] === (string)$question->correct_answer;
                        }
                    } else {
                        $isCorrect = (string)$option === (string)$question->correct_answer;
                    }
                } else {
                    $isCorrect = (string)$selected === (string)$question->correct_answer;
                }
            }

            if ($selected === null) {
                $unansweredCount++;
            } elseif ($isCorrect) {
                $correctCount++;
                $score += $question->points ?: 1;
            } else {
                $incorrectCount++;
            }

            // Build per-skill stats
            if ($question->skill_id) {
                if (!isset($skillStats[$question->skill_id])) {
                    $skill = $question->skill;
                    $skillStats[$question->skill_id] = [
                        'skill_id' => $question->skill_id,
                        'skill_name' => $skill?->name_ar ?? $skill?->name ?? 'مهارة غير مسماة',
                        'section_name' => $skill?->section?->name_ar ?? '',
                        'subject_name' => $skill?->section?->subject?->name_ar ?? '',
                        'correct' => 0,
                        'total' => 0,
                    ];
                }
                $skillStats[$question->skill_id]['total']++;
                if ($isCorrect) {
                    $skillStats[$question->skill_id]['correct']++;
                }
            }

            $reviewData[] = [
                'id' => $question->id,
                'text' => $question->question_text_ar ?? $question->question_text,
                'options' => $question->options,
                'selected' => $selected,
                'correct' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation_ar ?? $question->explanation,
                'points' => $question->points ?: 1,
            ];
        }

        $skillBreakdown = [];
        foreach ($skillStats as $stat) {
            $mastery = $stat['total'] > 0 ? round(($stat['correct'] / $stat['total']) * 100, 1) : 0;
            $skillBreakdown[] = array_merge($stat, [
                'mastery' => $mastery,
                'status' => $mastery >= 80 ? 'strong' : ($mastery >= 60 ? 'average' : 'weak'),
            ]);
        }

        $totalPoints = $quiz->questions->sum(fn($q) => $q->points ?: 1);
        $scorePct = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 1) : 0;
        $passed = $scorePct >= ($quiz->passing_score ?? 50);

        $attempt->update([
            'status' => 'completed',
            'completed_at' => now(),
            'score' => $score,
            'total_points' => $totalPoints,
            'passed' => $passed,
            'answers' => $answers,
            'time_taken_seconds' => $timeTaken,
        ]);

        $result = QuizResult::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'attempt_id' => $attempt->id,
            'score_percentage' => $scorePct,
            'passed' => $passed,
            'total_questions' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'skill_breakdown' => $skillBreakdown,
            'completed_at' => now(),
        ]);

        // Update SkillProgress for each skill
        $userId = auth()->id();
        foreach ($skillBreakdown as $sb) {
            $skillId = $sb['skill_id'] ?? null;
            if (!$skillId) continue;

            $progress = \App\Models\SkillProgress::firstOrNew([
                'user_id' => $userId,
                'skill_id' => $skillId,
            ]);

            $progress->total_attempts++;
            $progress->correct_answers += $sb['correct'] ?? 0;
            $progress->total_questions += $sb['total'] ?? 0;
            $progress->mastery = $progress->total_questions > 0
                ? round(($progress->correct_answers / $progress->total_questions) * 100, 1)
                : 0;
            $progress->status = $progress->mastery >= 80 ? 'mastered'
                : ($progress->mastery >= 60 ? 'good'
                : ($progress->mastery >= 40 ? 'average' : 'weak'));
            $progress->last_quiz_id = $quiz->id;
            $progress->last_quiz_title = $quiz->title_ar ?? $quiz->title;
            $progress->last_attempt_at = now();
            $progress->save();
        }

        \App\Models\Notification::create([
            'user_id' => auth()->id(),
            'type' => 'quiz',
            'title' => 'تم إكمال الاختبار',
            'title_ar' => 'تم إكمال الاختبار',
            'body' => "أنهيت اختبار \"{$quiz->title_ar}\" بنسبة {$scorePct}%",
            'body_ar' => "أنهيت اختبار \"{$quiz->title_ar}\" بنسبة {$scorePct}%",
            'data' => [
                'quiz_id' => $quiz->id,
                'score' => $scorePct,
                'passed' => $passed,
            ],
        ]);

        if ($quiz->course_id) {
            $completionService->checkAndComplete(auth()->id(), $quiz->course_id);
        }

        return redirect()->route('student.quiz.result', ['quiz' => $quiz->id, 'attempt' => $attempt->id]);
    }

    public function result(Quiz $quiz, ?int $attemptId = null)
    {
        $user = auth()->user();

        if ($attemptId) {
            $attempt = QuizAttempt::where('id', $attemptId)
                ->where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->firstOrFail();
        } else {
            $attempt = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->where('status', 'completed')
                ->latest()
                ->firstOrFail();
        }

        $result = QuizResult::where('attempt_id', $attempt->id)->firstOrFail();
        $questions = $quiz->questions()->get();

        $reviewData = [];
        foreach ($questions as $question) {
            $selected = $attempt->answers[$question->id] ?? null;
            $isCorrect = false;
            if ($selected !== null) {
                if (isset($question->options[$selected])) {
                    $option = $question->options[$selected];
                    if (is_array($option)) {
                        $isCorrect = (!empty($option['is_correct']) && ($option['is_correct'] === true || $option['is_correct'] === 1 || $option['is_correct'] === '1' || $option['is_correct'] === 'true'));
                        if (!$isCorrect && isset($option['text'])) {
                            $isCorrect = (string)$option['text'] === (string)$question->correct_answer;
                        }
                        if (!$isCorrect && isset($option['text_ar'])) {
                            $isCorrect = (string)$option['text_ar'] === (string)$question->correct_answer;
                        }
                    } else {
                        $isCorrect = (string)$option === (string)$question->correct_answer;
                    }
                } else {
                    $isCorrect = (string)$selected === (string)$question->correct_answer;
                }
            }
            $reviewData[] = [
                'id' => $question->id,
                'text' => $question->question_text_ar ?? $question->question_text,
                'options' => $question->options,
                'selected' => $selected,
                'correct' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation_ar ?? $question->explanation,
                'points' => $question->points ?: 1,
            ];
        }

        return view('student.quiz-result', compact('quiz', 'attempt', 'result', 'reviewData'));
    }
}
