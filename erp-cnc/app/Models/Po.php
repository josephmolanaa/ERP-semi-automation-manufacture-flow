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

    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'proses' => 'Proses',
        'selesai' => 'Selesai',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'pending' => 'gray',
        'proses' => 'info',
        'selesai' => 'success',
        'cancelled' => 'danger',
    ];

    public const STATUS_ICONS = [
        'pending' => 'heroicon-m-clock',
        'proses' => 'heroicon-m-cog-6-tooth',
        'selesai' => 'heroicon-m-check-circle',
        'cancelled' => 'heroicon-m-x-circle',
    ];

    public const STATUS_DESCRIPTIONS = [
        'pending' => 'Menunggu produksi dimulai',
        'proses' => 'Sedang diproses oleh produksi',
        'selesai' => 'PO sudah selesai diproduksi',
        'cancelled' => 'PO dibatalkan',
    ];

    protected $fillable = [
        'nomor_po', 'quotation_id', 'customer_id', 'created_by',
        'tanggal_po', 'estimasi_selesai', 'status', 'total', 'catatan', 'pdf_path',
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

    public function getStatusLabelAttribute(): string
    {
        return __('app.statuses.' . $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusIconAttribute(): string
    {
        return self::STATUS_ICONS[$this->status] ?? 'heroicon-m-question-mark-circle';
    }

    public function getStatusDescriptionAttribute(): string
    {
        return __('app.status_descriptions.po_' . $this->status);
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

    public function syncStatusFromJobs(): void
    {
        if ($this->jobOrders()->where('status', 'finished')->exists()) {
            $this->update(['status' => 'selesai']);
            return;
        }

        if ($this->jobOrders()->whereNotIn('status', ['pending', 'delayed'])->exists()) {
            $this->update(['status' => 'proses']);
        }
    }
}
