<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\LineChartWidget;

class UserRegistrationsChart extends LineChartWidget
{
    protected ?string $heading = 'تسجيلات المستخدمين (آخر 30 يوم)';

    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $startDate = now()->subDays(29)->startOfDay();

        $raw = User::where('created_at', '>=', $startDate)
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
                    'label' => 'المستخدمين',
                    'data' => $counts,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
