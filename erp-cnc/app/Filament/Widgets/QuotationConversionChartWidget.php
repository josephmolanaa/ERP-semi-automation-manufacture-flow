<?php

namespace App\Filament\Widgets;

use App\Models\Quotation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class QuotationConversionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Quotation Conversion Rate (6 Bulan)';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $labels = [];
        $totalData = [];
        $convertedData = [];

        // Generate last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $total = Quotation::whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->count();

            $converted = Quotation::whereIn('status', ['approved', 'converted'])
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->count();

            $totalData[] = $total;
            $convertedData[] = $converted;
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
