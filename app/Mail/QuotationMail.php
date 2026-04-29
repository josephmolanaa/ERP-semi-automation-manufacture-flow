<?php
// app/Mail/QuotationMail.php

namespace App\Mail;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Quotation $quotation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Penawaran Harga {$this->quotation->nomor} - CNC Flow",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation',
            with: [
                'quotation'   => $this->quotation,
                'customer'    => $this->quotation->customer,
                'approvalUrl' => $this->quotation->approval_url,
            ]
        );
    }

    public function attachments(): array
    {
        // Generate PDF on-the-fly
        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $this->quotation,
            'items'     => $this->quotation->items,
            'customer'  => $this->quotation->customer,
        ]);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "Penawaran-{$this->quotation->nomor}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}