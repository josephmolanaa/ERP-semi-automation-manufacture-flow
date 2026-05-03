<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'address',
        'company', 'npwp', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function pos(): HasMany
    {
        return $this->hasMany(Po::class);
    }

    // Accessor: nama display
    public function getDisplayNameAttribute(): string
    {
        return $this->company
            ? "{$this->company} ({$this->name})"
            : $this->name;
    }
}