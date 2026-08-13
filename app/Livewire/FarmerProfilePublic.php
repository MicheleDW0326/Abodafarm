<?php

namespace App\Livewire;

use App\Models\FarmProfile;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\LocalSeoService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.guest-home')]
#[Title('Profil Peternak - AbodaFarm')]
class FarmerProfilePublic extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?User $farmer = null;

    public ?FarmProfile $farmProfile = null;

    public string $tab = 'products';

    public string $productFilter = 'all';

    public string $productSort = 'newest';

    public Collection $products;

    public Collection $reviews;

    public int $totalProducts = 0;

    public int $totalReviews = 0;

    public int $totalGalleries = 0;

    public array $ratingDistribution = [
        5 => 0,
        4 => 0,
        3 => 0,
        2 => 0,
        1 => 0,
    ];

    public array $seo = [];

    public float $averageRating = 0;

    public function mount(string $username): void
    {
        $this->farmer = User::where('name', $username)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'peternak');
            })
            ->with('farmProfile')
            ->firstOrFail();

        $this->farmProfile = $this->farmer->farmProfile;

        if (!$this->farmProfile) {
            abort(404, 'Profil peternak tidak ditemukan');
        }

        // Generate SEO data
        $this->generateSeo();

        $this->loadProducts();
        $this->loadReviews();
        $this->loadGalleries();
    }

    protected function generateSeo(): void
    {
        $seoService = new LocalSeoService();
        $farmName = $this->farmProfile->farm_name ?? $this->farmer->name ?? 'Peternak';
        $location = $this->farmProfile->province ?? '';

        $this->seo = [
            'title' => $location
                ? "{$farmName} - Peternak di {$location} | AbodaFarm"
                : "{$farmName} | AbodaFarm",
            'description' => "Belanja {$this->totalProducts}+ produk peternakan berkualitas dari {$farmName}. " .
                "Rating " . number_format($this->farmProfile->rating ?? 0, 1) . " dari " . number_format($this->totalReviews) . " ulasan. " .
                "Sapi, kambing, ayam, dan daging segar langsung dari peternakan.",
            'schema' => $seoService->generateFarmerSchema($this->farmProfile),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function setProductFilter(string $filter): void
    {
        $this->productFilter = $filter;
        $this->resetPage();
        $this->loadProducts();
    }

    public function setProductSort(string $sort): void
    {
        $this->productSort = $sort;
        $this->resetPage();
        $this->loadProducts();
    }

    protected function loadProducts(): void
    {
        $query = Product::where('owner_id', $this->farmer->id)
            ->where('status', 'available');

        // Filter by product type
        switch ($this->productFilter) {
            case 'ternak':
                $query->where('type', 'live');
                break;
            case 'daging':
                $query->where('type', 'fresh');
                break;
            case 'pakan':
                $query->where('type', 'supply');
                break;
        }

        // Sort
        switch ($this->productSort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->withCount('orderItems')
                    ->orderBy('order_items_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $this->products = $query->limit(12)->get();
        $this->totalProducts = Product::where('owner_id', $this->farmer->id)
            ->where('status', 'available')
            ->count();
    }

    protected function loadReviews(): void
    {
        $reviews = Review::where('farmer_id', $this->farmer->id)
            ->where('review_type', 'farmer')
            ->where('status', 'approved')
            ->with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $this->reviews = $reviews;
        $this->totalReviews = Review::where('farmer_id', $this->farmer->id)
            ->where('review_type', 'farmer')
            ->where('status', 'approved')
            ->count();

        // Calculate rating distribution
        $this->calculateRatingDistribution();
    }

    protected function calculateRatingDistribution(): void
    {
        $allReviews = Review::where('farmer_id', $this->farmer->id)
            ->where('review_type', 'farmer')
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $total = $this->totalReviews;

        for ($i = 5; $i >= 1; $i--) {
            $count = $allReviews->get($i, 0);
            $this->ratingDistribution[$i] = $total > 0
                ? round(($count / $total) * 100, 1)
                : 0;
        }

        $this->averageRating = $this->farmProfile->rating ?? 0;
    }

    protected function loadGalleries(): void
    {
        $this->totalGalleries = $this->farmProfile->galleries()->count();
    }

    public function getProductTypeLabel(string $type): string
    {
        return match($type) {
            'live' => 'Ternak',
            'fresh' => 'Daging Segar',
            'supply' => 'Pakan & Perlengkapan',
            default => ucfirst($type),
        };
    }

    public function getBadgeClass(string $type): string
    {
        return match ($type) {
            'verified' => 'bg-blue-100 text-blue-700 border-blue-200',
            'top_seller' => 'bg-amber-100 text-amber-700 border-amber-200',
            'fast_response' => 'bg-green-100 text-green-700 border-green-200',
            'trusted_farmer' => 'bg-purple-100 text-purple-700 border-purple-200',
            'organic_certified' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'excellent_rating' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->farmProfile?->cover_image) {
            return asset('storage/' . $this->farmProfile->cover_image);
        }

        // Default cover gradient
        return null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->farmProfile?->logo) {
            return asset('storage/' . $this->farmProfile->logo);
        }

        if ($this->farmProfile?->image) {
            return asset('storage/' . $this->farmProfile->image);
        }

        // Default avatar
        return "https://ui-avatars.com/api/?name=" . urlencode($this->farmProfile?->farm_name ?? 'FP') . "&background=10B981&color=fff&size=128";
    }

    public function getJoinedDateAttribute(): string
    {
        return $this->farmer?->created_at?->format('M Y') ?? 'Baru';
    }

    public function getLocationAttribute(): string
    {
        return $this->farmProfile?->full_address ?? $this->farmProfile?->province ?? 'Indonesia';
    }

    public function getSeoTitleAttribute(): string
    {
        $farmName = $this->farmProfile?->farm_name ?? $this->farmer?->name ?? 'Peternak';
        $location = $this->farmProfile?->province ?? '';

        return $location
            ? "{$farmName} - Peternak di {$location} | AbodaFarm"
            : "{$farmName} | AbodaFarm";
    }

    public function getSeoDescriptionAttribute(): string
    {
        $farmName = $this->farmProfile?->farm_name ?? $this->farmer?->name ?? 'Peternak';
        $products = $this->totalProducts;
        $rating = $this->averageRating;
        $reviews = $this->totalReviews;

        return "Belanja {$products}+ produk peternakan berkualitas dari {$farmName}. " .
            "Rating {$rating} dari {$reviews} ulasan. " .
            "Sapi, kambing, ayam, dan daging segar segar langsung dari peternakan.";
    }

    public function render()
    {
        return view('livewire.farmer-profile-public', [
            'seoTitle' => $this->seoTitle,
            'seoDescription' => $this->seoDescription,
        ]);
    }
}
