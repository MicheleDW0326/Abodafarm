<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Models\ProductView;
use App\Models\Review;
use App\Services\RecommendationService;
use App\Services\SeoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest-home')]
#[Title('Produk - AbodaFarm')]
class ProductDetail extends Component
{
    public Product $product;

    public int $quantity = 1;

    public string $activeTab = 'description';

    public array $allImages = [];

    public ?int $selectedImageIndex = 0;

    public array $recommendations = [];

    public array $frequentlyBought = [];

    public string $recommendationTitle = 'Produk Lainnya';

    protected RecommendationService $recommendationService;

    public function mount(Product $product, RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;

        $this->product = $product->load(['category', 'reviews' => function ($query) {
            $query->where('status', Review::STATUS_APPROVED)
                ->orderByDesc('created_at')
                ->limit(5);
        }]);

        $this->buildImageGallery();

        // Track this product view
        ProductView::track($product->id);

        // Load recommendations and frequently bought together
        $this->loadRecommendations();
    }

    protected function loadRecommendations(): void
    {
        $userId = auth()->id();

        // Recommendations based on user's history
        if ($userId) {
            $this->recommendationTitle = 'Mungkin Anda Suka';
            $recommended = $this->recommendationService->getRecommendedForUser($userId, 8);
            $this->recommendations = $this->recommendationService->formatProducts($recommended);
        }

        // If not enough personalized recommendations, add related products
        if (count($this->recommendations) < 4) {
            $related = $this->recommendationService->getRelatedProducts($this->product, 8 - count($this->recommendations));
            $formattedRelated = $this->recommendationService->formatProducts($related);

            // Merge without duplicates
            $existingIds = array_column($this->recommendations, 'id');
            foreach ($formattedRelated as $item) {
                if (!in_array($item['id'], $existingIds)) {
                    $this->recommendations[] = $item;
                }
            }

            if (!$userId) {
                $this->recommendationTitle = 'Produk Serupa';
            }
        }

        // Frequently bought together
        $frequentlyBought = $this->recommendationService->getFrequentlyBoughtTogether($this->product, 6);
        $this->frequentlyBought = $this->recommendationService->formatProducts($frequentlyBought);
    }

    protected function buildImageGallery(): void
    {
        $this->allImages = [];

        // Add main image first
        if ($this->product->image) {
            $this->allImages[] = $this->product->image_url;
        }

        // Add additional images from JSON array
        if (!empty($this->product->images) && is_array($this->product->images)) {
            foreach ($this->product->images as $image) {
                $url = str_starts_with($image, 'http')
                    ? $image
                    : asset('storage/' . $image);
                if (!in_array($url, $this->allImages)) {
                    $this->allImages[] = $url;
                }
            }
        }

        // If no images, use placeholder
        if (empty($this->allImages)) {
            $this->allImages[] = 'https://via.placeholder.com/600x600/f5f5f5/999999?text=No+Image';
        }
    }

    public function selectImage(int $index): void
    {
        $this->selectedImageIndex = $index;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function getReviewsAttribute(): array
    {
        return $this->product->approvedReviews()
            ->with(['user.farmProfile'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($review) => [
                'id' => $review->id,
                'user_name' => $review->user?->name ?? 'Anonim',
                'user_avatar' => $review->user?->farmProfile?->image ?? null,
                'rating' => $review->rating,
                'content' => $review->content,
                'title' => $review->title,
                'created_at' => $review->created_at->diffForHumans(),
                'created_date' => $review->created_at->format('d M Y'),
            ])
            ->toArray();
    }

    public function getRatingBreakdownAttribute(): array
    {
        $distribution = $this->product->rating_distribution;
        $total = $this->product->total_reviews ?? 0;

        return [
            5 => [
                'count' => $distribution[5] ?? 0,
                'percent' => $total > 0 ? round(($distribution[5] ?? 0) / $total * 100) : 0,
            ],
            4 => [
                'count' => $distribution[4] ?? 0,
                'percent' => $total > 0 ? round(($distribution[4] ?? 0) / $total * 100) : 0,
            ],
            3 => [
                'count' => $distribution[3] ?? 0,
                'percent' => $total > 0 ? round(($distribution[3] ?? 0) / $total * 100) : 0,
            ],
            2 => [
                'count' => $distribution[2] ?? 0,
                'percent' => $total > 0 ? round(($distribution[2] ?? 0) / $total * 100) : 0,
            ],
            1 => [
                'count' => $distribution[1] ?? 0,
                'percent' => $total > 0 ? round(($distribution[1] ?? 0) / $total * 100) : 0,
            ],
        ];
    }

    public function render()
    {
        // Generate dynamic SEO data
        $seoService = app(SeoService::class);
        $seo = $seoService->forProduct($this->product);

        return view('livewire.shop.product-detail', [
            'seo' => $seo,
        ]);
    }
}
