<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';

    protected $fillable = [
        'product_id',
        'quantity',
        'date',
        'type',
        'notes',
        'health_status',
        'vaccination_records',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeRestock($query)
    {
        return $query->where('type', 'restock');
    }

    public function scopeSale($query)
    {
        return $query->where('type', 'sale');
    }

    public function scopeTransfer($query)
    {
        return $query->where('type', 'transfer');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'restock' => 'Restock',
            'sale' => 'Penjualan',
            'death' => 'Kematian',
            'transfer' => 'Transfer',
            'adjustment' => 'Penyesuaian',
            default => $this->type,
        };
    }
}
