<?php
// app/Events/QuotationConverted.php

namespace App\Events;

use App\Models\Po;
use App\Models\Quotation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationConverted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Quotation $quotation,
        public readonly Po $po,
    ) {}
}