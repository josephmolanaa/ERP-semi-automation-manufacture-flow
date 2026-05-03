<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratJalan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_sj', 'job_order_id', 'created_by', 'tanggal_kirim',
        'ekspedisi', 'no_resi', 'penerima', 'alamat_kirim',
        'status', 'diterima_at', 'catatan',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'diterima_at'   => 'datetime',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'sj_id');
    }

    public static function generateNomor(): string
    {
        $prefix = 'SJ-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('nomor_sj', 'like', $prefix . '%')
            ->orderByDesc('nomor_sj')
            ->value('nomor_sj');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}