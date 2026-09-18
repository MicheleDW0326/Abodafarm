<?php

namespace App\Livewire\Peternak;

use App\Models\FarmProfile;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
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

        // OPTIMIZED: Single query for all stats using UNION and aggregation
        $stats = DB::select("
            SELECT
                COALESCE(SUM(CASE WHEN review_type = ? AND is_visible = 1 THEN 1 ELSE 0 END), 0) as total_farmer_reviews,
                COALESCE(SUM(CASE WHEN review_type = ? AND is_visible = 1 THEN 1 ELSE 0 END), 0) as total_product_reviews,
                COALESCE(AVG(CASE WHEN review_type = ? AND is_visible = 1 THEN rating END), 0) as avg_farmer_rating,
                COALESCE(AVG(CASE WHEN review_type = ? AND is_visible = 1 THEN rating END), 0) as avg_product_rating
            FROM reviews
            WHERE (review_type = ? AND farmer_id = ?)
               OR (review_type = ? AND product_id IN (SELECT id FROM products WHERE owner_id = ?))
        ", [
            Review::TYPE_FARMER,
            Review::TYPE_PRODUCT,
            Review::TYPE_FARMER,
            Review::TYPE_PRODUCT,
            Review::TYPE_FARMER, $userId,
            Review::TYPE_PRODUCT, $userId
        ]);

        $statsData = $stats[0] ?? null;

        return [
            'total_farmer_reviews' => (int) ($statsData->total_farmer_reviews ?? 0),
            'total_product_reviews' => (int) ($statsData->total_product_reviews ?? 0),
            'avg_farmer_rating' => round((float) ($statsData->avg_farmer_rating ?? 0), 1),
            'avg_product_rating' => round((float) ($statsData->avg_product_rating ?? 0), 1),
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
