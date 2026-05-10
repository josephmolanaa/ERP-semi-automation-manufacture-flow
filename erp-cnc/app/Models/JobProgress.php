<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProgress extends Model
{
    protected $table = 'job_progress';

    public const TAHAP_LABELS = [
        'design' => 'Design',
        'machining' => 'Machining',
        'assembly' => 'Assembly',
        'qc' => 'Quality Control',
        'finishing' => 'Finishing',
    ];

    public const DEFAULT_DURASI_MENIT = 0;

    protected $fillable = [
        'job_order_id', 'operator_id', 'tahap',
        'tanggal', 'catatan', 'foto_paths', 'durasi_menit',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'foto_paths' => 'array',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function getTahapLabelAttribute(): string
    {
        return self::TAHAP_LABELS[$this->tahap] ?? $this->tahap;
    }
}
