<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoResource\Pages;
use App\Models\Po;
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

class PoResource extends Resource
{
    protected static ?string $model = Po::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static UnitEnum|string|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi PO')
                    ->schema([
                        TextInput::make('nomor_po')
                            ->label('Nomor PO')
                            ->default(fn () => Po::generateNomor())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('quotation_id')
                            ->label('Quotation')
                            ->relationship('quotation', 'nomor')
                            ->searchable()
                            ->preload(),

                        Select::make('status')
                            ->options(Po::STATUS_LABELS)
                            ->default('pending')
                            ->required(),

                        DatePicker::make('tanggal_po')
                            ->label('Tanggal PO')
                            ->default(today())
                            ->required(),

                        DatePicker::make('estimasi_selesai')
                            ->label('Estimasi Selesai'),

                        TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),

                        FileUpload::make('pdf_path')
                            ->label('Upload PDF PO')
                            ->disk('public')
                            ->directory('documents/pos')
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
                TextColumn::make('nomor_po')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quotation.nomor')
                    ->label('Quotation')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('tanggal_po')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('estimasi_selesai')
                    ->label('Estimasi')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'proses' => 'info',
                        'selesai' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('total')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->pdf_path)),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_po', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(Po::STATUS_LABELS),
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
            'index' => Pages\ListPos::route('/'),
            'create' => Pages\CreatePo::route('/create'),
            'edit' => Pages\EditPo::route('/{record}/edit'),
        ];
    }
}
