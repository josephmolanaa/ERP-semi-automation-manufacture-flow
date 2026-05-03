<?php
// app/Observers/QuotationObserver.php

namespace App\Observers;

use App\Models\Quotation;

class QuotationObserver
{
    public function creating(Quotation $quotation): void
    {
        // Auto-generate nomor jika belum diset
        if (empty($quotation->nomor)) {
            $quotation->nomor = Quotation::generateNomor();
        }

        // Auto-set created_by
        if (empty($quotation->created_by) && auth()->check()) {
            $quotation->created_by = auth()->id();
        }
    }
}

// Daftarkan di AppServiceProvider:
// Quotation::observe(QuotationObserver::class);