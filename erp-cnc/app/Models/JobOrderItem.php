<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderItem extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'proses'  => 'Proses',
        'selesai' => 'Selesai',
    ];

    protected $fillable = [
        'job_order_id', 'po_item_id', 'part_name', 'material', 'qty',
        'satuan', 'status', 'keterangan', 'catatan_produksi', 'urutan',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PoItem::class);
    }
}
