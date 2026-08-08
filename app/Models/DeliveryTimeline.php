<?php

namespace App\Models;

use App\Enums\ShipmentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTimeline extends Model
{
    protected $table = 'delivery_timeline';

    protected $fillable = [
        'delivery_id',
        'status',
        'status_label',
        'notes',
        'location',
        'latitude',
        'longitude',
        'created_by',
        'created_by_type',
        'timestamp',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'timestamp' => 'datetime',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusEnumAttribute(): ?ShipmentStatusEnum
    {
        return $this->status ? ShipmentStatusEnum::tryFrom($this->status) : null;
    }

    public function getStatusColorAttribute(): string
    {
        return $this->statusEnum?->color() ?? 'gray';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->statusEnum?->icon() ?? 'fa-circle';
    }

    public function getFormattedTimestampAttribute(): ?string
    {
        if (!$this->timestamp) {
            return null;
        }
        return $this->timestamp->translatedFormat('d M Y, H:i');
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }
}
