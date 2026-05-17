<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratJalanResource\Pages;
use App\Models\JobOrder;
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

class SuratJalanResource extends Resource
{
    protected static ?string $model = SuratJalan::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('app.groups.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.resources.surat_jalans');
    }

    public static function getModelLabel(): string
    {
        return __('app.resources.surat_jalans');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('app.sections.surat_jalan_info'))
                    ->schema([
                        TextInput::make('nomor_sj')
                            ->label(__('app.resources.surat_jalans'))
                            ->default(fn () => SuratJalan::generateNomor())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('job_order_id')
                            ->label(__('app.resources.job_orders'))
                            ->getSearchResultsUsing(fn (string $search): array => JobOrder::query()
                                ->with('po.customer')
                                ->where('status', 'finished')
                                ->whereDoesntHave('suratJalan')
                                ->where(function (Builder $query) use ($search): void {
                                    $query
                                        ->where('nomor_job', 'like', "%{$search}%")
                                        ->orWhereHas('po.customer', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"));
                                })
                                ->orderByDesc('updated_at')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (JobOrder $jobOrder): array => [
                                    $jobOrder->id => $jobOrder->nomor_job . ' - ' . ($jobOrder->po?->customer?->name ?? 'Tanpa customer'),
                                ])
                                ->all())
                            ->getOptionLabelUsing(function ($value): ?string {
                                $jobOrder = JobOrder::with('po.customer')->find($value);

                                return $jobOrder
                                    ? $jobOrder->nomor_job . ' - ' . ($jobOrder->po?->customer?->name ?? 'Tanpa customer')
                                    : null;
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set): void {
                                $jobOrder = JobOrder::with('po.customer')->find($state);
                                $set('alamat_kirim', $jobOrder?->po?->customer?->address);
                            })
                            ->required(),

                        DatePicker::make('tanggal_kirim')
                            ->label(__('app.fields.date'))
                            ->default(today())
                            ->required(),

                        Select::make('status')
                            ->options(SuratJalan::STATUS_LABELS)
                            ->default('disiapkan')
                            ->required(),

                        TextInput::make('ekspedisi')
                            ->maxLength(255),

                        TextInput::make('no_resi')
                            ->label('No. Resi')
                            ->maxLength(255),

                        TextInput::make('penerima')
                            ->maxLength(255),

                        FileUpload::make('pdf_path')
                            ->label('Upload PDF Surat Jalan')
                            ->disk('public')
                            ->directory('documents/surat-jalan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),

                        Textarea::make('alamat_kirim')
                            ->label(__('app.fields.address'))
                            ->columnSpanFull(),

                        Textarea::make('catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('jobOrder'))
            ->columns([
                TextColumn::make('nomor_sj')
                    ->label(__('app.resources.surat_jalans'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jobOrder.nomor_job')
                    ->label(__('app.resources.job_orders'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_kirim')
                    ->label(__('app.fields.date'))
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'disiapkan' => 'gray',
                        'dikirim' => 'info',
                        'diterima' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('ekspedisi')
                    ->placeholder('-'),

                TextColumn::make('no_resi')
                    ->label('No. Resi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('penerima')
                    ->searchable()
                    ->placeholder('-'),

                IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->pdf_path)),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_kirim', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(SuratJalan::STATUS_LABELS),
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
            'index' => Pages\ListSuratJalans::route('/'),
            'create' => Pages\CreateSuratJalan::route('/create'),
            'edit' => Pages\EditSuratJalan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowed('view_surat_jalan');
    }

    public static function canCreate(): bool
    {
        return FilamentAccess::allowed('create_surat_jalan');
    }

    public static function canEdit(Model $record): bool
    {
        return FilamentAccess::allowed('edit_surat_jalan');
    }

    public static function canDelete(Model $record): bool
    {
        return FilamentAccess::allowed('delete_surat_jalan');
    }
}
