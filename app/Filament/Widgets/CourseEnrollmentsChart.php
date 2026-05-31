<?php

namespace App\Filament\Widgets;

use App\Models\AccessGrant;
use App\Models\Course;
use Filament\Widgets\BarChartWidget;

class CourseEnrollmentsChart extends BarChartWidget
{
    protected ?string $heading = 'التسجيل في الكورسات (أكثر 10)';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $courses = Course::withCount('accessGrants')
            ->orderByDesc('access_grants_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلاب',
                    'data' => $courses->pluck('access_grants_count')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1'],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $courses->map(fn($c) => $c->title_ar ?? $c->title)->toArray(),
        ];
    }
}
