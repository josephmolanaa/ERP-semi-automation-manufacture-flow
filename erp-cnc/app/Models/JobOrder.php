<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_job', 'po_id', 'status', 'estimasi_selesai',
        'tanggal_selesai', 'catatan', 'progress_persen',
    ];

    protected $casts = [
        'estimasi_selesai' => 'date',
        'tanggal_selesai'  => 'date',
    ];

    const STATUS_LABELS = [
        'pending'   => 'Pending',
        'design'    => 'Design',
        'machining' => 'Machining',
        'assembly'  => 'Assembly',
        'qc'        => 'Quality Control',
        'finished'  => 'Selesai',
        'delayed'   => 'Delayed',
    ];

    const STATUS_PROGRESS = [
        'pending'   => 0,
        'design'    => 15,
        'machining' => 40,
        'assembly'  => 65,
        'qc'        => 85,
        'finished'  => 100,
        'delayed'   => 0,
    ];

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(JobProgress::class)->latest();
    }

    public function suratJalan(): HasOne
    {
        return $this->hasOne(SuratJalan::class);
    }

    public static function generateNomor(): string
    {
        $prefix = 'JO-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('nomor_job', 'like', $prefix . '%')
            ->orderByDesc('nomor_job')
            ->value('nomor_job');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function advanceStatus(): void
    {
        $flow = ['pending', 'design', 'machining', 'assembly', 'qc', 'finished'];
        $current = array_search($this->status, $flow);

        if ($current !== false && $current < count($flow) - 1) {
            $newStatus = $flow[$current + 1];
            $this->update([
                'status'          => $newStatus,
                'progress_persen' => self::STATUS_PROGRESS[$newStatus],
                'tanggal_selesai' => $newStatus === 'finished' ? now() : null,
            ]);
        }
    }
}