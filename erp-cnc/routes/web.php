<?php

use App\Http\Controllers\QuotationApprovalController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\QuotationPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::redirect('/', '/admin');

Route::get('/quotation/approve/{token}', [QuotationApprovalController::class, 'approve'])
    ->name('quotation.approve');

Route::get('/quotation/{quotation}/pdf', QuotationPdfController::class)
    ->name('quotation.pdf');
