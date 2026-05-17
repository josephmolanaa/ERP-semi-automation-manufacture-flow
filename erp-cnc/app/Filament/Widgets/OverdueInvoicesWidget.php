<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueInvoicesWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.dashboard.overdue_invoices'))
            ->query(
                Invoice::query()
                    ->with(['suratJalan.jobOrder.po.customer', 'createdBy'])
                    ->select(['id', 'nomor_invoice', 'sj_id', 'created_by', 'total', 'jumlah_bayar', 'jatuh_tempo', 'status_bayar'])
                    ->whereIn('status_bayar', ['unpaid', 'partial'])
                    ->where('jatuh_tempo', '<', today()->toDateString())
                    ->orderBy('jatuh_tempo', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor_invoice')
                    ->label(__('app.fields.invoice_no'))
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('suratJalan.jobOrder.po.customer.name')
                    ->label(__('app.fields.customer'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('app.fields.total'))
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('jumlah_bayar')
                    ->label(__('app.fields.paid'))
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('sisa_tagihan')
                    ->label(__('app.dashboard.outstanding_receivables'))
                    ->money('IDR')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->label(__('app.fields.due_date'))
                    ->date('d M Y')
                    ->color('danger')
                    ->description(fn ($record) => __('app.dashboard.days_late', [
                        'days' => now()->diffInDays($record->jatuh_tempo),
                    ])),

                Tables\Columns\TextColumn::make('status_bayar')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('app.statuses.' . $state)),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
