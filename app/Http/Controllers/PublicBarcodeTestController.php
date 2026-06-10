<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PublicBarcodeSubmission;
use App\Models\PublicBarcodeTest;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicBarcodeTestController extends Controller
{
    public function show(string $slug)
    {
        $test = PublicBarcodeTest::query()
            ->available()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_if($test->hasReachedSubmissionLimit(), 410);

        return view('public.barcode-test', [
            'test' => $test,
            'questions' => $test->questionsForTaking(),
            'submission' => null,
        ]);
    }

    public function submit(Request $request, string $slug)
    {
        $test = PublicBarcodeTest::query()
            ->available()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_if($test->hasReachedSubmissionLimit(), 410);

        $rules = [
            'student_name' => ['required', 'string', 'max:255'],
            'answers' => ['nullable', 'array'],
        ];

        if ($test->collect_school) {
            $rules['school_name'] = ['required', 'string', 'max:255'];
        }

        if ($test->collect_classroom) {
            $rules['classroom'] = ['required', 'string', 'max:255'];
        }

        $data = $request->validate($rules);
        $answers = $data['answers'] ?? [];
        $questions = $test->questionsForTaking();
        $result = $this->grade($questions, $answers, (float) $test->settingsValue('passingScore', 60));

        $submission = PublicBarcodeSubmission::create([
            'public_barcode_test_id' => $test->id,
            'student_name' => $data['student_name'],
            'school_name' => $data['school_name'] ?? null,
            'classroom' => $data['classroom'] ?? null,
            'answers' => $answers,
            'score' => $result['score'],
            'total_points' => $result['total_points'],
            'score_percentage' => $result['score_percentage'],
            'passed' => $result['passed'],
            'correct_count' => $result['correct_count'],
            'incorrect_count' => $result['incorrect_count'],
            'unanswered_count' => $result['unanswered_count'],
            'review' => $result['review'],
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ],
        ]);

        return view('public.barcode-test', [
            'test' => $test,
            'questions' => $questions,
            'submission' => $submission,
        ]);
    }

    private function grade(Collection $questions, array $answers, float $passingScore): array
    {
        $score = 0.0;
        $totalPoints = 0.0;
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $review = [];

        foreach ($questions as $question) {
            $points = (float) ($question->points ?: 1);
            $totalPoints += $points;
            $selected = $answers[$question->id] ?? null;
            $isCorrect = $selected !== null && $this->isCorrectAnswer($question, (string) $selected);

            if ($selected === null || $selected === '') {
                $unansweredCount++;
            } elseif ($isCorrect) {
                $correctCount++;
                $score += $points;
            } else {
                $incorrectCount++;
            }

            $review[] = [
                'id' => $question->id,
                'text' => $question->question_text_ar ?: $question->question_text,
                'selected' => $selected,
                'correct' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation_ar ?: $question->explanation,
                'points' => $points,
            ];
        }

        $scorePercentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0.0;

        return [
            'score' => $score,
            'total_points' => $totalPoints,
            'score_percentage' => $scorePercentage,
            'passed' => $scorePercentage >= $passingScore,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'review' => $review,
        ];
    }

    private function isCorrectAnswer(Question $question, string $selected): bool
    {
        if (isset($question->options[$selected])) {
            $option = $question->options[$selected];

            if (is_array($option)) {
                $flag = $option['is_correct'] ?? false;
                if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
                    return true;
                }

                return (isset($option['text']) && (string) $option['text'] === (string) $question->correct_answer)
                    || (isset($option['text_ar']) && (string) $option['text_ar'] === (string) $question->correct_answer);
            }

            return (string) $option === (string) $question->correct_answer;
        }

        return $selected === (string) $question->correct_answer;
    }
}
