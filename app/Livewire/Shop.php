<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\SeoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;

#[Layout('layouts.guest-home')]
#[Title('Produk - AbodaFarm')]
class Shop extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $selectedCategory = null;

    #[Url]
    public string $productType = 'all'; // all, live, fresh, supply

    #[Url]
    public string $sortBy = 'newest';

    #[Url]
    public string $priceRange = 'all';

    // Advanced filters
    #[Url]
    public ?float $minPrice = null;

    #[Url]
    public ?float $maxPrice = null;

    #[Url]
    public ?string $location = null;

    #[Url]
    public ?string $weightRange = null;

    #[Url]
    public ?string $ageRange = null;

    #[Url]
    public ?string $gender = null;

    #[Url]
    public ?int $minRating = null;

    #[Url]
    public bool $organicOnly = false;

    public bool $showFilters = false;

    public array $heroBanners = [];

    public array $promotionalBanner = [];

    public array $categories = [];

    public array $categoryCounts = [];

    public array $featuredProducts = [];

    public bool $showToast = false;

    public string $toastMessage = '';

    public string $toastType = 'success';

    // Filter options
    public array $locations = [];

    public array $weightRanges = [];

    public array $ageRanges = [];

    public array $genderOptions = [];

    public array $ratingOptions = [];

    public array $typeOptions = [];

    public array $typeCounts = [];

    public function mount(Request $request): void
    {
        // Initialize filter options
        $this->initFilterOptions();

        // Read search query from URL
        if ($request->has('q') && $request->q) {
            $this->search = $request->q;
        }

        // Read category from URL
        if ($request->has('category') && $request->category) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $this->selectedCategory = $category->id;
            }
        }

        // Read product type from URL
        if ($request->has('type') && $request->type) {
            $this->productType = in_array($request->type, ['all', 'live', 'fresh', 'supply'])
                ? $request->type
                : 'all';
        }

        $this->heroBanners = [
            [
                'title' => 'Produk Peternakan Berkualitas',
                'description' => 'Temukan berbagai macam ternak, daging segar, pakan, vitamin, dan perlengkapan peternakan untuk kebutuhan usaha Anda.',
                'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=1600&q=80',
                'badge' => 'Hot Promo',
            ],
            [
                'title' => 'Diskon Spesial untuk Pelanggan Setia',
                'description' => 'Dapatkan potongan harga hingga 30% untuk pembelian produk peternakan.',
                'image' => 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80',
                'badge' => 'Limited Offer',
            ],
        ];

        $this->promotionalBanner = [
            'title' => 'Gratis Ongkir',
            'subtitle' => 'Untuk pembelian di atas Rp 500.000',
            'icon' => 'fa-solid fa-truck-fast',
        ];

        // Load categories
        $this->loadCategories();

        // Load category counts
        $this->loadCategoryCounts();

        // Load featured products
        $this->loadFeaturedProducts();
    }

    protected function initFilterOptions(): void
    {
        $this->locations = [
            'manado' => 'Manado',
            'minahasa' => 'Minahasa',
            'bitung' => 'Bitung',
            'tomohon' => 'Tomohon',
            'sitaro' => 'Sitaro',
            'minahasa-selatan' => 'Minahasa Selatan',
            'minahasa-utara' => 'Minahasa Utara',
            'minahasa-tenga' => 'Minahasa Tengah',
            'bolaang-mongondow' => 'Bolaang Mongondow',
        ];

        $this->weightRanges = [
            '0-5' => '< 5 kg',
            '5-10' => '5 - 10 kg',
            '10-20' => '10 - 20 kg',
            '20-50' => '20 - 50 kg',
            '50-100' => '50 - 100 kg',
            '100+' => '> 100 kg',
        ];

        $this->ageRanges = [
            '0-3' => '< 3 bulan',
            '3-6' => '3 - 6 bulan',
            '6-12' => '6 - 12 bulan',
            '1-2' => '1 - 2 tahun',
            '2-5' => '2 - 5 tahun',
            '5+' => '> 5 tahun',
        ];

        $this->genderOptions = [
            'male' => 'Jantan',
            'female' => 'Betina',
            'mixed' => 'Campuran',
        ];

        $this->ratingOptions = [
            4 => '4+ Bintang',
            3 => '3+ Bintang',
            2 => '2+ Bintang',
            1 => '1+ Bintang',
        ];

        $this->typeOptions = [
            'all' => 'Semua',
            'live' => 'Ternak',
            'fresh' => 'Daging Segar',
            'supply' => 'Pakan & Perlengkapan',
        ];

        // Load type counts
        $this->loadTypeCounts();
    }

    protected function loadTypeCounts(): void
    {
        // OPTIMIZED: Single query instead of 4 separate COUNT queries
        $counts = Product::available()
            ->inStock()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $this->typeCounts = [
            'all' => array_sum($counts),
            'live' => $counts['live'] ?? 0,
            'fresh' => $counts['fresh'] ?? 0,
            'supply' => $counts['supply'] ?? 0,
        ];
    }

    protected function loadCategories(): void
    {
        // All supply categories
        $supplySlugs = [
            'pakan-ternak',
            'vitamin-suplemen',
            'obat-obatan',
            'alat-minum',
            'alat-makan',
            'alat-kebersihan',
            'perlengkapan-kandang',
            'lainnya',
        ];

        // Live/Fresh categories (livestock product categories)
        $livestockSlugs = [
            'sapi',
            'kambing',
            'ayam',
            'bebek',
            'lain-ternak',
        ];

        // Fresh meat categories
        $freshSlugs = [
            'daging-sapi',
            'daging-kambing',
            'daging-ayam',
            'daging-bebek',
        ];

        $query = Category::where('is_active', true);

        // Filter by type if not 'all'
        if ($this->productType === 'supply') {
            $query->whereIn('slug', $supplySlugs);
        } elseif ($this->productType === 'live') {
            $query->whereIn('slug', $livestockSlugs);
        } elseif ($this->productType === 'fresh') {
            $query->whereIn('slug', $freshSlugs);
        }

        $this->categories = $query
            ->orderBy('sort_order')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => $cat->icon,
                'image' => $cat->image_url,
            ])
            ->toArray();
    }

    protected function loadCategoryCounts(): void
    {
        // OPTIMIZED: Single query with GROUP BY instead of N queries in loop
        $categoryIds = array_column($this->categories, 'id');

        if (empty($categoryIds)) {
            $this->categoryCounts = [];
            return;
        }

        // Build base query with type filter
        $query = Product::query()
            ->selectRaw('category_id, COUNT(*) as total')
            ->whereIn('category_id', $categoryIds)
            ->available()
            ->inStock();

        // Apply type filter
        if ($this->productType === 'supply') {
            $query->supply();
        } elseif ($this->productType === 'live') {
            $query->live();
        } elseif ($this->productType === 'fresh') {
            $query->freshType();
        }

        // Single query with GROUP BY - returns all counts at once
        $counts = $query->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->toArray();

        // Fill all category counts, default to 0 for missing
        $this->categoryCounts = [];
        foreach ($categoryIds as $catId) {
            $this->categoryCounts[$catId] = $counts[$catId] ?? 0;
        }
    }

    protected function loadFeaturedProducts(): void
    {
        $query = Product::available()
            ->inStock()
            ->featured()
            ->with('category');

        // Apply type filter
        if ($this->productType === 'supply') {
            $query->supply();
        } elseif ($this->productType === 'live') {
            $query->live();
        } elseif ($this->productType === 'fresh') {
            $query->freshType();
        }

        $products = $query->limit(6)->get();

        $this->featuredProducts = $products->map(fn($p) => $this->formatProduct($p))->toArray();
    }

    protected function getProductsQuery()
    {
        $query = Product::available()
            ->inStock()
            ->with(['category', 'reviews']);

        // Filter by product type
        if ($this->productType === 'supply') {
            $query->supply();
        } elseif ($this->productType === 'live') {
            $query->live();
        } elseif ($this->productType === 'fresh') {
            $query->freshType();
        }

        // Category filter
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        // Search filter - OPTIMIZED: Removed LOWER() for better index usage
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Price range filter (presets)
        if ($this->priceRange !== 'all') {
            $ranges = [
                '0-50k' => [0, 50000],
                '50k-100k' => [50000, 100000],
                '100k-250k' => [100000, 250000],
                '250k-500k' => [250000, 500000],
                '500k+' => [500000, 999999999],
            ];
            if (isset($ranges[$this->priceRange])) {
                [$min, $max] = $ranges[$this->priceRange];
                $this->applyPriceFilter($query, $min, $max);
            }
        }

        // Custom price range filter
        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $min = $this->minPrice ?? 0;
            $max = $this->maxPrice ?? 999999999;
            $this->applyPriceFilter($query, $min, $max);
        }

        // Location filter (via owner barn_location)
        if ($this->location) {
            $query->whereHas('owner', function ($q) {
                $q->where('barn_location', 'LIKE', '%' . $this->location . '%');
            });
        }

        // Weight range filter
        if ($this->weightRange) {
            $this->applyWeightFilter($query, $this->weightRange);
        }

        // Age range filter
        if ($this->ageRange) {
            $this->applyAgeFilter($query, $this->ageRange);
        }

        // Gender filter
        if ($this->gender) {
            $query->where('sex', $this->gender);
        }

        // Rating filter
        if ($this->minRating !== null) {
            $query->where('rating', '>=', $this->minRating);
        }

        // Organic filter (could be based on category or a flag)
        if ($this->organicOnly) {
            $query->whereHas('category', function ($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%organic%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%organik%']);
            });
        }

        // Sorting
        $query = match ($this->sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'price_low' => $query->orderByRaw('COALESCE(discount_price, price) ASC'),
            'price_high' => $query->orderByRaw('COALESCE(discount_price, price) DESC'),
            'rating' => $query->orderByDesc('rating'),
            'best_selling' => $query->orderByDesc('total_reviews'),
            'popular' => $query->orderByDesc('total_reviews'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query;
    }

    protected function applyPriceFilter($query, float $min, float $max): void
    {
        $query->where(function ($q) use ($min, $max) {
            $q->where(function ($q2) use ($min, $max) {
                $q2->whereNotNull('discount_price')
                    ->whereBetween('discount_price', [$min, $max]);
            })->orWhere(function ($q2) use ($min, $max) {
                $q2->whereNull('discount_price')
                    ->whereBetween('price', [$min, $max]);
            });
        });
    }

    protected function applyWeightFilter($query, string $range): void
    {
        $ranges = [
            '0-5' => [0, 5],
            '5-10' => [5, 10],
            '10-20' => [10, 20],
            '20-50' => [20, 50],
            '50-100' => [50, 100],
            '100+' => [100, 9999],
        ];

        if (isset($ranges[$range])) {
            [$min, $max] = $ranges[$range];
            $query->whereBetween('weight', [$min, $max]);
        }
    }

    protected function applyAgeFilter($query, string $range): void
    {
        // Age is stored in various formats, trying to match months or years
        $ranges = [
            '0-3' => [0, 3],      // 0-3 months
            '3-6' => [3, 6],      // 3-6 months
            '6-12' => [6, 12],    // 6-12 months
            '1-2' => [12, 24],    // 1-2 years
            '2-5' => [24, 60],    // 2-5 years
            '5+' => [60, 999],    // 5+ years
        ];

        if (isset($ranges[$range])) {
            [$min, $max] = $ranges[$range];
            $query->whereBetween('age', [$min, $max]);
        }
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function setProductType(string $type): void
    {
        $this->productType = $type;
        $this->selectedCategory = null;
        $this->resetPage();
        $this->loadCategories();
        $this->loadCategoryCounts();
        $this->loadFeaturedProducts();
    }

    public function setSort(string $sort): void
    {
        $this->sortBy = $sort;
        $this->resetPage();
    }

    public function setPriceRange(string $range): void
    {
        $this->priceRange = $range;
        $this->resetPage();
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
        $this->resetPage();
    }

    public function setWeightRange(?string $range): void
    {
        $this->weightRange = $range;
        $this->resetPage();
    }

    public function setAgeRange(?string $range): void
    {
        $this->ageRange = $range;
        $this->resetPage();
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
        $this->resetPage();
    }

    public function setMinRating(?int $rating): void
    {
        $this->minRating = $rating;
        $this->resetPage();
    }

    public function setOrganicOnly(bool $value): void
    {
        $this->organicOnly = $value;
        $this->resetPage();
    }

    public function setCustomPriceRange(?float $min, ?float $max): void
    {
        $this->minPrice = $min;
        $this->maxPrice = $max;
        $this->priceRange = 'all'; // Reset preset range
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = null;
        $this->productType = 'all';
        $this->sortBy = 'newest';
        $this->priceRange = 'all';
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->location = null;
        $this->weightRange = null;
        $this->ageRange = null;
        $this->gender = null;
        $this->minRating = null;
        $this->organicOnly = false;
        $this->resetPage();
        $this->loadCategories();
        $this->loadCategoryCounts();
    }

    public function getActiveFiltersCount(): int
    {
        $count = 0;
        if ($this->search) {
            $count++;
        }
        if ($this->selectedCategory) {
            $count++;
        }
        if ($this->priceRange !== 'all' || $this->minPrice !== null || $this->maxPrice !== null) {
            $count++;
        }
        if ($this->location) {
            $count++;
        }
        if ($this->weightRange) {
            $count++;
        }
        if ($this->ageRange) {
            $count++;
        }
        if ($this->gender) {
            $count++;
        }
        if ($this->minRating !== null) {
            $count++;
        }
        if ($this->organicOnly) {
            $count++;
        }
        return $count;
    }

    public function getActiveFiltersList(): array
    {
        $filters = [];

        if ($this->selectedCategory) {
            $cat = Category::find($this->selectedCategory);
            if ($cat) {
                $filters[] = ['type' => 'category', 'label' => $cat->name, 'key' => 'selectedCategory', 'value' => null];
            }
        }

        if ($this->priceRange !== 'all') {
            $labels = [
                '0-50k' => '< Rp 50rb',
                '50k-100k' => 'Rp 50rb - 100rb',
                '100k-250k' => 'Rp 100rb - 250rb',
                '250k-500k' => 'Rp 250rb - 500rb',
                '500k+' => '> Rp 500rb',
            ];
            $filters[] = ['type' => 'price', 'label' => $labels[$this->priceRange] ?? $this->priceRange, 'key' => 'priceRange', 'value' => 'all'];
        }

        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $min = $this->minPrice ? 'Rp ' . number_format($this->minPrice, 0, ',', '.') : '0';
            $max = $this->maxPrice ? 'Rp ' . number_format($this->maxPrice, 0, ',', '.') : '∞';
            $filters[] = ['type' => 'price', 'label' => $min . ' - ' . $max, 'key' => 'customPrice', 'value' => null];
        }

        if ($this->location) {
            $locationName = $this->locations[$this->location] ?? $this->location;
            $filters[] = ['type' => 'location', 'label' => $locationName, 'key' => 'location', 'value' => null];
        }

        if ($this->weightRange) {
            $weightName = $this->weightRanges[$this->weightRange] ?? $this->weightRange;
            $filters[] = ['type' => 'weight', 'label' => $weightName, 'key' => 'weightRange', 'value' => null];
        }

        if ($this->ageRange) {
            $ageName = $this->ageRanges[$this->ageRange] ?? $this->ageRange;
            $filters[] = ['type' => 'age', 'label' => $ageName, 'key' => 'ageRange', 'value' => null];
        }

        if ($this->gender) {
            $genderName = $this->genderOptions[$this->gender] ?? $this->gender;
            $filters[] = ['type' => 'gender', 'label' => $genderName, 'key' => 'gender', 'value' => null];
        }

        if ($this->minRating !== null) {
            $filters[] = ['type' => 'rating', 'label' => $this->minRating . '+ Bintang', 'key' => 'minRating', 'value' => null];
        }

        if ($this->organicOnly) {
            $filters[] = ['type' => 'organic', 'label' => 'Organic', 'key' => 'organicOnly', 'value' => false];
        }

        return $filters;
    }

    public function removeFilter(string $key): void
    {
        match ($key) {
            'selectedCategory' => $this->selectedCategory = null,
            'priceRange' => $this->priceRange = 'all',
            'customPrice' => function () {
                $this->minPrice = null;
                $this->maxPrice = null;
            },
            'location' => $this->location = null,
            'weightRange' => $this->weightRange = null,
            'ageRange' => $this->ageRange = null,
            'gender' => $this->gender = null,
            'minRating' => $this->minRating = null,
            'organicOnly' => $this->organicOnly = false,
            default => null,
        };
        $this->resetPage();
    }

    /**
     * Check stock availability for a product
     */
    public function checkStock(int $productId): array
    {
        $product = Product::find($productId);

        if (!$product) {
            return [
                'available' => false,
                'stock' => 0,
                'message' => 'Produk tidak ditemukan',
            ];
        }

        return [
            'available' => $product->stock > 0 && $product->status === 'available',
            'stock' => $product->stock,
            'status' => $product->stock_status,
            'message' => $product->stock > 0
                ? "Stok tersedia: {$product->stock} unit"
                : 'Stok habis',
        ];
    }

    /**
     * Validate cart item quantity against current stock
     */
    public function validateCartQuantity(int $productId, int $requestedQty): array
    {
        $product = Product::find($productId);

        if (!$product) {
            return [
                'valid' => false,
                'maxQty' => 0,
                'currentStock' => 0,
                'message' => 'Produk tidak ditemukan',
            ];
        }

        $maxQty = $product->stock;

        if ($requestedQty > $maxQty) {
            return [
                'valid' => false,
                'maxQty' => $maxQty,
                'currentStock' => $product->stock,
                'message' => $maxQty > 0
                    ? "Stok hanya {$maxQty} unit"
                    : 'Stok habis',
            ];
        }

        return [
            'valid' => true,
            'maxQty' => $maxQty,
            'currentStock' => $product->stock,
            'message' => 'OK',
        ];
    }

    protected function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description ?? '',
            'price' => (int) $product->price,
            'current_price' => (int) $product->current_price,
            'formatted_price' => $product->formatted_price,
            'formatted_current_price' => $product->formatted_current_price,
            'has_discount' => $product->has_discount,
            'discount_percent' => $product->discount_percentage,
            'formatted_discount_price' => $product->formatted_discount_price,
            'stock' => $product->stock,
            'stock_status' => $product->stock_status,
            'stock_status_label' => $product->stock_status_label,
            'category' => $product->category?->name ?? '',
            'category_slug' => $product->category?->slug ?? '',
            'image' => $product->image_url,
            'images' => $product->images ?? [],
            'rating' => $product->rating ?? 0,
            'formatted_rating' => $product->formatted_rating,
            'total_reviews' => $product->total_reviews ?? 0,
            'unit' => $product->unit,
            'is_featured' => $product->is_featured,
            'type' => $product->type,
            'type_label' => $product->type_label,
        ];
    }

    public function render()
    {
        $products = $this->getProductsQuery()->paginate(12);

        // Generate dynamic SEO data
        $seoService = app(SeoService::class);
        $category = $this->selectedCategory ? Category::find($this->selectedCategory) : null;
        $seo = $seoService->forShop($this->search ?: null, $category);

        return view('livewire.shop', [
            'products' => $products,
            'seo' => $seo,
        ]);
    }
}