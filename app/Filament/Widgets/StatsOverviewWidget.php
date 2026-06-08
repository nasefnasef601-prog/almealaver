<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\LessonCompletion;
use App\Models\PaymentRequest;
use App\Models\QuizResult;
use App\Models\School;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userCounts = User::selectRaw("COUNT(*) as total, SUM(CASE WHEN role='student' THEN 1 ELSE 0 END) as students, SUM(CASE WHEN role='teacher' THEN 1 ELSE 0 END) as teachers")->first();
        $pendingPayments = PaymentRequest::whereIn('status', ['pending', 'pending_manual_review'])->count();
        $totalRevenue = PaymentRequest::where('status', 'approved')->sum('amount');
        $completedLessons = LessonCompletion::count();
        $quizResults = QuizResult::count();

        return [
            Stat::make('المستخدمين', $userCounts->total)
                ->description('إجمالي المستخدمين')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('الطلاب', $userCounts->students)
                ->description('إجمالي الطلاب')
                ->icon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make('المدرسين', $userCounts->teachers)
                ->description('إجمالي المدرسين')
                ->icon('heroicon-o-chalkboard-teacher')
                ->color('warning'),

            Stat::make('المدارس', School::count())
                ->description('إجمالي المدارس المسجلة')
                ->icon('heroicon-o-building-library')
                ->color('gray'),

            Stat::make('الكورسات', Course::count())
                ->description('إجمالي الكورسات')
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make('المهارات', Skill::count())
                ->description('إجمالي المهارات')
                ->icon('heroicon-o-light-bulb')
                ->color('primary'),

            Stat::make('المواد', Subject::count())
                ->description('إجمالي المواد الدراسية')
                ->icon('heroicon-o-book-open')
                ->color('info'),

            Stat::make('طلبات الدفع المعلقة', $pendingPayments)
                ->description('في انتظار المراجعة')
                ->icon('heroicon-o-clock')
                ->color($pendingPayments > 0 ? 'danger' : 'success'),

            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 0) . ' ريال')
                ->description('من الطلبات المقبولة')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('الدروس المكتملة', number_format($completedLessons))
                ->description('إجمالي الدروس التي تم إنهاؤها')
                ->icon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('نتائج الاختبارات', number_format($quizResults))
                ->description('إجمالي محاولات الاختبارات')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning'),
        ];
    }
}
