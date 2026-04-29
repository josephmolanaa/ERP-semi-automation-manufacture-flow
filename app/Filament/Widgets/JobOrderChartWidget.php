<?php
// app/Filament/Widgets/JobOrderChartWidget.php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\ChartWidget;

class JobOrderChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Status Job Order';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $statuses = ['pending', 'design', 'machining', 'assembly', 'qc', 'finished', 'delayed'];
        $counts   = [];

        foreach ($statuses as $status) {
            $counts[] = JobOrder::where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Job Orders',
                    'data'            => $counts,
                    'backgroundColor' => [
                        '#94a3b8', '#3b82f6', '#f59e0b',
                        '#8b5cf6', '#06b6d4', '#22c55e', '#ef4444',
                    ],
                ],
            ],
            'labels' => ['Pending', 'Design', 'Machining', 'Assembly', 'QC', 'Finished', 'Delayed'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}