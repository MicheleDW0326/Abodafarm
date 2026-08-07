<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

/**
 * Complete Promotion Validation Engine
 *
 * Handles promo code validation, discount calculation, and application.
 */
class PromoService
{
    private const SESSION_KEY = 'checkout.promo';

    /**
     * Validate a promo code against cart and user.
     *
     * @param string $code Promo code to validate
     * @param array $cart Cart items array with 'items' key containing products
     * @param int|null $userId User ID for per-user limits
     * @return array{valid: bool, message: string, promotion?: Promotion, reason?: string}
     */
    public function validatePromo(string $code, array $cart, ?int $userId = null): array
    {
        // Find promotion by code
        $promotion = Promotion::where('code', strtoupper(trim($code)))->first();

        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan.',
                'reason' => 'not_found',
            ];
        }

        // Calculate cart subtotal
        $subtotal = $this->calculateCartSubtotal($cart);

        // Rule 1: Check if promotion is active
        if ($promotion->status !== 'active') {
            $statusLabel = match($promotion->status) {
                'inactive' => 'tidak aktif',
                'expired' => 'kedaluwarsa',
                default => 'tidak tersedia',
            };
            return [
                'valid' => false,
                'message' => "Kode promo {$statusLabel}.",
                'reason' => 'inactive',
            ];
        }

        // Rule 2: Check date range
        $now = Carbon::now();

        if ($promotion->starts_at && $now->lt($promotion->starts_at)) {
            $startDate = $promotion->starts_at->format('d M Y');
            return [
                'valid' => false,
                'message' => "Kode promo belum berlaku. Mulai {$startDate}.",
                'reason' => 'not_started',
            ];
        }

        if ($promotion->ends_at && $now->gt($promotion->ends_at)) {
            $endDate = $promotion->ends_at->format('d M Y');
            return [
                'valid' => false,
                'message' => "Kode promo sudah berakhir pada {$endDate}.",
                'reason' => 'expired',
            ];
        }

        // Rule 3: Check global usage limit
        if ($promotion->usage_limit !== null && $promotion->used_count >= $promotion->usage_limit) {
            return [
                'valid' => false,
                'message' => 'Kode promo sudah mencapai batas penggunaan.',
                'reason' => 'usage_limit_exceeded',
            ];
        }

        // Rule 4: Check per-user usage limit
        if ($userId && $promotion->usage_per_user > 0) {
            $userUsageCount = $this->getUserUsageCount($promotion->id, $userId);
            if ($userUsageCount >= $promotion->usage_per_user) {
                return [
                    'valid' => false,
                    'message' => 'Anda sudah menggunakan kode promo ini.',
                    'reason' => 'per_user_limit_exceeded',
                ];
            }
        }

        // Rule 5: Check minimum purchase
        if ($promotion->minimum_purchase !== null && $subtotal < (float) $promotion->minimum_purchase) {
            $minFormatted = 'Rp ' . number_format($promotion->minimum_purchase, 0, ',', '.');
            $subtotalFormatted = 'Rp ' . number_format($subtotal, 0, ',', '.');
            return [
                'valid' => false,
                'message' => "Minimal pembelian {$minFormatted} untuk menggunakan kode promo ini. Total belanja Anda: {$subtotalFormatted}.",
                'reason' => 'minimum_not_met',
            ];
        }

        // Rule 6: Check product restrictions (if any)
        // This will be handled in calculateDiscount for applicable items

        return [
            'valid' => true,
            'message' => 'Kode promo berhasil diterapkan!',
            'promotion' => $promotion,
        ];
    }

    /**
     * Calculate discount amount based on promotion and cart.
     *
     * @param Promotion $promotion The promotion to apply
     * @param array $cart Cart items
     * @param float|null $shippingCost Shipping cost (for free_shipping type)
     * @return array{discount_amount: float, applicable_items: array, details: array}
     */
    public function calculateDiscount(Promotion $promotion, array $cart, ?float $shippingCost = null): array
    {
        $subtotal = $this->calculateCartSubtotal($cart);
        $items = $cart['items'] ?? $cart;

        // For free_shipping, discount is the shipping cost
        if ($promotion->type === 'free_shipping') {
            return [
                'discount_amount' => $shippingCost ?? 0,
                'applicable_items' => $items,
                'details' => [
                    'type' => 'free_shipping',
                    'shipping_covered' => $shippingCost ?? 0,
                ],
            ];
        }

        $discount = 0;
        $applicableItems = [];

        // Calculate discount based on type
        if ($promotion->type === 'percentage') {
            // Percentage discount on applicable items
            foreach ($items as $item) {
                // TODO: Check if item's category/product is restricted
                // For now, apply to all items
                $itemSubtotal = (float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 1);
                $itemDiscount = $itemSubtotal * ((float) $promotion->value / 100);

                // Apply max_discount cap if set
                if ($promotion->maximum_discount !== null && $itemDiscount > (float) $promotion->maximum_discount) {
                    $itemDiscount = (float) $promotion->maximum_discount;
                }

                $discount += $itemDiscount;
                $applicableItems[] = $item;
            }
        } elseif ($promotion->type === 'fixed') {
            // Fixed discount (capped at subtotal)
            $discount = min((float) $promotion->value, $subtotal);
            $applicableItems = $items;
        }

        // Ensure discount doesn't exceed subtotal
        $discount = min($discount, $subtotal);

        return [
            'discount_amount' => round($discount, 0),
            'applicable_items' => $applicableItems,
            'details' => [
                'type' => $promotion->type,
                'value' => $promotion->value,
                'subtotal' => $subtotal,
            ],
        ];
    }

    /**
     * Get the discount amount for the applied promo.
     *
     * @param array $cart Cart items
     * @param float|null $shippingCost Shipping cost
     * @return float
     */
    public function getDiscountAmount(array $cart, ?float $shippingCost = null): float
    {
        $applied = $this->getApplied();

        if (!$applied || !isset($applied['code'])) {
            return 0;
        }

        $promotion = Promotion::where('code', $applied['code'])->first();

        if (!$promotion) {
            return 0;
        }

        $result = $this->calculateDiscount($promotion, $cart, $shippingCost);

        return $result['discount_amount'];
    }

    /**
     * Calculate final total with discount.
     *
     * @param array $cart Cart items
     * @param float $shipping Shipping cost
     * @return array{subtotal: float, discount: float, shipping: float, grand_total: float}
     */
    public function getFinalTotal(array $cart, float $shipping = 0): array
    {
        $subtotal = $this->calculateCartSubtotal($cart);
        $discount = $this->getDiscountAmount($cart, $shipping);

        // Free shipping means shipping is 0
        $applied = $this->getApplied();
        if ($applied && isset($applied['type']) && $applied['type'] === 'free_shipping') {
            $shipping = 0;
        }

        $grandTotal = max(0, $subtotal - $discount + $shipping);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Apply a promo code to the current session.
     *
     * @param string $code Promo code
     * @param array $cart Cart items
     * @param int|null $userId User ID
     * @param float $shipping Shipping cost
     * @return array{valid: bool, message: string, discount?: float, promo?: array}
     */
    public function apply(string $code, array $cart, ?int $userId = null, float $shipping = 0): array
    {
        // Validate the promo
        $validation = $this->validatePromo($code, $cart, $userId);

        if (!$validation['valid']) {
            return [
                'valid' => false,
                'message' => $validation['message'],
            ];
        }

        $promotion = $validation['promotion'];

        // Calculate discount
        $discountResult = $this->calculateDiscount($promotion, $cart, $shipping);
        $subtotal = $this->calculateCartSubtotal($cart);

        // Store in session
        $sessionData = [
            'code' => $promotion->code,
            'name' => $promotion->name,
            'type' => $promotion->type,
            'discount_amount' => $discountResult['discount_amount'],
            'applied_at' => now()->toIso8601String(),
        ];

        Session::put(self::SESSION_KEY, $sessionData);

        return [
            'valid' => true,
            'message' => 'Kode promo berhasil diterapkan!',
            'discount' => $discountResult['discount_amount'],
            'promo' => $sessionData,
            'subtotal_after_discount' => $subtotal - $discountResult['discount_amount'],
            'shipping' => $promotion->type === 'free_shipping' ? 0 : $shipping,
            'grand_total' => $promotion->type === 'free_shipping'
                ? $subtotal - $discountResult['discount_amount']
                : $subtotal - $discountResult['discount_amount'] + $shipping,
        ];
    }

    /**
     * Remove the applied promo from session.
     *
     * @return void
     */
    public function remove(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get the currently applied promo from session.
     *
     * @return array|null
     */
    public function getApplied(): ?array
    {
        return Session::get(self::SESSION_KEY);
    }

    /**
     * Check if a promo is currently applied.
     *
     * @return bool
     */
    public function hasApplied(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /**
     * Get the discount amount from session.
     *
     * @return float
     */
    public function getAppliedDiscount(): float
    {
        $applied = $this->getApplied();
        return $applied['discount_amount'] ?? 0;
    }

    /**
     * Record usage after successful payment.
     *
     * @param int $promotionId
     * @param int $userId
     * @param int|null $orderId
     * @param float $discountAmount
     * @return PromotionUsage
     */
    public function markAsUsed(int $promotionId, int $userId, ?int $orderId, float $discountAmount): PromotionUsage
    {
        $usage = PromotionUsage::create([
            'promotion_id' => $promotionId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        // Increment usage count
        Promotion::where('id', $promotionId)->increment('used_count');

        return $usage;
    }

    /**
     * Re-validate promo before payment (server-side check).
     * This prevents manual manipulation from frontend.
     *
     * @param array $cart Cart items
     * @param int|null $userId User ID
     * @return array{valid: bool, message: string, discount?: float}
     */
    public function validateBeforePayment(array $cart, ?int $userId = null): array
    {
        $applied = $this->getApplied();

        if (!$applied || !isset($applied['code'])) {
            // No promo applied, that's fine
            return [
                'valid' => true,
                'message' => 'No promo applied',
                'discount' => 0,
            ];
        }

        // Re-validate the promo
        $validation = $this->validatePromo($applied['code'], $cart, $userId);

        if (!$validation['valid']) {
            // Promo is no longer valid, remove it
            $this->remove();
            return [
                'valid' => false,
                'message' => $validation['message'],
                'discount' => 0,
            ];
        }

        // Recalculate discount to ensure accuracy
        $promotion = $validation['promotion'];
        $discountResult = $this->calculateDiscount($promotion, $cart);

        // Update session with correct discount
        $applied['discount_amount'] = $discountResult['discount_amount'];
        Session::put(self::SESSION_KEY, $applied);

        return [
            'valid' => true,
            'message' => 'Promo valid',
            'discount' => $discountResult['discount_amount'],
        ];
    }

    /**
     * Get user usage count for a promotion.
     *
     * @param int $promotionId
     * @param int $userId
     * @return int
     */
    public function getUserUsageCount(int $promotionId, int $userId): int
    {
        return PromotionUsage::where('promotion_id', $promotionId)
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Calculate cart subtotal from items.
     *
     * @param array $cart
     * @return float
     */
    protected function calculateCartSubtotal(array $cart): float
    {
        $items = $cart['items'] ?? $cart;

        $subtotal = 0;
        foreach ($items as $item) {
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['qty'] ?? 1);
            $subtotal += $price * $qty;
        }

        return $subtotal;
    }

    /**
     * Get all available promotions for a user.
     *
     * @param float|null $subtotal Current cart subtotal for filtering
     * @return \Illuminate\Support\Collection
     */
    public function getAvailablePromotions(?float $subtotal = null): \Illuminate\Support\Collection
    {
        $query = Promotion::where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('used_count < usage_limit');
            });

        if ($subtotal !== null) {
            $query->where(function ($q) use ($subtotal) {
                $q->whereNull('minimum_purchase')
                    ->orWhere('minimum_purchase', '<=', $subtotal);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
