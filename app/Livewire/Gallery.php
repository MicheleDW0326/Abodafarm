<?php

namespace App\Livewire;

use App\Models\{Category, Favorite, Product};
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.guest-home')]
class Gallery extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';
    public ?int $selectedCategory = null;
    public string $typeFilter = 'all';
    public string $sortBy = 'newest';
    public int $perPage = 12;

    public array $categories = [];
    public array $favoritedIds = [];

    public function mount(): void
    {
        $this->loadCategories();
        $this->loadFavoritedIds();
    }

    protected function loadCategories(): void
    {
        $this->categories = Category::active()
            ->ordered()
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => Str::slug($cat->name),
                'icon' => $cat->icon ?? '🐾',
            ])->toArray();
    }

    protected function loadFavoritedIds(): void
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            $this->favoritedIds = [];
            return;
        }

        $this->favoritedIds = Favorite::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('favoritable_type', Product::class)
            ->pluck('favoritable_id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    public function toggleFavorite(int $productId): void
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            $this->dispatch('show-login-required');
            return;
        }

        $isFavorited = Favorite::toggle(\Illuminate\Support\Facades\Auth::id(), Product::class, $productId);

        // Update local state
        if ($isFavorited) {
            $this->favoritedIds[] = $productId;
        } else {
            $this->favoritedIds = array_values(array_filter($this->favoritedIds, fn($id) => $id !== $productId));
        }

        $this->dispatch('favorite-toggled', [
            'productId' => $productId,
            'isFavorited' => $isFavorited,
        ]);

        $this->dispatch('alert', type: 'success', message: $isFavorited ? 'Produk ditambahkan ke favorit' : 'Produk dihapus dari favorit');
    }

    #[Computed]
    public function products()
    {
        $query = Product::query()
            ->with('category')
            ->availableWithStatus();

        if ($this->typeFilter === 'live') {
            $query->live();
        } elseif ($this->typeFilter === 'fresh') {
            $query->freshType();
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty($this->search)) {
            $term = trim(mb_strtolower($this->search));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"])
                    ->orWhereHas('category', function ($q) use ($term) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"]);
                    });
            });
        }

        $query->orderByDesc('created_at');

        return $query->paginate($this->perPage);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function setCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = null;
        $this->typeFilter = 'all';
        $this->sortBy = 'newest';
    }

    public function render()
    {
        $paginator = $this->products;
        return view('livewire.gallery', [
            'products' => $paginator,
            'totalCount' => $paginator->total(),
        ]);
    }
}
