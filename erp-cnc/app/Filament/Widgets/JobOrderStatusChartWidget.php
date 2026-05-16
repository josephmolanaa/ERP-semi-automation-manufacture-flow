<?php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\ChartWidget;

class JobOrderStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Job Order Status Distribution';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = JobOrder::select('status', \DB::raw('count(*) as count'))
            ->whereIn('status', ['pending', 'design', 'machining', 'assembly', 'qc', 'finished'])
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'pending' => '#94a3b8',
            'design' => '#60a5fa',
            'machining' => '#fbbf24',
            'assembly' => '#fb923c',
            'qc' => '#a78bfa',
            'finished' => '#34d399',
        ];

        $backgroundColors = [];

        foreach ($statuses as $status) {
            $labels[] = JobOrder::STATUS_LABELS[$status->status] ?? $status->status;
            $data[] = $status->count;
            $backgroundColors[] = $colors[$status->status] ?? '#94a3b8';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Job Orders',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
