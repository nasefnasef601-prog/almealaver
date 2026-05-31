<?php

namespace App\Filament\Resources\SkillResource\Widgets;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\Skill;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SkillStatsOverview extends BaseWidget
{
    public ?int $recordId = null;

    protected function getStats(): array
    {
        if (!$this->recordId) {
            return [];
        }

        $skill = Skill::find($this->recordId);
        if (!$skill) {
            return [];
        }

        $courseIds = Course::where('skill_id', $skill->id)->pluck('id');
        $quizIds = Quiz::whereIn('course_id', $courseIds)->pluck('id');

        $results = QuizResult::whereIn('quiz_id', $quizIds);

        $attemptsCount = $results->count();
        $avgScore = $results->avg('score_percentage');
        $passedCount = (clone $results)->where('passed', true)->count();
        $passRate = $attemptsCount > 0 ? round(($passedCount / $attemptsCount) * 100, 1) : 0;
        $uniqueStudents = (clone $results)->distinct('user_id')->count('user_id');
        $courseCount = Course::where('skill_id', $skill->id)->count();
        $questionsCount = $skill->questions()->count();
        $quizCount = Quiz::whereIn('course_id', $courseIds)->count();

        return [
            Stat::make('الكورسات', $courseCount)
                ->description('إجمالي الكورسات المرتبطة')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('الأسئلة', $questionsCount)
                ->description('عدد الأسئلة المرتبطة')
                ->icon('heroicon-o-list-bullet'),

            Stat::make('الاختبارات', $quizCount)
                ->description('عدد الاختبارات في الكورسات')
                ->icon('heroicon-o-question-mark-circle'),

            Stat::make('الطلاب المختبرين', $uniqueStudents)
                ->description('عدد الطلاب الذين أدوا اختبارات')
                ->icon('heroicon-o-users'),

            Stat::make('متوسط الدرجات', $attemptsCount > 0 ? number_format($avgScore, 1) . '%' : '—')
                ->description($attemptsCount > 0 ? "من {$attemptsCount} محاولة" : 'لا توجد نتائج بعد')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('نسبة النجاح', $attemptsCount > 0 ? "{$passRate}%" : '—')
                ->description($passedCount . ' ناجح من ' . $attemptsCount . ' محاولة')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
