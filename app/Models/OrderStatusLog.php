<?php

namespace App\Models;

use App\Enums\BookingStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ShipmentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * We only use created_at, not updated_at.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'triggered_by_type',
        'triggered_by_id',
        'notes',
        'ip_address',
        'created_at',
        // New FK columns for status architecture
        'order_status_id',
        'booking_status_id',
        'shipment_status_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'triggered_by_id' => 'integer',
            'created_at' => 'datetime',
            // New FK columns
            'order_status_id' => 'integer',
            'booking_status_id' => 'integer',
            'shipment_status_id' => 'integer',
        ];
    }

    // =========================================================================
    // CONSTANTS
    // =========================================================================

    // Trigger type constants
    public const TRIGGER_CUSTOMER = 'customer';
    public const TRIGGER_ADMIN = 'admin';
    public const TRIGGER_STAFF = 'staff';
    public const TRIGGER_FARMER = 'farmer';
    public const TRIGGER_SYSTEM = 'system';
    public const TRIGGER_WEBHOOK = 'webhook';

    // Status label mapping (Indonesian) - Legacy
    public const STATUS_LABELS = [
        'pending' => 'Menunggu',
        'paid' => 'Dibayar',
        'payment_failed' => 'Pembayaran Gagal',
        'payment_refunded' => 'Dana Dikembalikan',
        'processing' => 'Diproses',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'shipped' => 'Dikirim',
        'delivered' => 'Diterima',
        'completed' => 'Selesai',
    ];

    // Status transition labels (Indonesian).
    public const TRANSITION_LABELS = [
        // From pending
        'pending_to_paid' => 'Pembayaran berhasil',
        'pending_to_processing' => 'Pesanan mulai diproses',
        'pending_to_approved' => 'Booking disetujui',
        'pending_to_rejected' => 'Booking ditolak',
        'pending_to_cancelled' => 'Dibatalkan',
        'pending_to_payment_failed' => 'Pembayaran gagal',

        // From paid
        'paid_to_processing' => 'Pesanan mulai diproses',
        'paid_to_cancelled' => 'Pembayaran dibatalkan',

        // From payment_failed
        'payment_failed_to_pending' => 'Pembayaran dicoba ulang',

        // From processing
        'processing_to_shipped' => 'Pesanan dikirim',
        'processing_to_cancelled' => 'Pesanan dibatalkan',

        // From shipped
        'shipped_to_delivered' => 'Pesanan diterima',

        // From delivered
        'delivered_to_completed' => 'Pesanan selesai',

        // From approved
        'approved_to_completed' => 'Booking selesai',
        'approved_to_cancelled' => 'Booking dibatalkan',
        'approved_to_processing' => 'Booking masuk tahap produksi',
        'approved_to_shipped' => 'Booking dikirim',
        'approved_to_delivered' => 'Booking diterima',

        // From rejected
        // No transitions from rejected (terminal)

        // From cancelled
        // No transitions from cancelled (terminal)

        // From completed
        // No transitions from completed (terminal)
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_id');
    }

    // New FK-based relationships
    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function bookingStatus(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'booking_status_id');
    }

    public function shipmentStatus(): BelongsTo
    {
        return $this->belongsTo(ShipmentStatus::class, 'shipment_status_id');
    }

    // =========================================================================
    // COMPUTED PROPERTIES - NEW STATUS ACCESSORS
    // =========================================================================

    /**
     * Get payment status enum value.
     */
    public function getPaymentStatusEnumAttribute(): ?PaymentStatusEnum
    {
        $code = $this->orderStatus?->code;
        return $code ? PaymentStatusEnum::tryFrom($code) : null;
    }

    /**
     * Get booking status enum value.
     */
    public function getBookingStatusEnumAttribute(): ?BookingStatusEnum
    {
        $code = $this->bookingStatus?->code;
        return $code ? BookingStatusEnum::tryFrom($code) : null;
    }

    /**
     * Get shipment status enum value.
     */
    public function getShipmentStatusEnumAttribute(): ?ShipmentStatusEnum
    {
        $code = $this->shipmentStatus?->code;
        return $code ? ShipmentStatusEnum::tryFrom($code) : null;
    }

    /**
     * Get status label from FK relationship.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->shipmentStatus?->label
            ?? $this->bookingStatus?->label
            ?? $this->orderStatus?->label
            ?? $this->toStatusLabel;
    }

    /**
     * Get badge class from FK relationship.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $color = $this->shipmentStatus?->color
            ?? $this->bookingStatus?->color
            ?? $this->orderStatus?->color;

        if ($color) {
            return "bg-{$color}-100 text-{$color}-700";
        }

        return 'bg-slate-100 text-slate-700';
    }

    // =========================================================================
    // COMPUTED PROPERTIES - LEGACY
    // =========================================================================

    /**
     * Get the status change label for display.
     */
    public function getStatusChangeLabelAttribute(): string
    {
        if ($this->from_status === null) {
            return 'Pesanan dibuat';
        }

        $key = "{$this->from_status}_to_{$this->to_status}";

        return self::TRANSITION_LABELS[$key] ?? $this->toStatusLabel;
    }

    /**
     * Get the to status label.
     */
    public function getToStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->to_status] ?? ucfirst($this->to_status ?? 'Unknown');
    }

    /**
     * Get the triggered by name.
     */
    public function getTriggeredByNameAttribute(): ?string
    {
        if ($this->triggered_by_type === self::TRIGGER_SYSTEM) {
            return 'Sistem';
        }

        if ($this->triggered_by_type === self::TRIGGER_WEBHOOK) {
            return 'Midtrans';
        }

        if ($this->triggeredBy) {
            return $this->triggeredBy->name;
        }

        return match ($this->triggered_by_type) {
            self::TRIGGER_CUSTOMER => 'Pelanggan',
            self::TRIGGER_ADMIN => 'Admin',
            self::TRIGGER_STAFF => 'Staff',
            self::TRIGGER_FARMER => 'Peternak',
            default => null,
        };
    }

    /**
     * Get the triggered by type label.
     */
    public function getTriggeredByTypeLabelAttribute(): string
    {
        return match ($this->triggered_by_type) {
            self::TRIGGER_CUSTOMER => 'Pelanggan',
            self::TRIGGER_ADMIN => 'Admin',
            self::TRIGGER_STAFF => 'Staff',
            self::TRIGGER_FARMER => 'Peternak',
            self::TRIGGER_SYSTEM => 'Sistem',
            self::TRIGGER_WEBHOOK => 'Midtrans',
            default => 'Tidak diketahui',
        };
    }

    // =========================================================================
    // BOOLEAN HELPERS
    // =========================================================================

    /**
     * Check if this is the initial status (order creation).
     */
    public function isInitialStatus(): bool
    {
        return $this->from_status === null;
    }

    /**
     * Check if this is a terminal status (no further transitions allowed).
     */
    public function isTerminalStatus(): bool
    {
        // Check new FK columns first
        if ($this->shipmentStatus?->code === 'completed') return true;
        if ($this->shipmentStatus?->code === 'cancelled') return true;
        if ($this->bookingStatus?->is_cancelled) return true;
        if ($this->orderStatus?->is_terminal) return true;

        // Fallback to legacy check
        return in_array($this->to_status, ['completed', 'rejected', 'cancelled', 'payment_failed', 'payment_refunded']);
    }

    // =========================================================================
    // STATIC METHODS
    // =========================================================================

    /**
     * Create a log entry for an order.
     */
    public static function log(
        Order $order,
        ?string $fromStatus,
        string $toStatus,
        string $triggeredByType = self::TRIGGER_SYSTEM,
        ?int $triggeredById = null,
        ?string $notes = null,
        ?string $ipAddress = null
    ): self {
        return self::create([
            'order_id' => $order->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'triggered_by_type' => $triggeredByType,
            'triggered_by_id' => $triggeredById,
            'notes' => $notes,
            'ip_address' => $ipAddress ?? request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Create initial log for order creation.
     */
    public static function logCreation(
        Order $order,
        string $triggeredByType = self::TRIGGER_CUSTOMER,
        ?int $triggeredById = null,
        ?string $notes = null
    ): self {
        return self::log(
            $order,
            null,
            $order->status,
            $triggeredByType,
            $triggeredById,
            $notes ?? 'Pesanan dibuat'
        );
    }
}
