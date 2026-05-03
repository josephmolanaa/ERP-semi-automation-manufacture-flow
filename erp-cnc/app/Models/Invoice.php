<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_invoice', 'sj_id', 'created_by', 'tanggal',
        'jatuh_tempo', 'total', 'jumlah_bayar', 'status_bayar',
        'paid_at', 'catatan',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'jatuh_tempo'  => 'date',
        'paid_at'      => 'datetime',
        'total'        => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
    ];

    public function suratJalan(): BelongsTo
    {
        return $this->belongsTo(SuratJalan::class, 'sj_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNomor(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('nomor_invoice', 'like', $prefix . '%')
            ->orderByDesc('nomor_invoice')
            ->value('nomor_invoice');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getSisaTagihanAttribute(): float
    {
        return $this->total - $this->jumlah_bayar;
    }
}