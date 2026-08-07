<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service for handling promotion validation, calculation, and application.
 *
 * Phase 1: This is a skeleton implementation.
 * Business logic will be implemented in future phases.
 */
class PromotionService
{
    /**
     * Validate if a promotion code can be applied.
     *
     * @param string $code Promotion code to validate
     * @param float $subtotal Current cart/order subtotal
     * @param int|null $userId User ID to check per-user usage limits
     * @return array{valid: bool, promotion?: Promotion, message: string}
     */
    public function validate(string $code, float $subtotal, ?int $userId = null): array
    {
        // TODO: Implement validation logic
        // - Check if promotion exists by code
        // - Check if promotion is active
        // - Check if promotion has started and not expired
        // - Check if minimum purchase requirement is met
        // - Check if global usage limit is not exceeded
        // - Check if user has reached per-user limit
        return [
            'valid' => false,
            'message' => 'Not implemented',
        ];
    }

    /**
     * Calculate the discount amount for a promotion.
     *
     * @param Promotion $promotion The promotion to calculate discount for
     * @param float $subtotal Current cart/order subtotal
     * @param array $context Additional context (cart items, shipping cost, etc.)
     * @return array{discount_amount: float, final_total: float, details: array}
     */
    public function calculate(Promotion $promotion, float $subtotal, array $context = []): array
    {
        // TODO: Implement discount calculation
        // - For percentage: apply percentage to subtotal
        // - For fixed: return fixed amount
        // - For free_shipping: return shipping cost
        // - Respect maximum_discount cap
        // - Return final total after discount
        return [
            'discount_amount' => 0,
            'final_total' => $subtotal,
            'details' => [],
        ];
    }

    /**
     * Apply a promotion code to an order/cart.
     *
     * @param string $code Promotion code to apply
     * @param float $subtotal Current cart/order subtotal
     * @param int|null $userId User ID
     * @return array{valid: bool, promotion?: Promotion, discount?: array, message: string}
     */
    public function apply(string $code, float $subtotal, ?int $userId = null): array
    {
        // TODO: Implement apply logic
        // - First validate the code
        // - Calculate discount
        // - Store applied promotion (session/database)
        // - Return result
        return [
            'valid' => false,
            'message' => 'Not implemented',
        ];
    }

    /**
     * Mark a promotion as used by a user for an order.
     *
     * @param Promotion $promotion The promotion that was used
     * @param User $user The user who used the promotion
     * @param int|null $orderId The order ID where promotion was applied
     * @param float $discountAmount The actual discount amount given
     * @return PromotionUsage
     */
    public function markAsUsed(Promotion $promotion, User $user, ?int $orderId, float $discountAmount): PromotionUsage
    {
        // TODO: Implement usage tracking
        // - Create PromotionUsage record
        // - Increment promotion's used_count
        // - Return the created usage record
        return new PromotionUsage();
    }

    /**
     * Get all active and valid promotions.
     *
     * @param float|null $subtotal Optional subtotal to filter by minimum purchase
     * @return Collection<Promotion>
     */
    public function getAvailablePromotions(?float $subtotal = null): Collection
    {
        // TODO: Implement retrieval logic
        return Promotion::valid()
            ->when($subtotal, fn($q, $subtotal) => $q->where('minimum_purchase', '<=', $subtotal))
            ->get();
    }

    /**
     * Get the number of times a user has used a specific promotion.
     *
     * @param int $promotionId
     * @param int $userId
     * @return int
     */
    public function getUserUsageCount(int $promotionId, int $userId): int
    {
        return PromotionUsage::byPromotion($promotionId)
            ->byUser($userId)
            ->count();
    }

    /**
     * Remove/discard an applied promotion.
     *
     * @return void
     */
    public function removeApplied(): void
    {
        // TODO: Implement removal logic
    }

    /**
     * Get the currently applied promotion (if any).
     *
     * @return array|null
     */
    public function getAppliedPromotion(): ?array
    {
        // TODO: Implement retrieval of applied promotion from session/database
        return null;
    }
}
