<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRecommendationService
{
    protected const FEED_CATEGORY_IDS = [6]; // Pakan Ternak
    protected const SUPPLEMENT_CATEGORY_IDS = [7, 8]; // Vitamin & Suplemen, Obat-Obatan
    protected const DEFAULT_LIMIT = 8;

    public function getRecommendations(?int $userId, ?Product $currentProduct, int $limit = self::DEFAULT_LIMIT): array
    {
        $recommendations = collect();

        // If user is logged in, get personalized recommendations from order history
        if ($userId) {
            $personalized = $this->getPersonalizedRecommendations($userId, $currentProduct, $limit);
            if ($personalized->isNotEmpty()) {
                $recommendations = $personalized;
            }
        }

        // Fill with related products if needed
        if ($recommendations->count() < $limit) {
            $related = $this->getRelatedProducts($currentProduct, $limit - $recommendations->count());
            $recommendations = $recommendations->merge($related);
        }

        // Add feed and supplements
        if ($recommendations->count() < $limit) {
            $feedSupplements = $this->getFeedAndSupplements($currentProduct, $limit - $recommendations->count());
            $recommendations = $recommendations->merge($feedSupplements);
        }

        // Deduplicate and exclude current product
        return $recommendations
            ->unique('id')
            ->where('id', '!=', $currentProduct?->id)
            ->take($limit)
            ->map(fn($product) => $this->formatProduct($product))
            ->toArray();
    }

    protected function getPersonalizedRecommendations(int $userId, ?Product $currentProduct, int $limit): Collection
    {
        // Get completed orders
        $completedOrderIds = Order::where('user_id', $userId)
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->pluck('id');

        if ($completedOrderIds->isEmpty()) {
            return collect();
        }

        // Get products from user's order history
        $orderedProductIds = \App\Models\OrderItem::whereIn('order_id', $completedOrderIds)
            ->pluck('product_id')
            ->unique()
            ->toArray();

        if (empty($orderedProductIds)) {
            return collect();
        }

        // Get categories from previously ordered products
        $orderedCategories = Product::whereIn('id', $orderedProductIds)
            ->pluck('category_id')
            ->unique()
            ->toArray();

        // Query: Same category products user hasn't ordered yet
        return Product::whereIn('category_id', $orderedCategories)
            ->where('id', '!=', $currentProduct?->id)
            ->whereNotIn('id', $orderedProductIds)
            ->available()
            ->inStock()
            ->with('category')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    protected function getRelatedProducts(?Product $currentProduct, int $limit): Collection
    {
        if (!$currentProduct) {
            return Product::available()
                ->inStock()
                ->with('category')
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        // Same category, excluding current product
        return Product::where('category_id', $currentProduct->category_id)
            ->where('id', '!=', $currentProduct->id)
            ->available()
            ->inStock()
            ->with('category')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    protected function getFeedAndSupplements(?Product $currentProduct, int $limit): Collection
    {
        $feedCategoryIds = array_merge(self::FEED_CATEGORY_IDS, self::SUPPLEMENT_CATEGORY_IDS);

        return Product::whereIn('category_id', $feedCategoryIds)
            ->where('id', '!=', $currentProduct?->id)
            ->available()
            ->inStock()
            ->with('category')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    protected function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->formatted_current_price,
            'original_price' => $product->formatted_price,
            'has_discount' => $product->has_discount,
            'image' => $product->image_url,
            'stock_status' => $product->stock_status,
            'category' => $product->category?->name,
            'category_type' => $this->getCategoryType($product->category_id),
        ];
    }

    protected function getCategoryType(?int $categoryId): ?string
    {
        if (!$categoryId) {
            return null;
        }

        if (in_array($categoryId, self::FEED_CATEGORY_IDS)) {
            return 'feed';
        }

        if (in_array($categoryId, self::SUPPLEMENT_CATEGORY_IDS)) {
            return 'supplement';
        }

        return 'related';
    }

    public function getSectionTitle(?int $userId): string
    {
        return $userId
            ? 'Mungkin Anda Suka'
            : 'Produk Lainnya';
    }
}
