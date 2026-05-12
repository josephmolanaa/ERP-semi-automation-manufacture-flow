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

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚠️ Overdue Invoices')
            ->query(
                Invoice::query()
                    ->with(['suratJalan.jobOrder.po.customer', 'createdBy'])
                    ->whereIn('status_bayar', ['unpaid', 'partial'])
                    ->where('jatuh_tempo', '<', now())
                    ->orderBy('jatuh_tempo', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor_invoice')
                    ->label('Invoice No')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('suratJalan.jobOrder.po.customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('jumlah_bayar')
                    ->label('Paid')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('sisa_tagihan')
                    ->label('Outstanding')
                    ->money('IDR')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->color('danger')
                    ->description(fn ($record) => 
                        now()->diffInDays($record->jatuh_tempo) . ' hari terlambat'
                    ),

                Tables\Columns\TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => Invoice::STATUS_LABELS[$state] ?? $state),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
