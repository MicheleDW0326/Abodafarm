<?php

namespace App\Services\Smart;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SmartSearchService
{
    protected const CACHE_TTL = 300;

    /**
     * Advanced search with multiple filters and sorting options.
     */
    public function search(array $params): array
    {
        $query = $params['query'] ?? '';
        $sortBy = $params['sort'] ?? 'relevance';
        $categoryId = $params['category'] ?? null;
        $type = $params['type'] ?? null;
        $minPrice = $params['min_price'] ?? null;
        $maxPrice = $params['max_price'] ?? null;
        $farmerId = $params['farmer'] ?? null;
        $location = $params['location'] ?? null;
        $rating = $params['rating'] ?? null;
        $inStock = $params['in_stock'] ?? true;
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 20;

        $cacheKey = $this->buildCacheKey($params);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use (
            $query, $sortBy, $categoryId, $type, $minPrice, $maxPrice,
            $farmerId, $location, $rating, $inStock, $page, $perPage
        ) {
            $products = Product::query()
                ->availableWithStatus()
                ->with(['category', 'owner.farmProfile']);

            // Text search
            if (!empty($query)) {
                $searchTerm = '%' . $query . '%';
                $products->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(name) LIKE ?', [mb_strtolower($searchTerm)])
                        ->orWhereRaw('LOWER(description) LIKE ?', [mb_strtolower($searchTerm)])
                        ->orWhereHas('category', fn($cq) => $cq->whereRaw('LOWER(name) LIKE ?', [mb_strtolower($searchTerm)]))
                        ->orWhereHas('owner', fn($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [mb_strtolower($searchTerm)]));
                });
            }

            // Category filter
            if ($categoryId) {
                $products->where('category_id', $categoryId);
            }

            // Type filter (live, fresh, supply)
            if ($type) {
                $products->where('type', $type);
            }

            // Price range filter
            if ($minPrice !== null) {
                $products->where('price', '>=', $minPrice);
            }
            if ($maxPrice !== null) {
                $products->where('price', '<=', $maxPrice);
            }

            // Farmer filter
            if ($farmerId) {
                $products->where('owner_id', $farmerId);
            }

            // Location filter (through farmer's farm profile)
            if ($location) {
                $products->whereHas('owner.farmProfile', fn($q) => $q->whereRaw('LOWER(address) LIKE ?', ['%' . mb_strtolower($location) . '%']));
            }

            // Rating filter
            if ($rating) {
                $products->where('rating', '>=', $rating);
            }

            // Stock filter
            if ($inStock) {
                $products->inStock();
            }

            // Sorting
            $products = $this->applySorting($products, $sortBy, $query);

            // Get total count before pagination
            $total = $products->count();

