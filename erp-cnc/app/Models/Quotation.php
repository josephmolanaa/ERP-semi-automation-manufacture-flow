<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor', 'customer_id', 'created_by', 'tanggal',
        'berlaku_sampai', 'status', 'total_harga',
        'catatan', 'approval_token', 'approved_at', 'sent_at',
    ];

    protected $casts = [
        'tanggal'         => 'date',
        'berlaku_sampai'  => 'date',
        'approved_at'     => 'datetime',
        'sent_at'         => 'datetime',
        'total_harga'     => 'decimal:2',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('urutan');
    }

    public function po(): HasOne
    {
        return $this->hasOne(Po::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public static function generateNomor(): string
    {
        $prefix = 'QUO-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('nomor', 'like', $prefix . '%')
            ->orderByDesc('nomor')
            ->value('nomor');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function generateApprovalToken(): string
    {
        $token = Str::random(64);
        $this->update(['approval_token' => $token]);
        return $token;
    }

    public function getApprovalUrlAttribute(): string
    {
        return route('quotation.approve', ['token' => $this->approval_token]);
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'total_harga' => $this->items()->sum('subtotal'),
        ]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }
}