<?php
// app/Http/Controllers/QuotationApprovalController.php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Actions\ConvertQuotationToPo;
use Illuminate\Http\Request;

class QuotationApprovalController extends Controller
{
    public function approve(Request $request, string $token)
    {
        $quotation = Quotation::where('approval_token', $token)
            ->where('status', 'sent')
            ->where('berlaku_sampai', '>=', today())
            ->firstOrFail();

        $action = $request->query('action', 'approve');

        if ($action === 'approve') {
            $quotation->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            // Notif ke sales via database notification
            $quotation->createdBy->notify(
                new \App\Notifications\QuotationApprovedNotification($quotation)
            );

            return view('approval.success', [
                'quotation' => $quotation,
                'message'   => 'Penawaran berhasil disetujui! Tim kami akan segera menghubungi Anda.',
            ]);
        }

        if ($action === 'reject') {
            $quotation->update(['status' => 'rejected']);

            return view('approval.success', [
                'quotation' => $quotation,
                'message'   => 'Penawaran telah ditolak. Terima kasih atas responnya.',
            ]);
        }

        abort(400);
    }
}

// Route di routes/web.php:
// Route::get('/quotation/approve/{token}', [QuotationApprovalController::class, 'approve'])
//      ->name('quotation.approve');