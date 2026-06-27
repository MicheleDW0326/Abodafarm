<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address',
        'province',
        'city',
        'district',
        'postal_code',
        'is_default',
        'is_active',
        'latitude',
        'longitude',
        'google_place_id',
        'courier_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->district,
            $this->city,
            $this->province,
        ]);

        return implode(', ', $parts) . ($this->postal_code ? ' ' . $this->postal_code : '');
    }

    /**
     * Set this address as default and unset others.
     */
    public function setAsDefault(): void
    {
        // Unset all other defaults for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Scope for default address.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for user's addresses.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for active addresses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted address for display.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->district,
            $this->city,
            $this->province,
        ]);

        $formatted = implode(', ', $parts);
        if ($this->postal_code) {
            $formatted .= ', ' . $this->postal_code;
        }

        return $formatted;
    }

    /**
     * Get short address (just street address).
     */
    public function getShortAddressAttribute(): string
    {
        return $this->address ?? '';
    }

    /**
     * Check if address has coordinates.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Calculate distance to another point (in km).
     * Uses Haversine formula.
     */
    public function distanceTo(float $lat, float $lng): float
    {
        if (!$this->hasCoordinates()) {
            return -1;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad((float) $this->latitude);
        $lonFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if within service radius (in km).
     * Default radius is 100km (can be configured).
     */
    public function isWithinServiceRadius(float $centerLat, float $centerLng, float $radiusKm = 100): bool
    {
        $distance = $this->distanceTo($centerLat, $centerLng);

        if ($distance < 0) {
            return false; // No coordinates
        }

        return $distance <= $radiusKm;
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }
}
