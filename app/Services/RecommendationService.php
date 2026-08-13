<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    protected const CACHE_TTL = 300; // 5 minutes
    protected const DEFAULT_LIMIT = 12;
    protected const TRENDING_DAYS = 7;
    protected const NEW_PRODUCTS_DAYS = 30;

    /**
     * Get trending products (most viewed in last 7 days).
     */
    public function getTrending(int $limit = self::DEFAULT_LIMIT): Collection
    {
        // Cache disabled temporarily due to corruption issue
        return Product::query()
            ->select('products.*')
            ->selectRaw('COUNT(product_views.id) as view_count')
            ->leftJoin('product_views', 'products.id', '=', 'product_views.product_id')
            ->where('product_views.viewed_at', '>=', now()->subDays(self::TRENDING_DAYS))
            ->orWhereNull('product_views.id')
            ->availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->groupBy('products.id')
            ->orderByDesc('view_count')
            ->orderByDesc('products.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular products (most purchased).
     */
    public function getPopular(int $limit = self::DEFAULT_LIMIT): Collection
    {
        // Skip cache temporarily - corrupted cache causes __PHP_Incomplete_Class error
        // TODO: Clear cache: php artisan cache:clear
        // return Cache::remember($cacheKey, self::CACHE_TTL, fn() => $this->fetchPopular($limit));

        return $this->fetchPopular($limit);
    }

    protected function fetchPopular(int $limit): Collection
    {
        $popularIds = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->pluck('product_id')
            ->values()
            ->toArray();

        if (empty($popularIds)) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $popularIds)
            ->availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->get()
            ->sortBy(fn($p) => array_search($p->id, $popularIds))
            ->values();
    }

    /**
     * Get newest products (recently added).
     */
    public function getNewest(int $limit = self::DEFAULT_LIMIT): Collection
    {
        return Product::query()
            ->availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get personalized recommendations for a user.
     * Priority: Purchase history > Favorite categories > Favorite farmer > Popularity > New arrivals
     */
    public function getRecommendedForUser(?int $userId, int $limit = self::DEFAULT_LIMIT): Collection
    {
        if (!$userId) {
            return $this->getTrending($limit);
        }

        $user = User::with(['favorites', 'orders.items.product'])->find($userId);

        if (!$user) {
            return $this->getTrending($limit);
        }

        $recommendations = collect();
        $excludedIds = [];

        // 1. Purchase history - products from same categories as previously purchased
        $purchasedProductIds = $user->orders
            ->flatMap(fn($order) => $order->items)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->toArray();

        $purchasedCategories = Product::whereIn('id', $purchasedProductIds)
            ->pluck('category_id')
            ->filter()
            ->unique()
            ->toArray();

        if (!empty($purchasedCategories)) {
            $fromPurchaseHistory = Product::whereIn('category_id', $purchasedCategories)
                ->whereNotIn('id', $purchasedProductIds)
                ->availableWithStatus()
                ->inStock()
                ->with(['category', 'owner'])
                ->limit($limit)
                ->inRandomOrder()
                ->get();

            $recommendations = $recommendations->merge($fromPurchaseHistory);
            $excludedIds = array_merge($excludedIds, $fromPurchaseHistory->pluck('id')->toArray());
        }

        // 2. Favorite categories - products from user's favorited categories
        $favoriteCategoryIds = $user->favorites
            ->map(fn($fav) => $fav->favoritable?->category_id)
            ->filter()
            ->unique()
            ->toArray();

        if (!empty($favoriteCategoryIds) && $recommendations->count() < $limit) {
            $remaining = $limit - $recommendations->count();
            $fromFavorites = Product::whereIn('category_id', $favoriteCategoryIds)
                ->whereNotIn('id', $excludedIds)
                ->availableWithStatus()
                ->inStock()
                ->with(['category', 'owner'])
                ->limit($remaining)
                ->inRandomOrder()
                ->get();

            $recommendations = $recommendations->merge($fromFavorites);
            $excludedIds = array_merge($excludedIds, $fromFavorites->pluck('id')->toArray());
        }

        // 3. Favorite farmers - products from farmers user has purchased from
        $favoriteFarmerIds = Product::whereIn('id', $purchasedProductIds)
            ->pluck('owner_id')
            ->filter()
            ->unique()
            ->toArray();

        if (!empty($favoriteFarmerIds) && $recommendations->count() < $limit) {
            $remaining = $limit - $recommendations->count();
            $fromFarms = Product::whereIn('owner_id', $favoriteFarmerIds)
                ->whereNotIn('id', $excludedIds)
                ->availableWithStatus()
                ->inStock()
                ->with(['category', 'owner'])
                ->limit($remaining)
                ->inRandomOrder()
                ->get();

            $recommendations = $recommendations->merge($fromFarms);
            $excludedIds = array_merge($excludedIds, $fromFarms->pluck('id')->toArray());
        }

        // 4. Fill with trending/popular if needed
        if ($recommendations->count() < $limit) {
            $remaining = $limit - $recommendations->count();
            $trending = $this->getTrending($remaining);
            $fromTrending = $trending->whereNotIn('id', $excludedIds)->take($remaining);

            $recommendations = $recommendations->merge($fromTrending);
        }

        return $recommendations->unique('id')->take($limit);
    }

    /**
     * Get recently viewed products.
     */
    public function getRecentlyViewed(?int $userId, int $limit = 10): Collection
    {
        $sessionId = session()->getId();

        return ProductView::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId), fn($q) => $q->where('session_id', $sessionId)->whereNull('user_id'))
            ->with(['product.category', 'product.owner'])
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->get()
            ->map(fn($view) => $view->product)
            ->filter();
    }

    /**
     * Get continue shopping (last viewed products with details).
     */
    public function getContinueShopping(?int $userId, int $limit = 6): Collection
    {
        return $this->getRecentlyViewed($userId, $limit);
    }

    /**
     * Get top rated products.
     */
    public function getTopRated(int $limit = self::DEFAULT_LIMIT): Collection
    {
        // Cache disabled temporarily
        return Product::query()
            ->availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->orderByDesc('rating')
            ->orderByDesc('total_reviews')
            ->limit($limit)
            ->get();
    }

    /**
     * Get organic/fresh choice products.
     */
    public function getOrganicChoice(int $limit = self::DEFAULT_LIMIT): Collection
    {
        // Cache disabled temporarily
        return Product::query()
            ->available()
            ->inStock()
            ->with(['category', 'owner'])
            ->where('type', Product::TYPE_FRESH)
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }

    /**
     * Get related products (same category, same farmer, similar price).
     */
    public function getRelatedProducts(Product $product, int $limit = 8): Collection
    {
        $products = collect();

        // Same category
        $sameCategory = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->limit($limit)
            ->inRandomOrder()
            ->get();

        $products = $products->merge($sameCategory);
        $excludedIds = $products->pluck('id')->toArray();

        // Same farmer (if we still need more)
        if ($products->count() < $limit && $product->owner_id) {
            $sameFarmer = Product::where('owner_id', $product->owner_id)
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $excludedIds)
                ->availableWithStatus()
                ->inStock()
                ->with(['category', 'owner'])
                ->limit($limit - $products->count())
                ->inRandomOrder()
                ->get();

            $products = $products->merge($sameFarmer);
            $excludedIds = array_merge($excludedIds, $sameFarmer->pluck('id')->toArray());
        }

        // Similar price range (within 50% variance)
        if ($products->count() < $limit) {
            $minPrice = $product->price * 0.5;
            $maxPrice = $product->price * 1.5;

            $similarPrice = Product::whereBetween('price', [$minPrice, $maxPrice])
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $excludedIds)
                ->availableWithStatus()
                ->inStock()
                ->with(['category', 'owner'])
                ->limit($limit - $products->count())
                ->inRandomOrder()
                ->get();

            $products = $products->merge($similarPrice);
        }

        return $products->unique('id')->take($limit);
    }

    /**
     * Get frequently bought together products.
     */
    public function getFrequentlyBoughtTogether(Product $product, int $limit = 6): Collection
    {
        // Get orders containing this product
        $orderIdsWithProduct = OrderItem::where('product_id', $product->id)
            ->pluck('order_id')
            ->unique();

        if ($orderIdsWithProduct->isEmpty()) {
            return $this->getRelatedProducts($product, $limit);
        }

        // Find other products in those orders
        $frequentlyBought = OrderItem::select('product_id', DB::raw('COUNT(*) as frequency'))
            ->whereIn('order_id', $orderIdsWithProduct)
            ->where('product_id', '!=', $product->id)
            ->groupBy('product_id')
            ->orderByDesc('frequency')
            ->limit($limit)
            ->pluck('product_id');

        return Product::whereIn('id', $frequentlyBought)
            ->availableWithStatus()
            ->inStock()
            ->with(['category', 'owner'])
            ->get()
            ->sortBy(function ($product) use ($frequentlyBought) {
                return $frequentlyBought->search($product->id);
            })
            ->values();
    }

    /**
     * Get search suggestions.
     */
    public function getSearchSuggestions(string $query, int $limit = 10): array
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return ['products' => [], 'categories' => [], 'farmers' => []];
        }

        $searchTerm = '%' . $query . '%';

        // Products
        $products = Product::availableWithStatus()
            ->where('name', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(5)
            ->get(['id', 'name', 'slug', 'image', 'price', 'type']);

        // Categories
        $categories = Category::active()
            ->where('name', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(3)
            ->get(['id', 'name', 'slug', 'image']);

        // Farmers (users with products)
        $farmers = User::where('name', 'like', $searchTerm)
            ->whereHas('products', fn($q) => $q->availableWithStatus())
            ->with(['farmProfile'])
            ->limit(3)
            ->get(['id', 'name']);

        return [
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'image' => $p->image_url,
                'price' => $p->formatted_current_price,
                'type' => $p->type,
                'type_label' => $p->type_label,
            ]),
            'categories' => $categories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $c->image_url,
            ]),
            'farmers' => $farmers->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'farm_name' => $f->farmProfile?->name,
                'image' => $f->farmProfile?->image ? asset('storage/' . $f->farmProfile->image) : null,
            ]),
        ];
    }

    /**
     * Track a product view.
     */
    public function trackView(int $productId): void
    {
        ProductView::track($productId);
    }

    /**
     * Clear recommendation cache.
     */
    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Format products for display.
     */
    public function formatProducts(Collection $products): array
    {
        return $products->map(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->formatted_current_price,
            'original_price' => $product->formatted_price,
            'has_discount' => $product->has_discount,
            'discount_percentage' => $product->discount_percentage,
            'image' => $product->image_url,
            'stock_status' => $product->stock_status,
            'stock_status_label' => $product->stock_status_label,
            'stock' => $product->stock,
            'category' => $product->category?->name,
            'category_slug' => $product->category?->slug,
            'owner' => $product->owner?->name,
            'rating' => $product->rating,
            'total_reviews' => $product->total_reviews,
            'type' => $product->type,
            'type_label' => $product->type_label,
            'weight' => $product->weight,
            'sex_label' => $product->sex_label,
        ])->toArray();
    }
}
