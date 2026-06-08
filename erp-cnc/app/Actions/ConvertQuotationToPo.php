<?php

namespace App\Actions;

use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\Po;
use App\Models\PoItem;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class ConvertQuotationToPo
{
    public function execute(Quotation $quotation): Po
    {
        return DB::transaction(function () use ($quotation) {
            $quotation->load('items');
            $quotation->refresh();

            if (! $quotation->canBeConverted()) {
                throw new \RuntimeException('Quotation sudah pernah dikonversi atau belum disetujui.');
            }

            if ($quotation->items->isEmpty()) {
                throw new \RuntimeException('Quotation tidak memiliki item. Tambahkan minimal satu item sebelum konversi.');
            }

            // 1. Buat PO dari Quotation
            $po = Po::create([
                'nomor_po'         => Po::generateNomor(),
                'quotation_id'     => $quotation->id,
                'customer_id'      => $quotation->customer_id,
                'created_by'       => auth()->id() ?? $quotation->created_by,
                'tanggal_po'       => today(),
                'estimasi_selesai' => today()->addDays(14),
                'status'           => 'pending',
                'total'            => $quotation->total_harga,
                'catatan'          => "Converted dari {$quotation->nomor}",
            ]);

            // 2. Copy Quotation Items ke PO Items
            $poItems = [];
            foreach ($quotation->items as $qItem) {
                $poItems[] = PoItem::create([
                    'po_id'              => $po->id,
                    'quotation_item_id'  => $qItem->id,
                    'part_name'          => $qItem->part_name,
                    'material'           => $qItem->material,
                    'qty'                => $qItem->qty,
                    'satuan'             => $qItem->satuan,
                    'harga_satuan'       => $qItem->harga_satuan,
                    'subtotal'           => $qItem->subtotal,
                    'keterangan'         => $qItem->keterangan,
                    'urutan'             => $qItem->urutan,
                ]);
            }

            // 3. Buat Job Order dari PO
            $jobOrder = JobOrder::create([
                'nomor_job'        => JobOrder::generateNomor(),
                'po_id'            => $po->id,
                'status'           => 'pending',
                'estimasi_selesai' => $po->estimasi_selesai,
                'catatan'          => "Auto-generated dari PO {$po->nomor_po}",
                'progress_persen'  => 0,
            ]);

            // 4. Copy PO Items ke Job Order Items
            foreach ($poItems as $poItem) {
                JobOrderItem::create([
                    'job_order_id' => $jobOrder->id,
                    'po_item_id'   => $poItem->id,
                    'part_name'    => $poItem->part_name,
                    'material'     => $poItem->material,
                    'qty'          => $poItem->qty,
                    'satuan'       => $poItem->satuan,
                    'status'       => 'pending',
                    'keterangan'   => $poItem->keterangan,
                    'urutan'       => $poItem->urutan,
                ]);
            }

            // 5. Update status quotation
            $quotation->update(['status' => 'converted']);

            // 6. Fire event untuk notifikasi/logging
            event(new \App\Events\QuotationConverted($quotation, $po));

            return $po;
        });
    }
}
