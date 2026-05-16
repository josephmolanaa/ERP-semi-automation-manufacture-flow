<?php

namespace App\Filament\Widgets;

use App\Models\Quotation;
use App\Models\Po;
use App\Models\JobOrder;
use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivitiesWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = '120s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Activities')
            ->query(
                Quotation::query()
                    ->with(['customer', 'createdBy'])
                    ->select(['id', 'nomor', 'customer_id', 'created_by', 'status', 'total_harga', 'tanggal', 'created_at'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('Document')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'converted' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Amount')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By'),
            ])
            ->paginated(false);
    }
}
