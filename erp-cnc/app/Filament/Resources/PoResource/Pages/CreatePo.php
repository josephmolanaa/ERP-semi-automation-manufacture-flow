<?php

namespace App\Filament\Resources\PoResource\Pages;

use App\Filament\Resources\PoResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePo extends CreateRecord
{
    protected static string $resource = PoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
