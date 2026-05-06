<?php

namespace App\Filament\Resources\PoResource\Pages;

use App\Filament\Resources\PoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPos extends ListRecords
{
    protected static string $resource = PoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
