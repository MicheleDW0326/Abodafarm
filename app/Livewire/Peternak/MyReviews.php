<?php

namespace App\Livewire\Peternak;

use App\Models\FarmProfile;
use App\Models\Review;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Ulasan Tentang Saya - Akun Peternak')]
class MyReviews extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $filter = 'all';

    public function getStatsProperty(): array
    {
        $userId = auth()->id();

        // Get reviews about the farmer (TYPE_FARMER)
        $farmerReviewsQuery = Review::where('review_type', Review::TYPE_FARMER)
            ->where('farmer_id', $userId);

        // Get reviews about farmer's products (TYPE_PRODUCT)
        $productReviewsQuery = Review::where('review_type', Review::TYPE_PRODUCT)
            ->whereHas('product', fn($q) => $q->where('owner_id', $userId));

        return [
            'total_farmer_reviews' => (clone $farmerReviewsQuery)->visible()->count(),
            'total_product_reviews' => (clone $productReviewsQuery)->visible()->count(),
            'avg_farmer_rating' => (clone $farmerReviewsQuery)->visible()->avg('rating') ?? 0,
            'avg_product_rating' => (clone $productReviewsQuery)->visible()->avg('rating') ?? 0,
        ];
    }

    public function getReviewsProperty()
    {
        $userId = auth()->id();

        $query = Review::with(['user', 'product', 'photos'])
            ->where(function ($q) use ($userId) {
                // Reviews about the farmer
                $q->where('review_type', Review::TYPE_FARMER)
                    ->where('farmer_id', $userId)
                    ->when($this->filter === 'product', fn($q) => $q->whereRaw('1 = 0')) // Exclude for product filter
                    ->when($this->filter === 'farmer', fn($q) => $q); // Include only farmer reviews
            })
            ->when($this->filter === 'product', function ($q) use ($userId) {
                // Reviews about farmer's products
                return $q->where('review_type', Review::TYPE_PRODUCT)
                    ->whereHas('product', fn($q) => $q->where('owner_id', $userId));
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(10);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.peternak.my-reviews', [
            'reviews' => $this->reviews,
            'stats' => $this->stats,
        ]);
    }
}
