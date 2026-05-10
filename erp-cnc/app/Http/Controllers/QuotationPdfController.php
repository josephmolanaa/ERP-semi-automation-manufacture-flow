<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationPdfController extends Controller
{
    public function __invoke(Quotation $quotation)
    {
        return Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'items' => $quotation->items,
            'customer' => $quotation->customer,
        ])->stream("Penawaran-{$quotation->nomor}.pdf");
    }
}
