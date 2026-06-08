<?php

namespace App\Http\Controllers;

use App\Models\CourseCompletion;
use App\Models\LessonCompletion;
use App\Models\QuizResult;
use App\Models\SkillProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function progressCsv()
    {
        $user = auth()->user();
        $filename = 'progress-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $rows = [];

        $rows[] = ['النوع', 'العنوان', 'التفاصيل', 'التاريخ'];

        $lessonCompletions = LessonCompletion::where('user_id', $user->id)
            ->with('lesson')->latest()->get();
        foreach ($lessonCompletions as $lc) {
            $rows[] = ['درس', $lc->lesson?->title_ar ?? $lc->lesson?->title ?? '', '', $lc->created_at->format('Y-m-d')];
        }

        $quizResults = QuizResult::where('user_id', $user->id)
            ->with('quiz')->latest()->get();
        foreach ($quizResults as $qr) {
            $rows[] = ['اختبار', $qr->quiz?->title_ar ?? $qr->quiz?->title ?? '', "{$qr->score_percentage}% - " . ($qr->passed ? 'نجاح' : 'رسوب'), $qr->created_at->format('Y-m-d')];
        }

        $courseCompletions = CourseCompletion::where('user_id', $user->id)
            ->with('course')->latest()->get();
        foreach ($courseCompletions as $cc) {
            $rows[] = ['إتمام دورة', $cc->course?->title_ar ?? $cc->course?->title ?? '', "رمز: {$cc->certificate_code}", $cc->completed_at->format('Y-m-d')];
        }

        $skills = SkillProgress::where('user_id', $user->id)->with('skill')->get();
        foreach ($skills as $s) {
            $rows[] = ['مهارة', $s->skill?->name_ar ?? $s->skill?->name ?? '', "{$s->mastery}% - {$s->status}", ''];
        }

        $callback = function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }
            fclose($stream);
        };

        return Response::stream($callback, 200, $headers);
    }
}
