<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_name',
        'city',
        'province',
        'fee',
        'estimated_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'estimated_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getFormattedFeeAttribute(): string
    {
        if ($this->fee == 0) {
            return 'Gratis';
        }
        return 'Rp ' . number_format($this->fee, 0, ',', '.');
    }

    public function getEstimatedDeliveryLabelAttribute(): string
    {
        if ($this->estimated_days == 1) {
            return '1 Hari';
        }
        return $this->estimated_days . ' Hari';
    }
}
