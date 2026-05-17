<?php

use App\Http\Controllers\QuotationApprovalController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\QuotationPdfController;
use App\Services\LanguageSwitcher;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::redirect('/', '/admin');

Route::get('/lang/{locale}', function (string $locale, LanguageSwitcher $switcher) {
    $switched = $switcher->switchTo($locale);

    session()->flash(
        $switched ? 'success' : 'error',
        $switched
            ? __('app.language_switched', ['locale' => __('app.locales.' . $locale)])
            : __('app.language_not_supported'),
    );

    return back();
})->name('lang.switch');

Route::get('/quotation/approve/{token}', [QuotationApprovalController::class, 'approve'])
    ->name('quotation.approve');

Route::get('/quotation/{quotation}/pdf', QuotationPdfController::class)
    ->name('quotation.pdf');
