<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'care_type',
        'date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeVaccination($query)
    {
        return $query->where('care_type', 'vaccination');
    }

    public function scopeHealthCheck($query)
    {
        return $query->where('care_type', 'health_check');
    }

    public function scopeFeeding($query)
    {
        return $query->where('care_type', 'feeding');
    }

    public function getCareTypeLabelAttribute(): string
    {
        return match($this->care_type) {
            'feeding' => 'Pemberian Pakan',
            'vaccination' => 'Vaksinasi',
            'health_check' => 'Pemeriksaan Kesehatan',
            'cleaning' => 'Pembersihan',
            'other' => 'Lainnya',
            default => $this->care_type,
        };
    }
}
