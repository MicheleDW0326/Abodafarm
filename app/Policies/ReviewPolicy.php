<?php

namespace App\Policies;

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
        // Approved reviews are public
        if ($review->status === Review::STATUS_APPROVED) {
            return true;
        }

        // Review owner can view their own
        if ($user && $review->user_id === $user->id) {
            return true;
        }

        // Admin/staff can view all
        if ($user && $user->hasRole(['admin', 'staf'])) {
            return true;
        }

        return false;
    }

    /**
     * User can create a review if they are a customer.
     */
    public function create(?User $user): bool
    {
        return $user && $user->hasRole('pelanggan');
    }

    /**
     * Check if user can create a product review.
     */
    public function createForProduct(User $user, Order $order, int $productId): bool
    {
        // Must be a completed order
        if (!in_array($order->status, [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
            return false;
        }

        // Must own the order
        if ($order->user_id !== $user->id) {
            return false;
        }

        // Must have purchased this product
        if (!$order->hasCompletedPurchaseForProduct($productId)) {
            return false;
        }

        // Must not have already reviewed
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
        // Must be a completed booking
        if ($booking->status !== Order::STATUS_COMPLETED) {
            return false;
        }

        // Must be an inquiry/booking
        if (!$booking->isInquiry()) {
            return false;
        }

        // Must own the booking
        if ($booking->user_id !== $user->id) {
            return false;
        }

        // Must have booked from this farmer
        $hasFarmerProduct = $booking->items()
            ->whereHas('product', fn($q) => $q->where('owner_id', $farmerId))
            ->exists();

        if (!$hasFarmerProduct) {
            return false;
        }

        // Must not have already reviewed this farmer
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
        // Must own the review
        if ($review->user_id !== $user->id) {
            return false;
        }

        // Can only edit within 30 days
        if ($review->created_at->diffInDays(now()) > 30) {
            return false;
        }

        return true;
    }

    /**
     * User can delete their own review.
     */
    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }

    /**
     * Admin can moderate reviews.
     */
    public function moderate(User $user, Review $review): bool
    {
        return $user->hasRole(['admin', 'staf']);
    }

    /**
     * Admin or farmer can hide/inappropriate flag a review.
     */
    public function flag(User $user, Review $review): bool
    {
        // Admin can flag any review
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        // Farmer can flag reviews on their products
        if ($review->review_type === Review::TYPE_PRODUCT && $review->product) {
            return $review->product->owner_id === $user->id;
        }

        return false;
    }
}
