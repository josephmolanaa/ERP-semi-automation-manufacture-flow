<?php

use App\Http\Controllers\QuotationApprovalController;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/quotation/approve/{token}', [QuotationApprovalController::class, 'approve'])
    ->name('quotation.approve');

Route::get('/quotation/{quotation}/pdf', function (Quotation $quotation) {
    return Pdf::loadView('pdf.quotation', [
        'quotation' => $quotation,
        'items' => $quotation->items,
        'customer' => $quotation->customer,
    ])->stream("Penawaran-{$quotation->nomor}.pdf");
})->name('quotation.pdf');
