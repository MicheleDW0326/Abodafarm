<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\User;
use App\Services\ReviewService;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitFarmerReview extends Component
{
    use WithFileUploads;

    public ?Order $booking = null;
    public int $farmerId;
    public string $farmerName = '';
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

    public function mount(int $bookingId, int $farmerId, string $farmerName)
    {
        $this->booking = Order::findOrFail($bookingId);
        $this->farmerId = $farmerId;
        $this->farmerName = $farmerName;
    }

    public function submit()
    {
        $this->authorize('createForFarmer', [Review::class, $this->booking, $this->farmerId]);

        $this->validate();

        $review = ReviewService::createFarmerReview(
            user: auth()->user(),
            order: $this->booking,
            farmerId: $this->farmerId,
            rating: $this->rating,
            content: $this->content ?: null,
            title: $this->title ?: null,
            photos: $this->photos
        );

        $this->showSuccess = true;
        $this->reset(['rating', 'title', 'content', 'photos']);

        $this->dispatch('review-submitted', ['farmerId' => $this->farmerId]);
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
        return view('livewire.customer.submit-farmer-review');
    }
}
