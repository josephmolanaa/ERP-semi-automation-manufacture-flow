<?php
// app/Actions/ConvertQuotationToPo.php

namespace App\Actions;

use App\Models\JobOrder;
use App\Models\Po;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class ConvertQuotationToPo
{
    public function execute(Quotation $quotation): Po
    {
        return DB::transaction(function () use ($quotation) {

            // 1. Buat PO dari Quotation
            $po = Po::create([
                'nomor_po'         => Po::generateNomor(),
                'quotation_id'     => $quotation->id,
                'customer_id'      => $quotation->customer_id,
                'created_by'       => auth()->id(),
                'tanggal_po'       => today(),
                'estimasi_selesai' => today()->addDays(14),
                'status'           => 'pending',
                'total'            => $quotation->total_harga,
                'catatan'          => "Converted dari {$quotation->nomor}",
            ]);

            // 2. Buat Job Order dari PO
            JobOrder::create([
                'nomor_job'        => JobOrder::generateNomor(),
                'po_id'            => $po->id,
                'status'           => 'pending',
                'estimasi_selesai' => $po->estimasi_selesai,
                'catatan'          => "Auto-generated dari PO {$po->nomor_po}",
                'progress_persen'  => 0,
            ]);

            // 3. Update status quotation
            $quotation->update(['status' => 'converted']);

            // 4. Fire event untuk notifikasi/logging
            event(new \App\Events\QuotationConverted($quotation, $po));

            return $po;
        });
    }
}