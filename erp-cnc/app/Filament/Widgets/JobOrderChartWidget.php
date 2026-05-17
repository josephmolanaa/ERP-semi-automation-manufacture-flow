<?php
// app/Filament/Widgets/JobOrderChartWidget.php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class JobOrderChartWidget extends ChartWidget
{
    protected ?string $heading = null;
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        return Cache::remember('filament.job_order_chart', now()->addMinutes(5), fn (): array => $this->buildData());
    }

    protected function getHeading(): ?string
    {
        return __('app.dashboard.job_status');
    }

    protected function buildData(): array
    {
        $statuses = ['pending', 'design', 'machining', 'assembly', 'qc', 'finished', 'delayed'];
        $statusCounts = JobOrder::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = array_map(
            fn (string $status): int => (int) ($statusCounts[$status] ?? 0),
            $statuses,
        );

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
            'labels' => array_map(
                fn (string $status): string => __('app.statuses.' . $status),
                $statuses,
            ),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
