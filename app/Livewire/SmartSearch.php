<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\RecommendationService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SmartSearch extends Component
{
    public string $query = '';

    public bool $isOpen = false;

    private const MIN_QUERY_LENGTH = 2;

    private const MAX_RESULTS = 5;

    protected RecommendationService $recommendationService;

    public function boot(RecommendationService $recommendationService): void
    {
        $this->recommendationService = $recommendationService;
    }

    public function updatedQuery(): void
    {
        $this->isOpen = strlen($this->query) >= self::MIN_QUERY_LENGTH;
    }

    public function hideResults(): void
    {
        $this->isOpen = false;
    }

    public function selectProduct(string $slug): void
    {
        $this->query = '';
        $this->isOpen = false;
        $this->redirect(route('shop.product', $slug));
    }

    public function selectCategory(string $slug): void
    {
        $this->query = '';
        $this->isOpen = false;
        $this->redirect(route('shop') . '?category=' . $slug);
    }

    public function selectFarm(int $id): void
    {
        $this->query = '';
        $this->isOpen = false;
        $this->redirect(route('shop') . '?q=' . urlencode('peternak:' . $id));
    }

    public function searchAll(): void
    {
        if (strlen($this->query) < self::MIN_QUERY_LENGTH) {
            return;
        }

        $this->redirect(route('shop') . '?q=' . urlencode($this->query));
    }

    #[Computed]
    public function categories(): array
    {
        if (strlen($this->query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $term = mb_strtolower($this->query);

        return Category::active()
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"]);
            })
            ->limit(self::MAX_RESULTS)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'type' => 'category',
            ])
            ->toArray();
    }

    #[Computed]
    public function products(): array
    {
        if (strlen($this->query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $term = mb_strtolower($this->query);

        return Product::with('category')
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"])
                    ->orWhereHas('category', function ($q2) use ($term) {
                        $q2->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"]);
                    });
            })
            ->available()
            ->inStock()
            ->limit(self::MAX_RESULTS)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->formatted_current_price,
                'image' => $p->image_url,
                'category' => $p->category?->name,
                'type' => $p->type,
                'type_label' => $p->type_label,
            ])
            ->toArray();
    }

    #[Computed]
    public function farms(): array
    {
        if (strlen($this->query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $term = mb_strtolower($this->query);

        return User::role('peternak')
            ->with('farmProfile')
            ->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
            ->limit(self::MAX_RESULTS)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->farmProfile?->image ? asset('storage/' . $user->farmProfile->image) : null,
                'location' => $user->farmProfile?->full_address,
                'type' => 'farm',
            ])
            ->toArray();
    }

    #[Computed]
    public function quickFilters(): array
    {
        if (strlen($this->query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $term = mb_strtolower($this->query);
        $filters = [];

        // Check for feed-related keywords
        $feedKeywords = ['pakan', 'feed', 'makanan', 'pakan ternak', 'konsentrat'];
        foreach ($feedKeywords as $keyword) {
            if (str_contains($term, $keyword)) {
                $filters[] = [
                    'name' => 'Pakan Ternak',
                    'url' => route('shop') . '?type=supply&category=pakan',
                    'icon' => 'fa-solid fa-wheat-awn',
                    'type' => 'filter',
                ];
                break;
            }
        }

        // Check for medicine/vitamin keywords
        $medicineKeywords = ['obat', 'vitamin', 'suplemen', 'medicine', 'vaksin'];
        foreach ($medicineKeywords as $keyword) {
            if (str_contains($term, $keyword)) {
                $filters[] = [
                    'name' => 'Obat & Vitamin',
                    'url' => route('shop') . '?type=supply&category=obat',
                    'icon' => 'fa-solid fa-capsules',
                    'type' => 'filter',
                ];
                break;
            }
        }

        // Check for livestock keywords
        $livestockKeywords = ['sapi', 'kambing', 'ayam', 'bebek', 'ternak'];
        foreach ($livestockKeywords as $keyword) {
            if (str_contains($term, $keyword)) {
                $filters[] = [
                    'name' => 'Ternak Hidup',
                    'url' => route('shop') . '?type=live',
                    'icon' => 'fa-solid fa-cow',
                    'type' => 'filter',
                ];
                break;
            }
        }

        // Check for fresh meat keywords
        $meatKeywords = ['daging', 'meat', 'segar'];
        foreach ($meatKeywords as $keyword) {
            if (str_contains($term, $keyword)) {
                $filters[] = [
                    'name' => 'Daging Segar',
                    'url' => route('shop') . '?type=fresh',
                    'icon' => 'fa-solid fa-drumstick-bite',
                    'type' => 'filter',
                ];
                break;
            }
        }

        return $filters;
    }

    public function getHasResultsProperty(): bool
    {
        return !empty($this->categories)
            || !empty($this->products)
            || !empty($this->farms)
            || !empty($this->quickFilters);
    }

    public function highlight(string $text, ?string $keyword = null): string
    {
        $term = $keyword ?? $this->query;

        if (empty($term)) {
            return e($text);
        }

        return preg_replace(
            '/(' . preg_quote($term, '/') . ')/i',
            '<mark class="bg-amber-100 text-amber-800 px-0.5 rounded font-semibold">$1</mark>',
            e($text)
        );
    }

    public function render()
    {
        return view('livewire.smart-search');
    }
}
