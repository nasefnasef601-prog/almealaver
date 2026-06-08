<?php

namespace App\Filament\Widgets;

use App\Models\QuizResult;
use Filament\Widgets\LineChartWidget;

class QuizCompletionChart extends LineChartWidget
{
    protected ?string $heading = 'إكمال الاختبارات (آخر 30 يوم)';

    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $startDate = now()->subDays(29)->startOfDay();

        $raw = QuizResult::where('created_at', '>=', $startDate)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $counts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $counts[] = $raw[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الاختبارات',
                    'data' => $counts,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
