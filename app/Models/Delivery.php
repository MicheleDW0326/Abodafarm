<?php

namespace App\Models;

use App\Enums\ShipmentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'delivery_code',
        'assigned_staff_id',
        'assigned_by',
        'tracking_number',
        'courier_name',
        'vehicle_type',
        'delivery_method',
        'estimated_delivery',
        'shipped_at',
        'delivered_at',
        'status',
        'previous_status',
        'proof_photo',
        'customer_signature',
        'delivery_notes',
        'receiver_name',
        'latitude',
        'longitude',
        'attempt_count',
        'last_attempt_at',
        'failure_reason',
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'attempt_count' => 'integer',
    ];

    // Status constants
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY_FOR_DELIVERY = 'ready_for_delivery';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'delivery_failed';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(DeliveryTimeline::class)->orderBy('created_at', 'asc');
    }

    // Status helpers
    public function getStatusEnumAttribute(): ?ShipmentStatusEnum
    {
        return $this->status ? ShipmentStatusEnum::tryFrom($this->status) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusEnum?->label() ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute(): string
    {
        return $this->statusEnum?->color() ?? 'gray';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->statusEnum?->badgeClass() ?? 'bg-gray-100 text-gray-700';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->statusEnum?->icon() ?? 'fa-circle';
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
            self::STATUS_RETURNED,
        ]);
    }

    public function isActive(): bool
    {
        return !$this->isTerminal();
    }

    // Proof helpers
    public function getProofPhotoUrlAttribute(): ?string
    {
        if (!$this->proof_photo) {
            return null;
        }
        return asset('storage/' . $this->proof_photo);
    }

    public function getCustomerSignatureUrlAttribute(): ?string
    {
        if (!$this->customer_signature) {
            return null;
        }
        return asset('storage/' . $this->customer_signature);
    }

    public function hasProof(): bool
    {
        return $this->proof_photo !== null || $this->customer_signature !== null;
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (!$this->hasLocation()) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    // Timeline helpers
    public function addTimelineEntry(string $status, ?string $notes = null, ?int $createdBy = null, string $createdByType = 'system'): DeliveryTimeline
    {
        $statusEnum = ShipmentStatusEnum::tryFrom($status);

        return $this->timeline()->create([
            'status' => $status,
            'status_label' => $statusEnum?->label() ?? $status,
            'notes' => $notes,
            'created_by' => $createdBy,
            'created_by_type' => $createdByType,
            'timestamp' => now(),
        ]);
    }

    public function getFormattedEstimatedDeliveryAttribute(): ?string
    {
        if (!$this->estimated_delivery) {
            return null;
        }
        return $this->estimated_delivery->translatedFormat('d M Y, H:i');
    }

    public function getFormattedShippedAtAttribute(): ?string
    {
        if (!$this->shipped_at) {
            return null;
        }
        return $this->shipped_at->translatedFormat('d M Y, H:i');
    }

    public function getFormattedDeliveredAtAttribute(): ?string
    {
        if (!$this->delivered_at) {
            return null;
        }
        return $this->delivered_at->translatedFormat('d M Y, H:i');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
            self::STATUS_RETURNED,
        ]);
    }

    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('assigned_staff_id', $staffId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_staff_id');
    }

    public function scopeReadyForDelivery($query)
    {
        return $query->where('status', self::STATUS_READY_FOR_DELIVERY);
    }

    public function scopeAssignedTo($query, int $staffId)
    {
        return $query->where('assigned_staff_id', $staffId);
    }

    // Static methods
    public static function generateDeliveryCode(): string
    {
        return 'DLV-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
    }

    // Vehicle types
    public static array $vehicleTypes = [
        'motor' => 'Motor',
        'mobil' => 'Mobil',
        'van' => 'Van/Pickup',
        'truck' => 'Truk',
    ];

    // Delivery methods
    public static array $deliveryMethods = [
        'delivery' => 'Dikirim ke Pelanggan',
        'pickup' => 'Ambil di Tempat',
    ];
}
