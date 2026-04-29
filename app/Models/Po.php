<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Po extends Model
{
    use SoftDeletes;

    protected $table = 'pos';

    protected $fillable = [
        'nomor_po', 'quotation_id', 'customer_id', 'created_by',
        'tanggal_po', 'estimasi_selesai', 'status', 'total', 'catatan',
    ];

    protected $casts = [
        'tanggal_po'       => 'date',
        'estimasi_selesai' => 'date',
        'total'            => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function jobOrder(): HasOne
    {
        return $this->hasOne(JobOrder::class)->latestOfMany();
    }

    public static function generateNomor(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('nomor_po', 'like', $prefix . '%')
            ->orderByDesc('nomor_po')
            ->value('nomor_po');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}