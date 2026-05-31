<?php

namespace App\Filament\Widgets;

use App\Models\LessonCompletion;
use App\Models\PaymentRequest;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayActivityWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        return [
            Stat::make('مستخدمون جدد اليوم', User::where('created_at', '>=', $today)->count())
                ->description('عدد المستخدمين المسجلين اليوم')
                ->icon('heroicon-o-user-plus')
                ->color('info'),

            Stat::make('دروس مكتملة اليوم', LessonCompletion::where('created_at', '>=', $today)->count())
                ->description('إجمالي الدروس التي تم إنهاؤها')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('اختبارات اليوم', QuizAttempt::where('created_at', '>=', $today)->count())
                ->description('محاولات الاختبارات اليوم')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning'),

            Stat::make('مدفوعات اليوم', PaymentRequest::where('created_at', '>=', $today)->count())
                ->description('طلبات الدفع المقدمة اليوم')
                ->icon('heroicon-o-currency-dollar')
                ->color('danger'),
        ];
    }
}
