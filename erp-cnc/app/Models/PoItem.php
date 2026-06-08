<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoItem extends Model
{
    protected $fillable = [
        'po_id', 'quotation_item_id', 'part_name', 'material', 'qty',
        'satuan', 'harga_satuan', 'subtotal', 'keterangan', 'urutan',
    ];

    protected $casts = [
        'qty'          => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class);
    }

    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(QuotationItem::class);
    }

    public function jobOrderItems(): HasMany
    {
        return $this->hasMany(JobOrderItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function (PoItem $item) {
            $item->subtotal = $item->qty * $item->harga_satuan;
        });
    }
}
