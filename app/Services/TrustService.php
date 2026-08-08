<?php

namespace App\Services;

use App\Models\CustomerLevel;
use App\Models\FarmerBadge;
use App\Models\FarmerFollower;
use App\Models\FarmProfile;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewProductNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrustService
{
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Calculate trust score for a farmer (0-100).
     */
    public function calculateTrustScore(User $user): float
    {
        $farmProfile = $user->farmProfile;
        if (!$farmProfile) {
            return 50;
        }

        $score = 50; // Base score

        // Rating factor (0-20 points)
        $ratingFactor = min(20, ($farmProfile->rating ?? 0) * 4);

        // Completed orders factor (0-15 points)
        $completedOrders = $user->orders()
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->count();
        $orderFactor = min(15, $completedOrders * 0.5);

        // Response rate factor (0-15 points)
        $responseFactor = ($farmProfile->response_rate ?? 0) * 0.15;

        // Account age factor (0-10 points) - 1 point per month, max 10
        $monthsSinceJoin = max(0, $user->created_at->diffInMonths(now()));
        $ageFactor = min(10, $monthsSinceJoin);

        // Low cancellation bonus (0-10 points)
        $cancellationPenalty = ($farmProfile->cancellation_rate ?? 0) * 10;
        $cancellationFactor = max(0, 10 - $cancellationPenalty);

        $score = $score + $ratingFactor + $orderFactor + $responseFactor + $ageFactor + $cancellationFactor;

        return round(min(100, max(0, $score)), 2);
    }

    /**
     * Update all farmer statistics.
     */
    public function updateFarmerStats(User $user): void
    {
        $farmProfile = $user->farmProfile;
        if (!$farmProfile) {
            return;
        }

        // Get orders
        $orders = $user->orders();
        $completedOrders = $orders->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]);
        $cancelledOrders = $orders->where('status', Order::STATUS_CANCELLED);

        // Calculate stats
        $totalOrders = $orders->count();
        $completedCount = $completedOrders->count();
        $cancelledCount = $cancelledOrders->count();

        // Products sold (from completed order items)
        $productsSold = DB::table('order_items')
            ->whereIn('order_id', $completedOrders->pluck('id'))
            ->whereHas('product', fn($q) => $q->where('type', 'supply'))
            ->sum('quantity');

        // Livestock sold
        $livestockSold = DB::table('order_items')
            ->whereIn('order_id', $completedOrders->pluck('id'))
            ->whereHas('product', fn($q) => $q->where('type', '!=', 'supply'))
            ->sum('quantity');

        // Total revenue
        $totalRevenue = $completedOrders->sum('total_amount');

        // Calculate cancellation rate
        $cancellationRate = $totalOrders > 0
            ? ($cancelledCount / $totalOrders) * 100
            : 0;

        // Repeat customers
        $repeatCustomers = DB::table('orders')
            ->where('user_id', $user->id)
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $farmProfile->update([
            'products_sold' => $productsSold,
            'livestock_sold' => $livestockSold,
            'total_revenue' => $totalRevenue,
            'completed_orders' => $completedCount,
            'completed_bookings' => $completedCount,
            'cancellation_rate' => round($cancellationRate, 2),
        ]);
    }

    /**
     * Update farmer rating based on reviews.
     */
    public function updateFarmerRating(User $user): void
    {
        $farmProfile = $user->farmProfile;
        if (!$farmProfile) {
            return;
        }

        $reviews = Review::where('farmer_id', $user->id)
            ->approved()
            ->where('review_type', Review::TYPE_FARMER);

        $avgRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        $farmProfile->update([
            'rating' => $avgRating ?? 0,
            'total_reviews' => $totalReviews,
        ]);
    }

    /**
     * Calculate and update response rate and time.
     */
    public function updateResponseStats(User $user): void
    {
        $farmProfile = $user->farmProfile;
        if (!$farmProfile) {
            return;
        }

        // Get orders that need response (inquiry type)
        $orders = $user->orders()
            ->where('order_type', Order::TYPE_INQUIRY)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_APPROVED, Order::STATUS_PROCESSING])
            ->get();

        $totalOrders = $orders->count();
        if ($totalOrders === 0) {
            $farmProfile->update([
                'response_rate' => 100,
                'response_time_hours' => 0,
                'last_response_at' => now(),
            ]);
            return;
        }

        // Calculate response time (time from placed_at to first status change)
        $totalResponseHours = 0;
        $respondedCount = 0;

        foreach ($orders as $order) {
            if ($order->placed_at && $order->assigned_at) {
                $totalResponseHours += $order->assigned_at->diffInHours($order->placed_at);
                $respondedCount++;
            }
        }

        $avgResponseHours = $respondedCount > 0 ? $totalResponseHours / $respondedCount : 0;
        $responseRate = $respondedCount > 0 ? ($respondedCount / $totalOrders) * 100 : 0;

        $farmProfile->update([
            'response_rate' => round($responseRate, 2),
            'response_time_hours' => round($avgResponseHours),
            'last_response_at' => now(),
        ]);
    }

    /**
     * Evaluate and update badges for a farmer.
     */
    public function evaluateBadges(User $user): void
    {
        $farmProfile = $user->farmProfile;
        if (!$farmProfile) {
            return;
        }

        $badges = [];

        // Verified badge
        if ($farmProfile->is_verified) {
            $badges[] = $this->createBadgeData(FarmerBadge::TYPE_VERIFIED);
        }

        // Top Seller badge (100+ sales)
        if ($farmProfile->completed_orders >= 100) {
            $badges[] = $this->createBadgeData(FarmerBadge::TYPE_TOP_SELLER);
        }

        // Fast Response badge (< 2 hours average)
        if ($farmProfile->response_time_hours > 0 && $farmProfile->response_time_hours < 2) {
            $badges[] = $this->createBadgeData(FarmerBadge::TYPE_FAST_RESPONSE);
        }

        // Trusted Farmer badge (trust score > 90)
        if ($farmProfile->trust_score >= 90) {
            $badges[] = $this->createBadgeData(FarmerBadge::TYPE_TRUSTED_FARMER);
        }

        // Organic Certified badge (manually set)
        if ($farmProfile->badge_organic_certified) {
            $badges[] = $this->createBadgeData(FarmerBadge::TYPE_ORGANIC_CERTIFIED);
        }

        // Excellent Rating badge (rating > 4.8)
        if ($farmProfile->rating >= 4.8 && $farmProfile->total_reviews >= 10) {
            $badges[] = $this->createBadgeData(FarmerBadge::TYPE_EXCELLENT_RATING);
        }

        // Update badges in database
        $this->syncBadges($user->id, $badges);

        // Update farm_profile badge fields
        $farmProfile->update([
            'badge_top_seller' => $farmProfile->completed_orders >= 100,
            'badge_fast_response' => $farmProfile->response_time_hours > 0 && $farmProfile->response_time_hours < 2,
            'badge_trusted_farmer' => $farmProfile->trust_score >= 90,
            'badge_excellent_rating' => $farmProfile->rating >= 4.8 && $farmProfile->total_reviews >= 10,
            'badges_updated_at' => now(),
        ]);
    }

    protected function createBadgeData(string $type): array
    {
        $info = FarmerBadge::getBadgeInfo($type);
        return [
            'badge_type' => $type,
            'name' => $info['name'],
            'description' => $info['description'],
            'icon' => $info['icon'],
            'color' => $info['color'],
            'is_active' => true,
            'earned_at' => now(),
        ];
    }

    protected function syncBadges(int $userId, array $badges): void
    {
        // Remove old badges
        FarmerBadge::where('user_id', $userId)->delete();

        // Add new badges
        foreach ($badges as $badge) {
            FarmerBadge::create(array_merge($badge, ['user_id' => $userId]));
        }
    }

    /**
     * Get all active badges for a farmer.
     */
    public function getFarmerBadges(User $user): Collection
    {
        return $user->farmerBadges()->active()->get();
    }

    /**
     * Update customer level based on points.
     */
    public function updateCustomerLevel(User $user): void
    {
        $level = CustomerLevel::getLevelForPoints($user->loyalty_points);

        if ($level) {
            $user->update(['customer_level_id' => $level->id]);
        }
    }

    /**
     * Add points to a user.
     */
    public function addPoints(User $user, int $points, string $reason = ''): void
    {
        $user->increment('loyalty_points', $points);
        $this->updateCustomerLevel($user);
    }

    /**
     * Calculate points for an order.
     */
    public function calculateOrderPoints(Order $order): int
    {
        $basePoints = (int) floor($order->total_amount / 10000); // 1 point per 10,000

        // Apply level multiplier
        $multiplier = 1;
        if ($order->user?->customerLevel) {
            $multiplier = 1 + ($order->user->customerLevel->discount_percent / 100);
        }

        return (int) round($basePoints * $multiplier);
    }

    /**
     * Update follower count for a farm profile.
     */
    public function updateFollowerCount(User $user): void
    {
        $farmProfile = $user->farmProfile;
        if (!$farmProfile) {
            return;
        }

        $count = FarmerFollower::where('farmer_id', $user->id)->count();
        $farmProfile->update(['followers_count' => $count]);
    }

    /**
     * Notify followers about new product.
     */
    public function notifyFollowersNewProduct(User $farmer, $product): void
    {
        $followers = FarmerFollower::where('farmer_id', $farmer->id)
            ->where('notify_new_product', true)
            ->with('follower')
            ->get();

        foreach ($followers as $follower) {
            try {
                $follower->follower->notify(new NewProductNotification($farmer, $product));
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error('Failed to notify follower', [
                    'farmer_id' => $farmer->id,
                    'follower_id' => $follower->follower_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get full farmer reputation data.
     */
    public function getFarmerReputation(User $user): array
    {
        $farmProfile = $user->farmProfile;

        return [
            'trust_score' => $farmProfile->trust_score ?? 50,
            'rating' => $farmProfile->rating ?? 0,
            'total_reviews' => $farmProfile->total_reviews ?? 0,
            'completed_orders' => $farmProfile->completed_orders ?? 0,
            'cancellation_rate' => $farmProfile->cancellation_rate ?? 0,
            'response_rate' => $farmProfile->response_rate ?? 0,
            'response_time_hours' => $farmProfile->response_time_hours ?? 0,
            'member_since' => $user->created_at->format('d M Y'),
            'total_revenue' => $farmProfile->total_revenue ?? 0,
            'products_sold' => $farmProfile->products_sold ?? 0,
            'livestock_sold' => $farmProfile->livestock_sold ?? 0,
            'repeat_customers' => $farmProfile->repeat_customers ?? 0,
            'followers_count' => $farmProfile->followers_count ?? 0,
            'badges' => $this->getFarmerBadges($user),
            'is_verified' => $farmProfile->is_verified ?? false,
        ];
    }

    /**
     * Recalculate all stats for a farmer.
     */
    public function recalculateAll(User $user): void
    {
        $this->updateFarmerStats($user);
        $this->updateFarmerRating($user);
        $this->updateResponseStats($user);

        $farmProfile = $user->farmProfile;
        if ($farmProfile) {
            $trustScore = $this->calculateTrustScore($user);
            $farmProfile->update(['trust_score' => $trustScore]);
        }

        $this->evaluateBadges($user);
        $this->updateFollowerCount($user);
    }

    /**
     * Get customer level benefits.
     */
    public function getLevelBenefits(User $user): array
    {
        $level = $user->customerLevel;

        return [
            'level' => $level,
            'points' => $user->loyalty_points,
            'discount_percent' => $level?->discount_percent ?? 0,
            'free_shipping_threshold' => $level?->free_shipping_threshold,
            'priority_support' => $level?->priority_support ?? 0,
            'next_level' => $level?->nextLevel,
            'progress_to_next' => $level?->progress_to_next_level,
        ];
    }

    /**
     * Clear all trust-related caches for a user.
     */
    public function clearCache(User $user): void
    {
        Cache::forget("farmer_reputation_{$user->id}");
        Cache::forget("farmer_badges_{$user->id}");
        Cache::forget("trust_score_{$user->id}");
    }
}
