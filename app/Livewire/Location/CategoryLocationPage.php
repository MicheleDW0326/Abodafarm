<?php

namespace App\Livewire\Location;

use App\Models\Category;
use App\Models\Product;
use App\Services\LocalSeoService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CategoryLocationPage extends Component
{
    public string $category;
    public string $location;

    protected LocalSeoService $seoService;

    public function mount(string $category, string $location): void
    {
        $this->category = $category;
        $this->location = $this->formatLocation($location);
        $this->seoService = app(LocalSeoService::class);
    }

    public function render()
    {
        return view('livewire.location.category-location-page')
            ->layout('layouts.guest-home');
    }

    #[Computed]
    public function seoData(): array
    {
        return $this->seoService->getCategoryLocationSeo($this->category, $this->location);
    }

    #[Computed]
    public function categoryData(): ?Category
    {
        return Category::where('slug', $this->category)->first();
    }

    #[Computed]
    public function products(): Collection
    {
        $categorySlug = $this->category;

        // Map category slug to actual category names
        $categorySlugs = match ($categorySlug) {
            'ternak' => ['sapi', 'kambing', 'ayam', 'bebek'],
            'daging' => ['daging-sapi', 'daging-kambing', 'daging-ayam'],
            'pakan' => ['pakan', 'obat', 'perlengkapan'],
            default => [$categorySlug],
        };

        return Product::query()
            ->with(['category', 'owner.farmProfile'])
            ->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            })
            ->whereHas('owner.farmProfile', function ($q) {
                $q->where('province', 'like', '%' . $this->location . '%')
                  ->orWhere('regency', 'like', '%' . $this->location . '%')
                  ->orWhere('district', 'like', '%' . $this->location . '%');
            })
            ->availableWithStatus()
            ->inStock()
            ->orderByDesc('created_at')
            ->paginate(12);
    }

    #[Computed]
    public function featuredProducts(): Collection
    {
        $categorySlug = $this->category;

        $categorySlugs = match ($categorySlug) {
            'ternak' => ['sapi', 'kambing', 'ayam', 'bebek'],
            'daging' => ['daging-sapi', 'daging-kambing', 'daging-ayam'],
            'pakan' => ['pakan', 'obat', 'perlengkapan'],
            default => [$categorySlug],
        };

        return Product::query()
            ->with(['category', 'owner.farmProfile'])
            ->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            })
            ->whereHas('owner.farmProfile', function ($q) {
                $q->where('province', 'like', '%' . $this->location . '%')
                  ->orWhere('regency', 'like', '%' . $this->location . '%')
                  ->orWhere('district', 'like', '%' . $this->location . '%');
            })
            ->availableWithStatus()
            ->inStock()
            ->orderByDesc('is_featured')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $categorySlug = $this->category;

        $categorySlugs = match ($categorySlug) {
            'ternak' => ['sapi', 'kambing', 'ayam', 'bebek'],
            'daging' => ['daging-sapi', 'daging-kampung'],
            'pakan' => ['pakan', 'obat', 'perlengkapan'],
            default => [$categorySlug],
        };

        $count = Product::query()
            ->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            })
            ->whereHas('owner.farmProfile', function ($q) {
                $q->where('province', 'like', '%' . $this->location . '%')
                  ->orWhere('regency', 'like', '%' . $this->location . '%')
                  ->orWhere('district', 'like', '%' . $this->location . '%');
            })
            ->count();

        return [
            'products' => $count,
        ];
    }

    protected function formatLocation(string $location): string
    {
        return str_replace('-', ' ', ucwords(str_replace('-', ' ', $location)));
    }
}
