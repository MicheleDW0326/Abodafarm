<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Str;
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

    public function mount(Product $product)
    {
        $this->product = $product->load(['category', 'reviews' => function ($query) {
            $query->where('status', Review::STATUS_APPROVED)
                ->orderByDesc('created_at')
                ->limit(5);
        }]);

        $this->buildImageGallery();
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

    public function getRelatedProducts(): array
    {
        if (!$this->product->category) {
            return [];
        }

        return Product::supply()
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->available()
            ->inStock()
            ->with('category')
            ->limit(4)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'current_price' => $p->current_price,
                'formatted_current_price' => $p->formatted_current_price,
                'has_discount' => $p->has_discount,
                'discount_percent' => $p->discount_percentage,
                'image' => $p->image_url,
                'stock_status' => $p->stock_status,
                'category' => $p->category?->name ?? '',
            ])
            ->toArray();
    }

    public function getReviewsAttribute(): array
    {
        return $this->product->approvedReviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($review) => [
                'id' => $review->id,
                'user_name' => $review->user?->name ?? 'Anonim',
                'user_avatar' => $review->user?->avatar ?? null,
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
        return view('livewire.shop.product-detail');
    }
}
