<?php

namespace App\Policies;

use App\Enums\ReviewStatusEnum;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Anyone can view approved reviews.
     */
    public function viewAny(?User $user, ?string $type = null): bool
    {
        return true;
    }

    /**
     * Approved reviews are public, owner/admin can view all.
     */
    public function view(?User $user, Review $review): bool
    {
        // Approved reviews are public - use new enum
        if ($review->statusEnum === ReviewStatusEnum::APPROVED) {
            return true;
        }

        // Also check using legacy constant for backward compatibility
        if ($review->status === Review::STATUS_APPROVED) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($review->user_id === $user->id) {
            return true;
        }

        return $user->can('reviews.view');
    }

    /**
     * User can create a review if they have permission.
     */
    public function create(?User $user): bool
    {
        return $user && $user->can('reviews.create');
    }

    /**
     * Check if user can create a product review.
     */
    public function createForProduct(User $user, Order $order, int $productId): bool
    {
        if (!$user->can('reviews.create')) {
            return false;
        }

        // Must be a completed order - use new effective_status
        $currentStatus = $order->effective_status ?? $order->status;
        if (!in_array($currentStatus, [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
            return false;
        }

        if ($order->user_id !== $user->id) {
            return false;
        }

        if (!$order->hasCompletedPurchaseForProduct($productId)) {
            return false;
        }

        if ($order->hasExistingReviewForProduct($productId)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can create a farmer review.
     */
    public function createForFarmer(User $user, Order $booking, int $farmerId): bool
    {
        if (!$user->can('reviews.create')) {
            return false;
        }

        // Must be a completed booking - use new effective_status
        $currentStatus = $booking->effective_status ?? $booking->status;
        if ($currentStatus !== Order::STATUS_COMPLETED) {
            return false;
        }

        if (!$booking->isInquiry()) {
            return false;
        }

        if ($booking->user_id !== $user->id) {
            return false;
        }

        $hasFarmerProduct = $booking->items()
            ->whereHas('product', fn($q) => $q->where('owner_id', $farmerId))
            ->exists();

        if (!$hasFarmerProduct) {
            return false;
        }

        if ($booking->hasExistingReviewForFarmer($farmerId)) {
            return false;
        }

        return true;
    }

    /**
     * User can update their own review within 30 days.
     */
    public function update(User $user, Review $review): bool
    {
        // Admin/Staff can update any review
        if ($user->can('reviews.update')) {
            return true;
        }

        // Users can update their own reviews within 30 days
        if ($user->can('reviews.update-own') && $review->user_id === $user->id) {
            return $review->created_at->diffInDays(now()) <= 30;
        }

        return false;
    }

    /**
     * User can delete their own review.
     */
    public function delete(User $user, Review $review): bool
    {
        // Admin/Staff can delete any review
        if ($user->can('reviews.delete')) {
            return true;
        }

        // Users can delete their own reviews
        return $user->can('reviews.delete-own') && $review->user_id === $user->id;
    }

    /**
     * Admin/Staff can moderate reviews.
     */
    public function moderate(User $user, Review $review): bool
    {
        return $user->can('reviews.moderate');
    }

    /**
     * Admin/Staff or farmer can flag a review.
     */
    public function flag(User $user, Review $review): bool
    {
        if ($user->can('reviews.flag')) {
            return true;
        }

        // Farmer can flag reviews on their products
        if ($user->hasRole('peternak') && $review->review_type === Review::TYPE_PRODUCT && $review->product) {
            return $review->product->owner_id === $user->id;
        }

        return false;
    }

    /**
     * Check if review is pending moderation.
     */
    public function isPending(Review $review): bool
    {
        return $review->statusEnum === ReviewStatusEnum::PENDING
            || $review->status === Review::STATUS_PENDING;
    }

    /**
     * Check if review is approved.
     */
    public function isApproved(Review $review): bool
    {
        return $review->statusEnum === ReviewStatusEnum::APPROVED
            || $review->status === Review::STATUS_APPROVED;
    }

    /**
     * Check if review is rejected.
     */
    public function isRejected(Review $review): bool
    {
        return $review->statusEnum === ReviewStatusEnum::REJECTED
            || $review->status === Review::STATUS_REJECTED;
    }
}
