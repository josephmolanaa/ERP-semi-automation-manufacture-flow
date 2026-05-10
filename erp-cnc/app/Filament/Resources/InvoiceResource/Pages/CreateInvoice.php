<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status_bayar'] = \App\Models\Invoice::paymentStatus(
            (float) ($data['total'] ?? 0),
            (float) ($data['jumlah_bayar'] ?? 0),
        );
        $data['paid_at'] = ($data['status_bayar'] ?? null) === 'paid' ? now() : null;

        return $data;
    }
}
