<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Actions;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('app.groups.master_data');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.resources.customers');
    }

    public static function getModelLabel(): string
    {
        return __('app.resources.customers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('app.sections.customer_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('app.fields.pic'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('company')
                            ->label(__('app.fields.company'))
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('app.fields.phone'))
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(30),

                        Toggle::make('is_active')
                            ->label(__('app.fields.active'))
                            ->default(true),

                        Textarea::make('address')
                            ->label(__('app.fields.address'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company')
                    ->label(__('app.fields.company'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('name')
                    ->label(__('app.fields.pic'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('phone')
                    ->label(__('app.fields.phone'))
                    ->searchable()
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label(__('app.fields.active'))
                    ->boolean(),

                TextColumn::make('quotations_count')
                    ->label('Penawaran')
                    ->counts('quotations')
                    ->sortable(),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->actions([
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
