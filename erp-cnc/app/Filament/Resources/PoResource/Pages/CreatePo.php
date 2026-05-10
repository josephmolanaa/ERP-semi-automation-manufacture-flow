<?php

namespace App\Filament\Resources\PoResource\Pages;

use App\Filament\Resources\PoResource;
use App\Models\JobOrder;
use Filament\Resources\Pages\CreateRecord;

class CreatePo extends CreateRecord
{
    protected static string $resource = PoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->jobOrders()->exists()) {
            return;
        }

        JobOrder::create([
            'nomor_job' => JobOrder::generateNomor(),
            'po_id' => $this->record->id,
            'status' => 'pending',
            'estimasi_selesai' => $this->record->estimasi_selesai,
            'catatan' => "Auto-generated dari PO {$this->record->nomor_po}",
            'progress_persen' => 0,
        ]);
    }
}
