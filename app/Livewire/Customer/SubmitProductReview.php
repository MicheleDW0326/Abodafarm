<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\Product;
use App\Services\ReviewService;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitProductReview extends Component
{
    use WithFileUploads;

    public ?Order $order = null;
    public ?Product $product = null;
    public int $rating = 5;
    public string $title = '';
    public string $content = '';
    public array $photos = [];
    public bool $showSuccess = false;

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'title' => 'nullable|string|max:255',
        'content' => 'nullable|string|max:2000',
        'photos.*' => 'image|max:2048',
    ];

    protected $messages = [
        'rating.required' => 'Rating harus diisi.',
        'photos.*.image' => 'File harus berupa gambar.',
        'photos.*.max' => 'Ukuran foto maksimal 2MB.',
    ];

    public function mount(int $orderId, int $productId)
    {
        $this->order = Order::findOrFail($orderId);
        $this->product = Product::findOrFail($productId);
    }

    public function submit()
    {
        $this->authorize('createForProduct', [Review::class, $this->order, $this->product->id]);

        $this->validate();

        $review = ReviewService::createProductReview(
            user: auth()->user(),
            order: $this->order,
            product: $this->product,
            rating: $this->rating,
            content: $this->content ?: null,
            title: $this->title ?: null,
            photos: $this->photos
        );

        $this->showSuccess = true;
        $this->reset(['rating', 'title', 'content', 'photos']);

        $this->dispatch('review-submitted', ['productId' => $this->product->id]);
    }

    public function removePhoto(int $index): void
    {
        if (isset($this->photos[$index])) {
            unset($this->photos[$index]);
            $this->photos = array_values($this->photos);
        }
    }

    public function render()
    {
        return view('livewire.customer.submit-product-review');
    }
}
