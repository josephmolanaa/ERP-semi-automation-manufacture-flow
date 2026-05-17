<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\SuratJalan;
use App\Support\FilamentAccess;
use BackedEnum;
use Filament\Actions;
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
use Illuminate\Database\Eloquent\Model;
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
                            ->getSearchResultsUsing(fn (string $search): array => SuratJalan::query()
                                ->with('jobOrder.po.customer')
                                ->where('status', 'diterima')
                                ->whereDoesntHave('invoice')
                                ->where(function (Builder $query) use ($search): void {
                                    $query
                                        ->where('nomor_sj', 'like', "%{$search}%")
                                        ->orWhereHas('jobOrder', fn (Builder $query): Builder => $query->where('nomor_job', 'like', "%{$search}%"))
                                        ->orWhereHas('jobOrder.po.customer', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"));
                                })
                                ->orderByDesc('tanggal_kirim')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (SuratJalan $suratJalan): array => [
                                    $suratJalan->id => $suratJalan->nomor_sj . ' - ' . ($suratJalan->jobOrder?->po?->customer?->name ?? 'Tanpa customer'),
                                ])
                                ->all())
                            ->getOptionLabelUsing(function ($value): ?string {
                                $suratJalan = SuratJalan::with('jobOrder.po.customer')->find($value);

                                return $suratJalan
                                    ? $suratJalan->nomor_sj . ' - ' . ($suratJalan->jobOrder?->po?->customer?->name ?? 'Tanpa customer')
                                    : null;
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set): void {
                                $suratJalan = SuratJalan::with('jobOrder.po')->find($state);
                                $set('total', $suratJalan?->jobOrder?->po?->total ?? 0);
                                $set('jumlah_bayar', 0);
                                $set('status_bayar', 'unpaid');
                            })
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
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn ($state, $set, $get) =>
                                $set('status_bayar', Invoice::paymentStatus((float) $get('total'), (float) $state))
                            )
                            ->required(),

                        Select::make('status_bayar')
                            ->label('Status Bayar')
                            ->options(Invoice::STATUS_LABELS)
                            ->default('unpaid')
                            ->required(),

                        FileUpload::make('pdf_path')
                            ->label('Upload PDF Invoice')
                            ->disk('public')
                            ->directory('documents/invoices')
                            ->acceptedFileTypes(['application/pdf'])
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (!$state) {
                                    return;
                                }

                                try {
                                    $file = is_array($state) ? reset($state) : $state;
                                    
                                    if (!$file) {
                                        return;
                                    }

                                    // Parse PDF
                                    $parser = app(\App\Services\Parsers\InvoicePdfParser::class);
                                    $data = $parser->parse($file);

                                    if (!$data['success']) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Gagal parse PDF')
                                            ->body($data['error'] ?? 'Unknown error')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    // Auto-fill dates
                                    if ($data['tanggal']) {
                                        $set('tanggal', $data['tanggal']);
                                    }
                                    if ($data['jatuh_tempo']) {
                                        $set('jatuh_tempo', $data['jatuh_tempo']);
                                    }

                                    // Auto-fill total
                                    if ($data['total']) {
                                        $set('total', $data['total']);
                                    }

                                    // Auto-fill notes
                                    if ($data['catatan']) {
                                        $set('catatan', $data['catatan']);
                                    }

                                    \Filament\Notifications\Notification::make()
                                        ->title('PDF berhasil di-parse!')
                                        ->body('Data otomatis terisi dari PDF.')
                                        ->success()
                                        ->send();

                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Invoice PDF Auto-fill Error: ' . $e->getMessage());
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Error saat parse PDF')
                                        ->body('Silakan isi form secara manual.')
                                        ->warning()
                                        ->send();
                                }
                            }),

                        Textarea::make('catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['suratJalan', 'createdBy']))
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
                    ->options(Invoice::STATUS_LABELS),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->actions([
                Actions\Action::make('uploaded_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->visible(fn ($record): bool => filled($record->pdf_path))
                    ->url(fn ($record): string => Storage::disk('public')->url($record->pdf_path))
                    ->openUrlInNewTab(),

                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowed('view_invoice');
    }

    public static function canCreate(): bool
    {
        return FilamentAccess::allowed('create_invoice');
    }

    public static function canEdit(Model $record): bool
    {
        return FilamentAccess::allowed('edit_invoice');
    }

    public static function canDelete(Model $record): bool
    {
        return FilamentAccess::allowed('delete_invoice');
    }
}
