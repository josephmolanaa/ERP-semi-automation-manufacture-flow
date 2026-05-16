<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOrderResource\Pages;
use App\Models\JobProgress;
use App\Models\JobOrder;
use App\Models\User;
use App\Support\FilamentAccess;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class JobOrderResource extends Resource
{
    protected static ?string $model = JobOrder::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static UnitEnum|string|null $navigationGroup = 'Produksi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Job Order')
                    ->schema([
                        TextInput::make('nomor_job')
                            ->label('Nomor Job')
                            ->default(fn () => JobOrder::generateNomor())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('po_id')
                            ->label('PO')
                            ->relationship('po', 'nomor_po')
                            ->searchable()
                            ->required(),

                        Select::make('status')
                            ->options(JobOrder::STATUS_LABELS)
                            ->default('pending')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('progress_persen', JobOrder::STATUS_PROGRESS[$state] ?? 0);
                                $set('tanggal_selesai', $state === 'finished' ? today() : null);
                            })
                            ->required(),

                        TextInput::make('progress_persen')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->required(),

                        DatePicker::make('estimasi_selesai')
                            ->label('Estimasi Selesai'),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai'),

                        Textarea::make('catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['po.customer']))
            ->columns([
                TextColumn::make('nomor_job')
                    ->label('Nomor Job')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('po.nomor_po')
                    ->label('PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('po.customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => JobOrder::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'design' => 'info',
                        'machining' => 'warning',
                        'assembly' => 'primary',
                        'qc' => 'info',
                        'finished' => 'success',
                        'delayed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('progress_persen')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('estimasi_selesai')
                    ->label('Estimasi')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->estimasi_selesai?->isPast() && $record->status !== 'finished' ? 'danger' : null),

                TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('progresses_count')
                    ->label('Log')
                    ->counts('progresses')
                    ->sortable(),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(JobOrder::STATUS_LABELS),
            ])
            ->actions([
                Actions\Action::make('tambah_progress')
                    ->label('Progress')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn (JobOrder $record): bool => FilamentAccess::allowed('update_job_progress') && $record->status !== 'finished')
                    ->form([
                        Select::make('tahap')
                            ->options(JobProgress::TAHAP_LABELS)
                            ->default(fn (?JobOrder $record): string => match ($record?->status) {
                                'pending', 'delayed' => 'design',
                                'finished' => JobOrder::FINISHING_TAHAP,
                                'machining', 'assembly', 'qc' => $record->status,
                                default => 'design',
                            })
                            ->required(),

                        Select::make('operator_id')
                            ->label('Operator')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->limit(100)
                                ->pluck('name', 'id')
                                ->all())
                            ->default(fn () => auth()->id())
                            ->searchable()
                            ->required(),

                        DatePicker::make('tanggal')
                            ->default(today())
                            ->required(),

                        TextInput::make('durasi_menit')
                            ->label('Durasi (menit)')
                            ->numeric()
                            ->minValue(0),

                        FileUpload::make('foto_paths')
                            ->label('Foto Progress')
                            ->disk('public')
                            ->directory('job-progress')
                            ->multiple()
                            ->image()
                            ->preserveFilenames(),

                        Textarea::make('catatan')
                            ->columnSpanFull(),
                    ])
                    ->action(function (JobOrder $record, array $data): void {
                        $data['durasi_menit'] ??= JobProgress::DEFAULT_DURASI_MENIT;

                        $record->progresses()->create($data);
                        self::syncStatusFromTahap($record, $data['tahap']);

                        Notification::make()
                            ->title('Progress job order tersimpan')
                            ->success()
                            ->send();
                    }),

                Actions\Action::make('advance_status')
                    ->label('Lanjut Status')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn (JobOrder $record): bool => ! in_array($record->status, ['finished', 'delayed'], true))
                    ->requiresConfirmation()
                    ->action(function (JobOrder $record): void {
                        $record->advanceStatus();

                        Notification::make()
                            ->title('Status job order diperbarui')
                            ->success()
                            ->send();
                    }),

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
            'index' => Pages\ListJobOrders::route('/'),
            'create' => Pages\CreateJobOrder::route('/create'),
            'edit' => Pages\EditJobOrder::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowed('view_job_order');
    }

    public static function canCreate(): bool
    {
        return FilamentAccess::allowed('create_job_order');
    }

    public static function canEdit(Model $record): bool
    {
        return FilamentAccess::allowed('edit_job_order');
    }

    public static function canDelete(Model $record): bool
    {
        return FilamentAccess::allowed('delete_job_order');
    }

    private static function syncStatusFromTahap(JobOrder $record, string $tahap): void
    {
        $status = JobOrder::statusFromTahap($tahap);

        $record->update([
            'status' => $status,
            'progress_persen' => JobOrder::STATUS_PROGRESS[$status] ?? $record->progress_persen,
            'tanggal_selesai' => $status === 'finished' ? today() : null,
        ]);

        $record->po?->syncStatusFromJobs();
    }
}
