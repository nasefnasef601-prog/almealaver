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
        $data = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = User::whereDate('created_at', $date)->count();
            $data->push([
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'المستخدمين',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }
}
