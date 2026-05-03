<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Quotation $quotation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'quotation_id' => $this->quotation->id,
            'nomor' => $this->quotation->nomor,
            'message' => "Quotation {$this->quotation->nomor} disetujui customer.",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Quotation {$this->quotation->nomor} disetujui")
            ->line("Quotation {$this->quotation->nomor} sudah disetujui customer.");
    }
}
