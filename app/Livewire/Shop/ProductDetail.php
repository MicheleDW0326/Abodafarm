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

    // Farmer/Owner data for "Kunjungi Toko Peternak" link
    public ?array $farmer = null;

    public function mount(Product $product, RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;

        $this->product = $product->load([
            'category',
            'owner.farmProfile',
            'reviews' => function ($query) {
                $query->where('status', Review::STATUS_APPROVED)
                    ->orderByDesc('created_at')
                    ->limit(5);
            }
        ]);

        // Load farmer data for "Kunjungi Toko Peternak"
        $this->loadFarmerData();

        $this->buildImageGallery();

        // Track this product view (only if product exists)
        if ($this->product && $this->product->id) {
            ProductView::track($this->product->id);
        }

        // Load recommendations and frequently bought together
        $this->loadRecommendations();
    }

    protected function loadFarmerData(): void
    {
        $owner = $this->product->owner;

        if (!$owner || !$owner->hasRole('peternak')) {
            $this->farmer = null;
            return;
        }

        $farmProfile = $owner->farmProfile;

        $this->farmer = [
            'username' => $owner->username ?? $owner->name ?? 'peternak',
            'name' => $farmProfile?->farm_name ?? $owner->name ?? 'Toko Peternak',
            'logo_url' => $farmProfile?->logo
                ? asset('storage/' . $farmProfile->logo)
                : ($farmProfile?->image
                    ? asset('storage/' . $farmProfile->image)
                    : null),
            'rating' => $farmProfile?->rating ?? 0,
            'total_reviews' => $farmProfile?->total_reviews ?? 0,
            'is_verified' => $farmProfile?->is_verified ?? false,
            'profile_url' => route('farmer.public-profile', ['username' => $owner->name ?? $owner->id]),
        ];
    }

    protected function loadRecommendations(): void
    {
        try {
            $userId = auth()->id();

            // Initialize as empty arrays
            $this->recommendations = [];
            $this->frequentlyBought = [];

            // Recommendations based on user's history
            if ($userId) {
                $this->recommendationTitle = 'Mungkin Anda Suka';
                $recommended = $this->recommendationService->getRecommendedForUser($userId, 8);
                $this->recommendations = $this->recommendationService->formatProducts($recommended) ?? [];
            }

            // If not enough personalized recommendations, add related products
            if (is_array($this->recommendations) && count($this->recommendations) < 4) {
                $relatedCount = 8 - count($this->recommendations);
                $related = $this->recommendationService->getRelatedProducts($this->product, $relatedCount > 0 ? $relatedCount : 4);
                $formattedRelated = $this->recommendationService->formatProducts($related) ?? [];

                // Merge without duplicates
                $existingIds = array_column($this->recommendations, 'id');
                foreach ($formattedRelated as $item) {
                    if (is_array($item) && isset($item['id']) && !in_array($item['id'], $existingIds)) {
                        $this->recommendations[] = $item;
                        $existingIds[] = $item['id'];
                    }
                }

                if (!$userId) {
                    $this->recommendationTitle = 'Produk Serupa';
                }
            }

            // Frequently bought together
            $frequentlyBought = $this->recommendationService->getFrequentlyBoughtTogether($this->product, 6);
            $this->frequentlyBought = $this->recommendationService->formatProducts($frequentlyBought) ?? [];
        } catch (\Exception $e) {
            // Fallback to empty arrays on error
            $this->recommendations = [];
            $this->frequentlyBought = [];
        }
    }

    protected function buildImageGallery(): void
    {
        $this->allImages = [];

        // Add main image first
        if (!empty($this->product->image)) {
            $imageUrl = $this->product->image_url;
            if (!empty($imageUrl)) {
                $this->allImages[] = $imageUrl;
            }
        }

        // Add additional images from JSON array
        if (!empty($this->product->images) && is_array($this->product->images)) {
            foreach ($this->product->images as $image) {
                if (!empty($image)) {
                    $url = str_starts_with($image, 'http')
                        ? $image
                        : asset('storage/' . $image);
                    if (!in_array($url, $this->allImages)) {
                        $this->allImages[] = $url;
                    }
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

    public function getReviews(): array
    {
        try {
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
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getRatingBreakdown(): array
    {
        try {
            $distribution = $this->product->rating_distribution ?? [];
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
        } catch (\Exception $e) {
            return [
                5 => ['count' => 0, 'percent' => 0],
                4 => ['count' => 0, 'percent' => 0],
                3 => ['count' => 0, 'percent' => 0],
                2 => ['count' => 0, 'percent' => 0],
                1 => ['count' => 0, 'percent' => 0],
            ];
        }
    }

    public function render()
    {
        // Generate dynamic SEO data
        $seoService = app(SeoService::class);
        $seo = $seoService->forProduct($this->product);

        // Ensure allImages is always an array
        $this->buildImageGallery();

        return view('livewire.shop.product-detail', [
            'seo' => $seo,
            'reviews' => $this->getReviews(),
            'ratingBreakdown' => $this->getRatingBreakdown(),
            'allImages' => $this->allImages,
        ]);
    }
}
