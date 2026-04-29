<?php
// app/Filament/Resources/QuotationResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Customer;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Penawaran';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Penawaran')
                ->columns(2)
                ->schema([
                    TextInput::make('nomor')
                        ->label('Nomor Quotation')
                        ->default(fn () => Quotation::generateNomor())
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')->required(),
                            TextInput::make('company'),
                            TextInput::make('email')->email(),
                            TextInput::make('phone'),
                        ]),

                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->default(today())
                        ->required(),

                    DatePicker::make('berlaku_sampai')
                        ->label('Berlaku Sampai')
                        ->default(today()->addDays(14))
                        ->required(),

                    Select::make('status')
                        ->options([
                            'draft'     => 'Draft',
                            'sent'      => 'Terkirim',
                            'approved'  => 'Disetujui',
                            'rejected'  => 'Ditolak',
                            'converted' => 'Converted ke PO',
                        ])
                        ->default('draft')
                        ->disabled(fn ($record) => $record?->status === 'converted')
                        ->required(),

                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->columnSpanFull(),
                ]),

            Section::make('Item Penawaran')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            TextInput::make('part_name')
                                ->label('Nama Part')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('material')
                                ->label('Material')
                                ->columnSpan(2),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) =>
                                    $set('subtotal', (float)$state * (float)$get('harga_satuan'))
                                ),

                            Select::make('satuan')
                                ->options(['pcs' => 'pcs', 'set' => 'set', 'unit' => 'unit', 'kg' => 'kg', 'm' => 'm'])
                                ->default('pcs')
                                ->required(),

                            TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) =>
                                    $set('subtotal', (float)$state * (float)$get('qty'))
                                ),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(),

                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->reorderable('urutan')
                        ->addActionLabel('+ Tambah Item')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('berlaku_sampai')
                    ->label('Berlaku s/d')
                    ->date('d M Y')
                    ->color(fn ($record) =>
                        $record->berlaku_sampai->isPast() && $record->status === 'sent'
                            ? 'danger' : null
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'draft',
                        'info'    => 'sent',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'warning' => 'converted',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim',
                        'approved'  => 'Disetujui',
                        'rejected'  => 'Ditolak',
                        'converted' => 'Converted',
                        default     => $state,
                    }),

                TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim',
                        'approved'  => 'Disetujui',
                        'rejected'  => 'Ditolak',
                        'converted' => 'Converted',
                    ]),

                Tables\Filters\Filter::make('bulan_ini')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $q) => $q->whereMonth('tanggal', now()->month)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // ── Action: Kirim Email ──────────────────────────────────
                Tables\Actions\Action::make('kirim_email')
                    ->label('Kirim Email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'sent']))
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Quotation via Email?')
                    ->modalDescription(fn ($record) =>
                        "Email akan dikirim ke {$record->customer->email} beserta PDF dan link approval."
                    )
                    ->action(function ($record) {
                        $token = $record->approval_token ?? $record->generateApprovalToken();
                        \Mail::to($record->customer->email)
                            ->send(new \App\Mail\QuotationMail($record));
                        $record->update(['status' => 'sent', 'sent_at' => now()]);
                        \Filament\Notifications\Notification::make()
                            ->title('Email berhasil dikirim!')
                            ->success()->send();
                    }),

                // ── Action: Download PDF ─────────────────────────────────
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => route('quotation.pdf', $record))
                    ->openUrlInNewTab(),

                // ── Action: Convert to PO ────────────────────────────────
                Tables\Actions\Action::make('convert_to_po')
                    ->label('Convert → PO')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Convert ke Purchase Order?')
                    ->modalDescription('PO dan Job Order akan otomatis dibuat dari quotation ini.')
                    ->action(function ($record) {
                        app(\App\Actions\ConvertQuotationToPo::class)->execute($record);
                        \Filament\Notifications\Notification::make()
                            ->title('PO & Job Order berhasil dibuat!')
                            ->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit'   => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }

    // Hanya tampilkan data milik user sendiri untuk role sales
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('sales') && !auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->id());
        }

        return $query;
    }
}