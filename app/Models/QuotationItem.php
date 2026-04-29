<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'part_name', 'material', 'qty',
        'satuan', 'harga_satuan', 'subtotal', 'keterangan', 'urutan',
    ];

    protected $casts = [
        'qty'          => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    // Auto-calculate subtotal before save
    protected static function booted(): void
    {
        static::saving(function (QuotationItem $item) {
            $item->subtotal = $item->qty * $item->harga_satuan;
        });

        static::saved(function (QuotationItem $item) {
            $item->quotation->recalculateTotal();
        });

        static::deleted(function (QuotationItem $item) {
            $item->quotation->recalculateTotal();
        });
    }
}