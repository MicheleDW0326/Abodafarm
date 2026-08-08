<?php

namespace App\Services\Smart;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SmartProductRecommendationService
{
    protected const CACHE_TTL = 600;
    protected const DEFAULT_LIMIT = 12;

    /**
     * Get personalized "Recommended For You" products.
     * Combines multiple signals: purchase history, views, ratings, popularity.
     */
    public function getRecommendedForYou(?int $userId, int $limit = self::DEFAULT_LIMIT): array
    {
        if (!$userId) {
            return $this->getFallbackRecommendations($limit);
        }

        $cacheKey = "smart_recommendations_{$userId}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $limit) {
            $user = User::with(['favorites', 'orders.items.product'])->find($userId);

            if (!$user) {
                return $this->getFallbackRecommendations($limit);
            }

            $scores = [];
            $excludedIds = $this->getExcludedProductIds($user);

            // Score products based on multiple signals
            $products = Product::availableWithStatus()
                ->inStock()
                ->with(['category', 'owner', 'reviews'])
                ->get();

            foreach ($products as $product) {
                if (in_array($product->id, $excludedIds)) {
                    continue;
                }

                $score = $this->calculateProductScore($product, $user);
                if ($score > 0) {
                    $scores[$product->id] = [
                        'product' => $product,
                        'score' => $score,
                        'reasons' => $this->getRecommendationReasons($product, $user),
                    ];
                }
            }

            // Sort by score and return top products
            uasort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

            return array_slice($scores, 0, $limit, true);
        });
    }

    /**
     * Calculate recommendation score for a product based on user signals.
     */
    protected function calculateProductScore(Product $product, User $user): float
    {
        $score = 0.0;

        // 1. Category affinity (30% weight)
        $score += $this->getCategoryAffinityScore($product, $user) * 0.30;

        // 2. Farmer affinity (20% weight)
        $score += $this->getFarmerAffinityScore($product, $user) * 0.20;

        // 3. Price range fit (15% weight)
        $score += $this->getPriceRangeScore($product, $user) * 0.15;

        // 4. Rating quality (15% weight)
        $score += $this->getRatingScore($product) * 0.15;

        // 5. Popularity signal (10% weight)
        $score += $this->getPopularityScore($product) * 0.10;

        // 6. Recency (10% weight)
        $score += $this->getRecencyScore($product) * 0.10;

        return $score;
    }

    protected function getCategoryAffinityScore(Product $product, User $user): float
    {
        // Check if user has purchased from this category
        $purchasedCategories = $this->getUserPurchasedCategories($user);
        if (in_array($product->category_id, $purchasedCategories)) {
            return 1.0;
        }

        // Check if user has favorited this category
        $favoriteCategories = $this->getUserFavoriteCategories($user);
        if (in_array($product->category_id, $favoriteCategories)) {
            return 0.7;
        }

        // Check if user has viewed products from this category
        $viewedCategories = $this->getUserViewedCategories($user->id);
        if (in_array($product->category_id, $viewedCategories)) {
            return 0.4;
        }

        return 0.1;
    }

    protected function getFarmerAffinityScore(Product $product, User $user): float
    {
        if (!$product->owner_id) {
            return 0.1;
        }

        $preferredFarmers = $this->getUserPreferredFarmers($user);
        if (in_array($product->owner_id, $preferredFarmers)) {
            return 1.0;
        }

        return 0.1;
    }

    protected function getPriceRangeScore(Product $product, User $user): float
    {
        $avgOrderValue = $this->getUserAverageOrderValue($user);
        if (!$avgOrderValue) {
            return 0.5;
        }

        // Product within 50% of average order value gets higher score
        $price = $product->current_price;
        $minPrice = $avgOrderValue * 0.3;
        $maxPrice = $avgOrderValue * 2.0;

        if ($price >= $minPrice && $price <= $maxPrice) {
            return 1.0;
        }

        if ($price >= $minPrice * 0.5 && $price <= $maxPrice * 1.5) {
            return 0.6;
        }

        return 0.2;
    }

    protected function getRatingScore(Product $product): float
    {
        $rating = $product->rating ?? 0;
        $totalReviews = $product->total_reviews ?? 0;

        if ($totalReviews < 3) {
            return 0.3;
        }

        // Score based on rating (0-5 scale normalized to 0-1)
        return ($rating / 5) * min(1, $totalReviews / 10);
    }

    protected function getPopularityScore(Product $product): float
    {
        $purchaseCount = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn($q) => $q->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
            ->sum('quantity');

        $viewCount = ProductView::where('product_id', $product->id)
            ->where('viewed_at', '>=', now()->subDays(30))
            ->count();

        // Normalize scores
        $purchaseScore = min(1, $purchaseCount / 50);
        $viewScore = min(1, $viewCount / 500);

        return ($purchaseScore * 0.6 + $viewScore * 0.4);
    }

    protected function getRecencyScore(Product $product): float
    {
        $daysSinceCreated = $product->created_at->diffInDays(now());

        if ($daysSinceCreated <= 7) {
            return 1.0;
        }

        if ($daysSinceCreated <= 30) {
            return 0.8;
        }

        if ($daysSinceCreated <= 90) {
            return 0.5;
        }

        return 0.2;
    }

    protected function getRecommendationReasons(Product $product, User $user): array
    {
        $reasons = [];

        $purchasedCategories = $this->getUserPurchasedCategories($user);
        if (in_array($product->category_id, $purchasedCategories)) {
            $category = Category::find($product->category_id);
            $reasons[] = "Similar to your previous purchases";
        }

        $preferredFarmers = $this->getUserPreferredFarmers($user);
        if (in_array($product->owner_id, $preferredFarmers)) {
            $reasons[] = "From your favorite farmer";
        }

        if (($product->rating ?? 0) >= 4.5 && ($product->total_reviews ?? 0) >= 5) {
            $reasons[] = "Highly rated product";
        }

        if ($product->created_at->diffInDays(now()) <= 7) {
            $reasons[] = "New product";
        }

        if (count($reasons) === 0) {
            $reasons[] = "Popular choice";
        }

        return $reasons;
    }

    protected function getUserPurchasedCategories(User $user): array
    {
        return OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id))
            ->whereNotNull('product_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->distinct()
            ->pluck('products.category_id')
            ->filter()
            ->toArray();
    }

    protected function getUserFavoriteCategories(User $user): array
    {
        return $user->favorites
            ->map(fn($fav) => $fav->favoritable?->category_id ?? null)
            ->filter()
            ->unique()
            ->toArray();
    }

    protected function getUserViewedCategories(int $userId): array
    {
        return ProductView::where('user_id', $userId)
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->distinct()
            ->pluck('products.category_id')
            ->filter()
            ->toArray();
    }

    protected function getUserPreferredFarmers(User $user): array
    {
        return OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereNotNull('products.owner_id')
            ->selectRaw('COUNT(*) as purchase_count, products.owner_id')
            ->groupBy('products.owner_id')
            ->orderByDesc('purchase_count')
            ->limit(5)
            ->pluck('owner_id')
            ->toArray();
    }

    protected function getUserAverageOrderValue(User $user): float
    {
        return Order::where('user_id', $user->id)
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->avg('total_amount') ?? 0;
    }

    protected function getExcludedProductIds(User $user): array
    {
        // Exclude products user has already purchased
        return OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id))
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->toArray();
    }

    protected function getFallbackRecommendations(int $limit): array
    {
        $products = Product::availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->orderByDesc('rating')
            ->orderByDesc('total_reviews')
            ->limit($limit)
            ->get();

        $scores = [];
        foreach ($products as $product) {
            $scores[$product->id] = [
                'product' => $product,
                'score' => $this->getRatingScore($product),
                'reasons' => ['Popular choice'],
            ];
        }

        return $scores;
    }

    /**
     * Get "You might also like" products based on current product.
     */
    public function getYouMightAlsoLike(Product $product, int $limit = 6): array
    {
        $products = Product::availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                    ->orWhere('owner_id', $product->owner_id);
            })
            ->get();

        // Score by similarity
        $scored = [];
        foreach ($products as $p) {
            $score = 0;
            if ($p->category_id === $product->category_id) {
                $score += 0.5;
            }
            if ($p->owner_id === $product->owner_id) {
                $score += 0.3;
            }
            // Price similarity
            $priceDiff = abs($p->current_price - $product->current_price);
            $avgPrice = ($p->current_price + $product->current_price) / 2;
            if ($avgPrice > 0 && $priceDiff / $avgPrice < 0.3) {
                $score += 0.2;
            }
            $scored[$p->id] = [
                'product' => $p,
                'score' => $score,
                'reasons' => ['Related product'],
            ];
        }

        uasort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit, true);
    }

    /**
     * Clear recommendation cache for a user.
     */
    public function clearUserCache(int $userId): void
    {
        Cache::flush();
    }

    /**
     * Format recommendations for display.
     */
    public function formatForDisplay(array $recommendations): array
    {
        return array_map(function ($item) {
            $product = $item['product'];
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->formatted_current_price,
                'original_price' => $product->formatted_price,
                'has_discount' => $product->has_discount,
                'image' => $product->image_url,
                'category' => $product->category?->name,
                'farmer' => $product->owner?->name,
                'rating' => $product->rating,
                'total_reviews' => $product->total_reviews,
                'stock_status' => $product->stock_status,
                'score' => $item['score'],
                'reasons' => $item['reasons'],
            ];
        }, $recommendations);
    }
}
