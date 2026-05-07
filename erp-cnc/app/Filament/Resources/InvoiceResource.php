<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static UnitEnum|string|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Invoice')
                    ->schema([
                        TextInput::make('nomor_invoice')
                            ->label('Nomor Invoice')
                            ->default(fn () => Invoice::generateNomor())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('sj_id')
                            ->label('Surat Jalan')
                            ->relationship('suratJalan', 'nomor_sj')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('tanggal')
                            ->default(today())
                            ->required(),

                        DatePicker::make('jatuh_tempo')
                            ->label('Jatuh Tempo')
                            ->default(today()->addDays(30)),

                        TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        TextInput::make('jumlah_bayar')
                            ->label('Jumlah Bayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),

                        Select::make('status_bayar')
                            ->label('Status Bayar')
                            ->options([
                                'unpaid' => 'Belum Bayar',
                                'partial' => 'Sebagian',
                                'paid' => 'Lunas',
                            ])
                            ->default('unpaid')
                            ->required(),

                        FileUpload::make('pdf_path')
                            ->label('Upload PDF Invoice')
                            ->disk('public')
                            ->directory('documents/invoices')
                            ->acceptedFileTypes(['application/pdf'])
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),

                        Textarea::make('catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_invoice')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('suratJalan.nomor_sj')
                    ->label('Surat Jalan')
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('jumlah_bayar')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('sisa_tagihan')
                    ->label('Sisa')
                    ->money('IDR'),

                TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->pdf_path)),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('status_bayar')
                    ->label('Status Bayar')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('uploaded_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->visible(fn ($record): bool => filled($record->pdf_path))
                    ->url(fn ($record): string => Storage::disk('public')->url($record->pdf_path))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
