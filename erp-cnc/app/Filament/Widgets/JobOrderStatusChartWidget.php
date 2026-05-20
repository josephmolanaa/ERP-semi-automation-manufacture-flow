<?php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JobOrderStatusChartWidget extends ChartWidget
{
    protected ?string $heading = null;
    protected static ?int $sort = 3;
    protected static bool $isLazy = true;
    protected ?string $maxHeight = '300px';
    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        return Cache::remember('filament.job_order_status_chart', now()->addMinutes(5), fn (): array => $this->buildData());
    }

    protected function buildData(): array
    {
        $statuses = JobOrder::select('status', DB::raw('count(*) as count'))
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
            $labels[] = __('app.statuses.' . $status->status);
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

    protected function getHeading(): ?string
    {
        return __('app.dashboard.job_status');
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
