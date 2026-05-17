<?php
// app/Filament/Resources/QuotationResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Customer;
use App\Models\Quotation;
use App\Support\FilamentAccess;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = null;
    protected static UnitEnum|string|null $navigationGroup = null;
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('app.groups.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.resources.quotations');
    }

    public static function getModelLabel(): string
    {
        return __('app.resources.quotations');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Group::make()->schema([
                Section::make(__('app.sections.customer_info'))
                // ->columns(2)
                ->schema([
                    /* TextInput::make('nomor')
                        ->label('Nomor Quotation')
                        ->default(fn () => Quotation::generateNomor())
                        ->disabled()
                        ->dehydrated()
                        ->required(), */

                    Select::make('customer_id')
                        ->label(__('app.fields.customer'))
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')->required(),
                            TextInput::make('company'),
                            TextInput::make('email')->email(),
                            TextInput::make('phone'),
                        ]),

                    /* DatePicker::make('tanggal')
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
                        ->required(), */

                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->columnSpanFull(),
                ]),

            Section::make(__('app.sections.quotation_items'))
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
                                ->afterStateUpdated(fn ($state, $set, $get) =>
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
                                ->afterStateUpdated(fn ($state, $set, $get) =>
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
                        ->addActionLabel('+ Tambah Item')
                        ->columnSpanFull(),
                ]),
            ])->columnSpan(['lg' => 2]),

            \Filament\Schemas\Components\Group::make()->schema([
                Section::make(__('app.sections.status_documents'))->schema([
                    TextInput::make('nomor')
                        ->label(__('app.fields.quotation_no'))
                        ->default(fn () => Quotation::generateNomor())
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Select::make('status')
                        ->options(Quotation::STATUS_LABELS)
                        ->default('draft')
                        ->disabled(fn ($record) => $record?->status === 'converted')
                        ->required(),

                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->default(today())
                        ->required(),

                    DatePicker::make('berlaku_sampai')
                        ->label('Berlaku Sampai')
                        ->default(today()->addDays(14))
                        ->required(),

                    FileUpload::make('pdf_path')
                        ->label('Upload PDF Penawaran')
                        ->disk('public')
                        ->directory('documents/quotations')
                        ->acceptedFileTypes(['application/pdf'])
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                            if (!$state) {
                                return;
                            }

                            try {
                                // Get uploaded file
                                $file = is_array($state) ? reset($state) : $state;
                                
                                if (!$file) {
                                    return;
                                }

                                // Parse PDF
                                $parser = app(\App\Services\Parsers\QuotationPdfParser::class);
                                $data = $parser->parse($file);

                                if (!$data['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Gagal parse PDF')
                                        ->body($data['error'] ?? 'Unknown error')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                // Auto-fill customer if found
                                if (!empty($data['customer']['name'])) {
                                    // Try to find existing customer
                                    $customer = \App\Models\Customer::where('name', 'like', '%' . $data['customer']['name'] . '%')
                                        ->orWhere('company', 'like', '%' . $data['customer']['company'] . '%')
                                        ->first();

                                    if ($customer) {
                                        $set('customer_id', $customer->id);
                                    }
                                }

                                // Auto-fill dates
                                if ($data['tanggal']) {
                                    $set('tanggal', $data['tanggal']);
                                }
                                if ($data['berlaku_sampai']) {
                                    $set('berlaku_sampai', $data['berlaku_sampai']);
                                }

                                // Auto-fill notes
                                if ($data['catatan']) {
                                    $set('catatan', $data['catatan']);
                                }

                                // Auto-fill items
                                if (!empty($data['items'])) {
                                    $set('items', $data['items']);
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('PDF berhasil di-parse!')
                                    ->body('Data otomatis terisi dari PDF. Silakan review dan sesuaikan jika perlu.')
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('PDF Auto-fill Error: ' . $e->getMessage());
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Error saat parse PDF')
                                    ->body('Silakan isi form secara manual.')
                                    ->warning()
                                    ->send();
                            }
                        }),
                ])
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['customer', 'createdBy']))
            ->columns([
                TextColumn::make('nomor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label(__('app.fields.customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label(__('app.fields.date'))
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('berlaku_sampai')
                    ->label('Berlaku s/d')
                    ->date('d M Y')
                    ->color(fn ($record) =>
                        $record->berlaku_sampai->isPast() && $record->status === 'sent'
                            ? 'danger' : null
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'converted' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'converted' => 'Converted',
                        default     => Quotation::STATUS_LABELS[$state] ?? $state,
                    }),

                TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->pdf_path)),

                TextColumn::make('createdBy.name')
                    ->label(__('app.fields.created_by'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Quotation::STATUS_LABELS),

                Tables\Filters\Filter::make('bulan_ini')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $q) => $q->whereBetween('tanggal', [
                        now()->startOfMonth()->toDateString(),
                        now()->endOfMonth()->toDateString(),
                    ])),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->actions([
                Actions\EditAction::make(),

                // ── Action: Kirim Email ──────────────────────────────────
                Actions\Action::make('kirim_email')
                    ->label(__('app.actions.send_email'))
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn ($record) => FilamentAccess::allowed('send_quotation') && in_array($record->status, ['draft', 'sent']))
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
                Actions\Action::make('uploaded_pdf')
                    ->label(__('app.actions.uploaded_pdf'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->visible(fn ($record): bool => filled($record->pdf_path))
                    ->url(fn ($record): string => Storage::disk('public')->url($record->pdf_path))
                    ->openUrlInNewTab(),

                Actions\Action::make('download_pdf')
                    ->label(__('app.actions.generated_pdf'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => route('quotation.pdf', $record))
                    ->openUrlInNewTab(),

                // ── Action: Convert to PO ────────────────────────────────
                Actions\Action::make('convert_to_po')
                    ->label(__('app.actions.convert_to_po'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn (Quotation $record): bool => FilamentAccess::allowed('convert_quotation') && $record->canBeConverted())
                    ->requiresConfirmation()
                    ->modalHeading('Convert ke Purchase Order?')
                    ->modalDescription('PO dan Job Order akan otomatis dibuat dari quotation ini.')
                    ->action(function (Quotation $record) {
                        if (! $record->canBeConverted()) {
                            Notification::make()
                                ->title('Quotation tidak bisa dikonversi')
                                ->body('Pastikan status sudah disetujui dan belum memiliki PO.')
                                ->warning()
                                ->send();

                            return;
                        }

                        app(\App\Actions\ConvertQuotationToPo::class)->execute($record);

                        Notification::make()
                            ->title('PO & Job Order berhasil dibuat!')
                            ->success()->send();
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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

        $user = auth()->user();

        if ($user && $user->hasRole('sales') && ! $user->hasRole('admin')) {
            $query->where('created_by', auth()->id());
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowed('view_quotation');
    }

    public static function canCreate(): bool
    {
        return FilamentAccess::allowed('create_quotation');
    }

    public static function canEdit(Model $record): bool
    {
        return FilamentAccess::allowed('edit_quotation');
    }

    public static function canDelete(Model $record): bool
    {
        return FilamentAccess::allowed('delete_quotation');
    }
}
