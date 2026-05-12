<?php

namespace App\Filament\Widgets;

use App\Models\Quotation;
use App\Models\Po;
use App\Models\JobOrder;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Quotations stats
        $totalQuotations = Quotation::whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth)
            ->count();
        
        $approvedQuotations = Quotation::where('status', 'approved')
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth)
            ->count();

        $conversionRate = $totalQuotations > 0 
            ? round(($approvedQuotations / $totalQuotations) * 100, 1) 
            : 0;

        // Revenue stats
        $currentRevenue = Invoice::where('status_bayar', 'paid')
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth)
            ->sum('total');

        $lastMonthRevenue = Invoice::where('status_bayar', 'paid')
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth - 1)
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Job Orders stats
        $activeJobs = JobOrder::whereIn('status', ['pending', 'design', 'machining', 'assembly', 'qc'])
            ->count();

        $finishedJobs = JobOrder::where('status', 'finished')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        // Outstanding invoices
        $outstandingInvoices = Invoice::whereIn('status_bayar', ['unpaid', 'partial'])
            ->sum('total');

        $overdueInvoices = Invoice::whereIn('status_bayar', ['unpaid', 'partial'])
            ->where('jatuh_tempo', '<', now())
            ->count();

        return [
            Stat::make('Quotations Bulan Ini', $totalQuotations)
                ->description("Conversion Rate: {$conversionRate}%")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($conversionRate >= 50 ? 'success' : 'warning')
                ->chart([7, 12, 15, 18, 22, 25, $totalQuotations]),

            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($currentRevenue, 0, ',', '.'))
                ->description($revenueChange >= 0 ? "+{$revenueChange}% dari bulan lalu" : "{$revenueChange}% dari bulan lalu")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthRevenue * 0.7,
                    $lastMonthRevenue * 0.8,
                    $lastMonthRevenue * 0.9,
                    $lastMonthRevenue,
                    $currentRevenue * 0.6,
                    $currentRevenue * 0.8,
                    $currentRevenue
                ]),

            Stat::make('Job Orders Aktif', $activeJobs)
                ->description("{$finishedJobs} selesai bulan ini")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->chart([5, 8, 12, 15, 18, 20, $activeJobs]),

            Stat::make('Outstanding Invoices', 'Rp ' . number_format($outstandingInvoices, 0, ',', '.'))
                ->description($overdueInvoices > 0 ? "{$overdueInvoices} invoice overdue" : 'Semua on-time')
                ->descriptionIcon($overdueInvoices > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                ->color($overdueInvoices > 0 ? 'danger' : 'success'),
        ];
    }
}
