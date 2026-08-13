<?php

namespace App\Services;

use App\Enums\ReviewStatusEnum;
use App\Models\FarmProfile;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewPhoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReviewService
{
    /**
     * Create a product review with photos.
     * Reviews are auto-approved for better UX.
     * Use approveReview() if you want moderation.
     */
    public static function createProductReview(
        $user,
        $order,
        Product $product,
        int $rating,
        ?string $content = null,
        ?string $title = null,
        array $photos = []
    ): Review {
        return DB::transaction(function () use ($user, $order, $product, $rating, $content, $title, $photos) {
            // Auto-approve product reviews for better UX
            $review = Review::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'review_type' => Review::TYPE_PRODUCT,
                'product_id' => $product->id,
                'farmer_id' => null,
                'rating' => $rating,
                'content' => $content,
                'title' => $title,
                'status' => ReviewStatusEnum::APPROVED->value,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            self::uploadPhotos($review, $photos);

            // Refresh product to ensure we have latest data before recalculating
            $product->refresh();
            self::recalculateProductRating($product);

            // Log for debugging
            \Log::info('Product rating recalculated after review', [
                'product_id' => $product->id,
                'review_id' => $review->id,
                'rating_submitted' => $rating,
                'new_product_rating' => $product->fresh()->rating,
                'new_total_reviews' => $product->fresh()->total_reviews,
            ]);

            return $review->load('photos');
        });
    }

    /**
     * Create a farmer/booking review with photos.
     * Reviews are auto-approved for better UX.
     */
    public static function createFarmerReview(
        $user,
        $order,
        int $farmerId,
        int $rating,
        ?string $content = null,
        ?string $title = null,
        array $photos = []
    ): Review {
        return DB::transaction(function () use ($user, $order, $farmerId, $rating, $content, $title, $photos) {
            // Auto-approve farmer reviews for better UX
            $review = Review::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'review_type' => Review::TYPE_FARMER,
                'product_id' => null,
                'farmer_id' => $farmerId,
                'rating' => $rating,
                'content' => $content,
                'title' => $title,
                'status' => ReviewStatusEnum::APPROVED->value,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            self::uploadPhotos($review, $photos);

            // Refresh farm profile to ensure we have latest data before recalculating
            self::recalculateFarmProfileRating($farmerId);

            // Log for debugging
            \Log::info('Farm profile rating recalculated after review', [
                'farmer_id' => $farmerId,
                'review_id' => $review->id,
                'rating_submitted' => $rating,
            ]);

            return $review->load('photos');
        });
    }

    /**
     * Create a product review that requires moderation.
     */
    public static function createProductReviewPending(
        $user,
        $order,
        Product $product,
        int $rating,
        ?string $content = null,
        ?string $title = null,
        array $photos = []
    ): Review {
        return DB::transaction(function () use ($user, $order, $product, $rating, $content, $title, $photos) {
            $review = Review::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'review_type' => Review::TYPE_PRODUCT,
                'product_id' => $product->id,
                'farmer_id' => null,
                'rating' => $rating,
                'content' => $content,
                'title' => $title,
                'status' => ReviewStatusEnum::PENDING->value,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            self::uploadPhotos($review, $photos);

            return $review->load('photos');
        });
    }

    /**
     * Update a review.
     */
    public static function updateReview(
        Review $review,
        int $rating,
        ?string $content = null,
        ?string $title = null,
        array $photos = []
    ): Review {
        return DB::transaction(function () use ($review, $rating, $content, $title, $photos) {
            $review->update([
                'rating' => $rating,
                'content' => $content,
                'title' => $title,
            ]);

            if (!empty($photos)) {
                self::uploadPhotos($review, $photos);
            }

            self::recalculateRating($review);

            return $review->load('photos');
        });
    }

    /**
     * Delete a review photo.
     */
    public static function deletePhoto(ReviewPhoto $photo): void
    {
        if ($photo->path) {
            Storage::disk('public')->delete($photo->path);
        }

        $review = $photo->review;
        $photo->delete();

        if ($review) {
            self::recalculateRating($review);
        }
    }

    /**
     * Upload review photos to storage.
     */
    private static function uploadPhotos(Review $review, array $photos): void
    {
        foreach ($photos as $index => $uploadedFile) {
            $filename = $uploadedFile->getClientOriginalName();
            $path = $uploadedFile->storeAs(
                'reviews/' . $review->id,
                time() . '_' . ($index + 1) . '.' . $uploadedFile->getClientOriginalExtension(),
                'public'
            );

            ReviewPhoto::create([
                'review_id' => $review->id,
                'path' => $path,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Recalculate product rating and distribution.
     */
    public static function recalculateProductRating(Product $product): void
    {
        // Get rating distribution counts
        $reviews = Review::where('product_id', $product->id)
            ->where('status', ReviewStatusEnum::APPROVED->value)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $total = $reviews->sum();
        $average = $total > 0
            ? Review::where('product_id', $product->id)
                ->where('status', ReviewStatusEnum::APPROVED->value)
                ->avg('rating')
            : 0;

        $updateData = [
            'rating' => round($average, 2),
            'total_reviews' => $total,
            'rating_1_count' => $reviews->get(1, 0),
            'rating_2_count' => $reviews->get(2, 0),
            'rating_3_count' => $reviews->get(3, 0),
            'rating_4_count' => $reviews->get(4, 0),
            'rating_5_count' => $reviews->get(5, 0),
        ];

        // Log the update data for debugging
        \Log::debug('Updating product rating', [
            'product_id' => $product->id,
            'total_approved_reviews' => $total,
            'average_rating' => round($average, 2),
            'update_data' => $updateData,
        ]);

        // Perform the update using direct query to ensure it works
        Product::where('id', $product->id)->update($updateData);

        // Refresh the product model to reflect changes
        $product->refresh();

        \Log::debug('Product rating updated', [
            'product_id' => $product->id,
            'new_rating' => $product->rating,
            'new_total_reviews' => $product->total_reviews,
        ]);
    }

    /**
     * Recalculate farm profile rating.
     */
    public static function recalculateFarmProfileRating(int $farmerId): void
    {
        $profile = FarmProfile::where('user_id', $farmerId)->first();

        if (!$profile) {
            \Log::warning('Farm profile not found for rating recalculation', [
                'farmer_id' => $farmerId,
            ]);
            return;
        }

        $farmerReviews = Review::where('review_type', Review::TYPE_FARMER)
            ->where('farmer_id', $farmerId)
            ->where('status', ReviewStatusEnum::APPROVED->value)
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        $total = $farmerReviews->total ?? 0;
        $average = $farmerReviews->average ?? 0;

        $updateData = [
            'rating' => round($average, 2),
            'total_reviews' => $total,
        ];

        // Log the update data for debugging
        \Log::debug('Updating farm profile rating', [
            'profile_id' => $profile->id,
            'farmer_id' => $farmerId,
            'total_reviews' => $total,
            'average_rating' => round($average, 2),
        ]);

        // Perform the update using direct query to ensure it works
        FarmProfile::where('id', $profile->id)->update($updateData);

        // Refresh the profile model to reflect changes
        $profile->refresh();

        \Log::debug('Farm profile rating updated', [
            'profile_id' => $profile->id,
            'new_rating' => $profile->rating,
            'new_total_reviews' => $profile->total_reviews,
        ]);
    }

    /**
     * Recalculate rating after review change.
     */
    public static function recalculateRating(Review $review): void
    {
        if ($review->product_id) {
            self::recalculateProductRating($review->product);
        }

        if ($review->farmer_id) {
            self::recalculateFarmProfileRating($review->farmer_id);
        }
    }

    /**
     * Delete a review and update ratings.
     */
    public static function deleteReview(Review $review): void
    {
        $productId = $review->product_id;
        $farmerId = $review->farmer_id;

        foreach ($review->photos as $photo) {
            if ($photo->path) {
                Storage::disk('public')->delete($photo->path);
            }
        }

        $review->delete();

        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                self::recalculateProductRating($product);
            }
        }

        if ($farmerId) {
            self::recalculateFarmProfileRating($farmerId);
        }
    }

    /**
     * Approve a review.
     */
    public static function approveReview(Review $review): void
    {
        $review->update([
            'status' => ReviewStatusEnum::APPROVED->value,
            'is_visible' => true,
        ]);
        self::recalculateRating($review);
    }

    /**
     * Hide a review (make it invisible without deleting).
     */
    public static function hideReview(Review $review): void
    {
        $review->update(['is_visible' => false]);
        self::recalculateRating($review);
    }

    /**
     * Show a hidden review.
     */
    public static function showReview(Review $review): void
    {
        $review->update(['is_visible' => true]);
        self::recalculateRating($review);
    }

    /**
     * Reject a review.
     */
    public static function rejectReview(Review $review, ?string $reason = null): void
    {
        $review->update([
            'status' => ReviewStatusEnum::REJECTED->value,
            'admin_notes' => $reason,
        ]);
        // Note: Rejected reviews don't count toward ratings
        // So we don't recalculate rating for rejection
    }

    /**
     * Get rating distribution percentages for a product.
     */
    public static function getRatingDistributionPercentages(Product $product): array
    {
        $total = $product->total_reviews ?? 0;

        if ($total === 0) {
            return [
                5 => 0,
                4 => 0,
                3 => 0,
                2 => 0,
                1 => 0,
            ];
        }

        return [
            5 => round((($product->rating_5_count ?? 0) / $total) * 100, 1),
            4 => round((($product->rating_4_count ?? 0) / $total) * 100, 1),
            3 => round((($product->rating_3_count ?? 0) / $total) * 100, 1),
            2 => round((($product->rating_2_count ?? 0) / $total) * 100, 1),
            1 => round((($product->rating_1_count ?? 0) / $total) * 100, 1),
        ];
    }
}
