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
        $data = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = QuizResult::whereDate('created_at', $date)->count();
            $data->push([
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'الاختبارات',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }
}