            // Paginate
            $products = $products
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            return [
                'products' => $products,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ];
        });
    }

    protected function applySorting($query, string $sortBy, string $searchQuery)
    {
        return match ($sortBy) {
            'cheapest' => $query->orderBy('price', 'asc'),
            'expensive' => $query->orderBy('price', 'desc'),
            'highest_rated' => $query->orderByDesc('rating')->orderByDesc('total_reviews'),
            'most_popular' => $query
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as purchase_count')
                ->groupBy('products.id')
                ->orderByDesc('purchase_count'),
            'newest' => $query->orderByDesc('created_at'),
            'best_selling' => $query
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['completed', 'delivered'])
                ->selectRaw('COUNT(DISTINCT orders.id) as order_count')
                ->groupBy('products.id')
                ->orderByDesc('order_count'),
            'relevance' => !empty($searchQuery)
                ? $query->orderByRaw("CASE WHEN LOWER(name) LIKE ? THEN 0 ELSE 1 END", [mb_strtolower('%' . $searchQuery . '%')])
                    ->orderByDesc('total_reviews')
                : $query->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    protected function buildCacheKey(array $params): string
    {
        return 'smart_search_' . md5(json_encode($params));
    }

    /**
     * Get search suggestions with enhanced data.
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return ['products' => [], 'categories' => [], 'farmers' => [], 'tags' => []];
        }

        $searchTerm = '%' . mb_strtolower($query) . '%';

        // Products
        $products = Product::availableWithStatus()
            ->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'image', 'price', 'type', 'rating', 'total_reviews']);

        // Categories
        $categories = Category::active()
            ->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
            ->limit(5)
            ->get(['id', 'name', 'slug', 'image', 'product_count']);

        // Farmers
        $farmers = User::role('peternak')
            ->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
            ->with(['farmProfile'])
            ->limit(5)
            ->get(['id', 'name']);

        // Related tags/keywords
        $tags = $this->extractRelatedTags($query);

        return [
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'image' => $p->image_url,
                'price' => $p->formatted_current_price,
                'rating' => $p->rating,
                'total_reviews' => $p->total_reviews,
            ]),
            'categories' => $categories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $c->image_url,
                'product_count' => $c->product_count,
            ]),
            'farmers' => $farmers->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'farm_name' => $f->farmProfile?->shop_name,
                'location' => $f->farmProfile?->full_address,
                'avatar' => $f->farmProfile?->image ? asset('storage/' . $f->farmProfile->image) : null,
            ]),
            'tags' => $tags,
        ];
    }

    /**
     * Extract related tags based on query.
     */
    protected function extractRelatedTags(string $query): array
    {
        $tagMappings = [
            'ayam' => ['ayam broiler', 'ayam kampung', 'ayam petelur', 'DOC ayam'],
            'sapi' => ['sapi potong', 'sapi perah', 'sapi qurban'],
            'kambing' => ['kambing etawa', 'kambing potong', 'kambing domba'],
            'pakan' => ['pakan ayam', 'pakan sapi', 'konsentrat', 'jagung'],
            'obat' => ['obat ayam', 'vitamin', 'vaksin', 'antibiotik'],
            'daging' => ['daging ayam', 'daging sapi', 'daging kambing'],
            'telur' => ['telur ayam', 'telur bebek', 'telur puyuh'],
        ];

        $tags = [];
        $queryLower = mb_strtolower($query);

        foreach ($tagMappings as $keyword => $related) {
            if (str_contains($queryLower, $keyword)) {
                foreach ($related as $tag) {
                    if (!str_contains(mb_strtolower($tag), $queryLower)) {
                        $tags[] = $tag;
                    }
                }
            }
        }

        return array_slice($tags, 0, 5);
    }

    /**
     * Get popular search terms.
     */
    public function getPopularSearches(int $limit = 10): array
    {
        return Cache::remember('popular_searches', self::CACHE_TTL, function () use ($limit) {
            // In production, track searches in a table
            // For now, return common searches
            return [
                'Ayam Broiler',
                'Pakan Ayam',
                'Sapi Potong',
                'Vitamin Ternak',
                'Kambing Etawa',
                'Telur Ayam',
                'DOC Ayam',
                'Konsentrat Sapi',
                'Daging Sapi',
                'Obat Ayam',
            ];
        });
    }

    /**
     * Get search filters based on current search context.
     */
    public function getAvailableFilters(array $params): array
    {
        $query = $params['query'] ?? '';
        $categoryId = $params['category'] ?? null;

        $baseQuery = Product::availableWithStatus();

        if (!empty($query)) {
            $searchTerm = '%' . mb_strtolower($query) . '%';
            $baseQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
        }

        if ($categoryId) {
            $baseQuery->where('category_id', $categoryId);
        }

        // Get price range
        $priceRange = clone $baseQuery;
        $priceRange = $priceRange->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        // Get categories with counts
        $categories = (clone $baseQuery)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.id, categories.name, COUNT(*) as count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Get rating distribution
        $ratingDistribution = (clone $baseQuery)
            ->selectRaw('CASE
                WHEN rating >= 4.5 THEN 5
                WHEN rating >= 4.0 THEN 4
                WHEN rating >= 3.0 THEN 3
                WHEN rating >= 2.0 THEN 2
                ELSE 1
            END as rating_bucket, COUNT(*) as count')
            ->groupBy('rating_bucket')
            ->orderByDesc('rating_bucket')
            ->get();

        return [
            'price_range' => [
                'min' => $priceRange->min_price ?? 0,
                'max' => $priceRange->max_price ?? 0,
            ],
            'categories' => $categories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'count' => $c->count,
            ]),
            'ratings' => $ratingDistribution->map(fn($r) => [
                'rating' => (int) $r->rating_bucket,
                'count' => $r->count,
            ]),
        ];
    }

    /**
     * Format search results for display.
     */
    public function formatResults(array $results): array
    {
        return [
            'products' => $results['products']->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->formatted_current_price,
                'original_price' => $p->formatted_price,
                'has_discount' => $p->has_discount,
                'discount_percentage' => $p->discount_percentage,
                'image' => $p->image_url,
                'category' => $p->category?->name,
                'category_slug' => $p->category?->slug,
                'farmer' => $p->owner?->name,
                'farmer_id' => $p->owner_id,
                'farm_name' => $p->owner?->farmProfile?->shop_name,
                'location' => $p->owner?->farmProfile?->full_address,
                'rating' => $p->rating,
                'total_reviews' => $p->total_reviews,
                'stock_status' => $p->stock_status,
                'stock_status_label' => $p->stock_status_label,
                'type' => $p->type,
                'type_label' => $p->type_label,
            ]),
            'total' => $results['total'],
            'page' => $results['page'],
            'per_page' => $results['per_page'],
            'total_pages' => $results['total_pages'],
        ];
    }
}
