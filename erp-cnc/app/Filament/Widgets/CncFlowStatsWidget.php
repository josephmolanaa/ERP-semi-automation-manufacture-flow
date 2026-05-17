<?php
// app/Filament/Widgets/CncFlowStatsWidget.php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\JobOrder;
use App\Models\Po;
use App\Models\Quotation;
use Illuminate\Support\Facades\Cache;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CncFlowStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        return Cache::remember('filament.cnc_flow_stats', now()->addMinutes(5), fn (): array => $this->buildStats());
    }

    protected function buildStats(): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $today = today()->toDateString();

        $quotationSummary = Quotation::query()
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(total_harga), 0) as aggregate_total')
            ->first();

        $poSummary = Po::query()
            ->whereBetween('tanggal_po', [$monthStart, $monthEnd])
            ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(total), 0) as aggregate_total')
            ->first();

        $jobSummary = JobOrder::query()
            ->selectRaw(
                "SUM(CASE WHEN status IN ('pending', 'design', 'machining', 'assembly', 'qc') THEN 1 ELSE 0 END) as active_count"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'delayed' OR (status <> 'finished' AND estimasi_selesai < ?) THEN 1 ELSE 0 END) as delayed_count",
                [$today],
            )
            ->first();

        $revenue = Invoice::query()
            ->where('status_bayar', 'paid')
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->sum('total');

        $piutang = (float) Invoice::query()
            ->whereIn('status_bayar', ['unpaid', 'partial'])
            ->selectRaw('COALESCE(SUM(total - jumlah_bayar), 0) as outstanding')
            ->value('outstanding');

        $quotasiCount = (int) ($quotationSummary->aggregate_count ?? 0);
        $quotasiValue = (float) ($quotationSummary->aggregate_total ?? 0);
        $poCount = (int) ($poSummary->aggregate_count ?? 0);
        $poValue = (float) ($poSummary->aggregate_total ?? 0);
        $jobPending = (int) ($jobSummary->active_count ?? 0);
        $jobDelayed = (int) ($jobSummary->delayed_count ?? 0);

        return [
            Stat::make('Penawaran Bulan Ini', $quotasiCount)
                ->description('Total: Rp ' . number_format($quotasiValue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('PO Masuk Bulan Ini', $poCount)
                ->description('Nilai: Rp ' . number_format($poValue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Job Order Aktif', $jobPending)
                ->description($jobDelayed > 0 ? "{$jobDelayed} Delayed" : 'Semua on-track')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color($jobDelayed > 0 ? 'danger' : 'success'),

            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Piutang Outstanding', 'Rp ' . number_format($piutang, 0, ',', '.'))
                ->description('Belum lunas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($piutang > 50_000_000 ? 'danger' : 'warning'),
        ];
    }
}
