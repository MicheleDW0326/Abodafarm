<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.guest-home')]
#[Title('Toko Pakan & Perlengkapan - AbodaFarm')]
class Shop extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedCategory = null;

    public string $sortBy = 'newest';

    public string $priceRange = 'all';

    public bool $showFilters = false;

    public array $heroBanners = [];

    public array $promotionalBanner = [];

    public array $categories = [];

    public array $categoryCounts = [];

    public array $featuredProducts = [];

    public bool $showToast = false;

    public string $toastMessage = '';

    public string $toastType = 'success';

    public function mount(): void
    {
        $this->heroBanners = [
            [
                'title' => 'Pakan & Perlengkapan Ternak Berkualitas',
                'description' => 'Temukan berbagai macam pakan, vitamin, obat-obatan, dan perlengkapan peternakan untuk kebutuhan usaha Anda.',
                'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=1600&q=80',
                'badge' => 'Hot Promo',
            ],
            [
                'title' => 'Diskon Spesial untuk Pelanggan Setia',
                'description' => 'Dapatkan potongan harga hingga 30% untuk pembelian pakan dalam jumlah besar.',
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

    protected function loadCategories(): void
    {
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

        $this->categories = Category::where('is_active', true)
            ->whereIn('slug', $supplySlugs)
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
        $counts = [];
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

        $supplyCategories = Category::whereIn('slug', $supplySlugs)->get();

        foreach ($supplyCategories as $cat) {
            $count = Product::supply()
                ->where('category_id', $cat->id)
                ->available()
                ->inStock()
                ->count();
            $counts[$cat->id] = $count;
        }

        $this->categoryCounts = $counts;
    }

    protected function loadFeaturedProducts(): void
    {
        $products = Product::supply()
            ->available()
            ->inStock()
            ->featured()
            ->with('category')
            ->limit(6)
            ->get();

        $this->featuredProducts = $products->map(fn($p) => $this->formatProduct($p))->toArray();
    }

    protected function getProductsQuery()
    {
        $query = Product::supply()
            ->available()
            ->inStock()
            ->with(['category', 'reviews']);

        // Category filter
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        // Search filter
        if ($this->search) {
            $searchTerm = Str::lower($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        // Price range filter
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
        }

        // Sorting
        $query = match ($this->sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'price_low' => $query->orderByRaw('COALESCE(discount_price, price) ASC'),
            'price_high' => $query->orderByRaw('COALESCE(discount_price, price) DESC'),
            'rating' => $query->orderByDesc('rating'),
            'best_selling' => $query->orderByDesc('total_reviews'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query;
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = null;
        $this->sortBy = 'newest';
        $this->priceRange = 'all';
        $this->resetPage();
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
        if ($this->priceRange !== 'all') {
            $count++;
        }
        return $count;
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
        ];
    }

    public function render()
    {
        $products = $this->getProductsQuery()->paginate(12);

        return view('livewire.shop', [
            'products' => $products,
        ]);
    }
}