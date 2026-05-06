<?php

namespace App\Filament\Resources\PoResource\Pages;

use App\Filament\Resources\PoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePo extends CreateRecord
{
    protected static string $resource = PoResource::class;
}
