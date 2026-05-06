import os

base_dir = r"g:\VSCodePortable\data\ERP Automation Manufacture Models\erp-cnc\app\Filament\Resources"

resources = [
    {"model": "Customer", "icon": "heroicon-o-users", "group": "Master Data"},
    {"model": "Po", "icon": "heroicon-o-shopping-cart", "group": "Sales"},
    {"model": "JobOrder", "icon": "heroicon-o-cog-6-tooth", "group": "Produksi"},
    {"model": "Invoice", "icon": "heroicon-o-document-currency-dollar", "group": "Finance"},
    {"model": "SuratJalan", "icon": "heroicon-o-truck", "group": "Sales"}
]

for res in resources:
    model = res["model"]
    res_name = f"{model}Resource"
    icon = res["icon"]
    group = res["group"]
    
    # Create directory
    res_dir = os.path.join(base_dir, res_name)
    pages_dir = os.path.join(res_dir, "Pages")
    os.makedirs(pages_dir, exist_ok=True)
    
    # Resource class
    resource_code = f"""<?php

namespace App\\Filament\\Resources;

use App\\Filament\\Resources\\{res_name}\\Pages;
use App\\Models\\{model};
use Filament\\Forms;
use Filament\\Schemas\\Schema;
use Filament\\Resources\\Resource;
use Filament\\Tables;
use Filament\\Tables\\Table;
use Illuminate\\Database\\Eloquent\\Builder;

class {res_name} extends Resource
{{
    protected static ?string $model = {model}::class;
    protected static ?string $navigationIcon = '{icon}';
    protected static ?string $navigationGroup = '{group}';

    public static function form(Schema $schema): Schema
    {{
        return $schema
            ->components([
                Forms\\Components\\Section::make('Informasi {model}')
                    ->schema([
                        // Placeholder for fields
                    ]),
            ]);
    }}

    public static function table(Table $table): Table
    {{
        return $table
            ->columns([
                Tables\\Columns\\TextColumn::make('id')->sortable(),
                Tables\\Columns\\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\\Columns\\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\\Actions\\EditAction::make(),
            ])
            ->bulkActions([
                Tables\\Actions\\BulkActionGroup::make([
                    Tables\\Actions\\DeleteBulkAction::make(),
                ]),
            ]);
    }}

    public static function getRelations(): array
    {{
        return [
            //
        ];
    }}

    public static function getPages(): array
    {{
        return [
            'index' => Pages\\List{model}s::route('/'),
            'create' => Pages\\Create{model}::route('/create'),
            'edit' => Pages\\Edit{model}::route('/{{record}}/edit'),
        ];
    }}
}}
"""
    with open(os.path.join(base_dir, f"{res_name}.php"), "w", encoding="utf-8") as f:
        f.write(resource_code)

    # Pages
    list_code = f"""<?php

namespace App\\Filament\\Resources\\{res_name}\\Pages;

use App\\Filament\\Resources\\{res_name};
use Filament\\Actions;
use Filament\\Resources\\Pages\\ListRecords;

class List{model}s extends ListRecords
{{
    protected static string $resource = {res_name}::class;

    protected function getHeaderActions(): array
    {{
        return [
            Actions\\CreateAction::make(),
        ];
    }}
}}
"""
    with open(os.path.join(pages_dir, f"List{model}s.php"), "w", encoding="utf-8") as f:
        f.write(list_code)

    create_code = f"""<?php

namespace App\\Filament\\Resources\\{res_name}\\Pages;

use App\\Filament\\Resources\\{res_name};
use Filament\\Actions;
use Filament\\Resources\\Pages\\CreateRecord;

class Create{model} extends CreateRecord
{{
    protected static string $resource = {res_name}::class;
}}
"""
    with open(os.path.join(pages_dir, f"Create{model}.php"), "w", encoding="utf-8") as f:
        f.write(create_code)

    edit_code = f"""<?php

namespace App\\Filament\\Resources\\{res_name}\\Pages;

use App\\Filament\\Resources\\{res_name};
use Filament\\Actions;
use Filament\\Resources\\Pages\\EditRecord;

class Edit{model} extends EditRecord
{{
    protected static string $resource = {res_name}::class;

    protected function getHeaderActions(): array
    {{
        return [
            Actions\\DeleteAction::make(),
        ];
    }}
}}
"""
    with open(os.path.join(pages_dir, f"Edit{model}.php"), "w", encoding="utf-8") as f:
        f.write(edit_code)

print("All resources generated successfully!")
