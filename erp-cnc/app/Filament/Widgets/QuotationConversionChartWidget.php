<?php

namespace App\Filament\Widgets;

use App\Models\Quotation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuotationConversionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Quotation Conversion Rate (6 Bulan)';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '300px';
    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        return Cache::remember('filament.quotation_conversion_chart', now()->addMinutes(5), fn (): array => $this->buildData());
    }

    protected function buildData(): array
    {
        $startDate = now()->subMonths(5)->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();
        $monthlyRows = Quotation::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->select([
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN status IN ('approved', 'converted') THEN 1 ELSE 0 END) as converted_count"),
            ])
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($row): string => $row->year . '-' . $row->month);

        $labels = [];
        $totalData = [];
        $convertedData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $row = $monthlyRows->get($date->year . '-' . $date->month);

            $totalData[] = (int) ($row->total_count ?? 0);
            $convertedData[] = (int) ($row->converted_count ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Quotations',
                    'data' => $totalData,
                    'backgroundColor' => 'rgba(148, 163, 184, 0.5)',
                    'borderColor' => 'rgb(148, 163, 184)',
                ],
                [
                    'label' => 'Converted/Approved',
                    'data' => $convertedData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
