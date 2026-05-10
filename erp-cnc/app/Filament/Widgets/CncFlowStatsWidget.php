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

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        return Cache::remember('filament.cnc_flow_stats', now()->addMinute(), fn (): array => $this->buildStats());
    }

    protected function buildStats(): array
    {
        $bulanIni = now()->startOfMonth();

        // Quotation bulan ini
        $quotasiCount  = Quotation::where('tanggal', '>=', $bulanIni)->count();
        $quotasiValue  = Quotation::where('tanggal', '>=', $bulanIni)->sum('total_harga');

        // PO masuk bulan ini
        $poCount       = Po::where('tanggal_po', '>=', $bulanIni)->count();
        $poValue       = Po::where('tanggal_po', '>=', $bulanIni)->sum('total');

        // Job Order aktif
        $jobPending    = JobOrder::whereIn('status', ['pending', 'design', 'machining', 'assembly', 'qc'])->count();
        $jobDelayed    = JobOrder::where('status', 'delayed')
                            ->orWhere(function ($q) {
                                $q->whereNotIn('status', ['finished'])
                                  ->where('estimasi_selesai', '<', today());
                            })->count();

        // Revenue bulan ini (invoice paid)
        $revenue = Invoice::where('status_bayar', 'paid')
                       ->where('tanggal', '>=', $bulanIni)
                       ->sum('total');

        // Piutang outstanding
        $piutang = (float) Invoice::whereIn('status_bayar', ['unpaid', 'partial'])
            ->selectRaw('COALESCE(SUM(total - jumlah_bayar), 0) as outstanding')
            ->value('outstanding');

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
