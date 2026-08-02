<?php

namespace App\Livewire\Customer;

use App\Enums\ReviewStatusEnum;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Ulasan Saya - Akun Saya')]
class MyReviews extends Component
{
    public string $filter = 'all';

    public Collection $reviews;

    public ?int $editingReviewId = null;
    public int $editRating = 5;
    public string $editTitle = '';
    public string $editContent = '';

    public function mount(): void
    {
        $this->loadReviews();
    }

    public function loadReviews(): void
    {
        $this->reviews = Review::where('user_id', auth()->id())
            ->when($this->filter !== 'all', fn($q) => $q->where('review_type', $this->filter))
            ->with(['product', 'farmer', 'photos'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatedFilter(): void
    {
        $this->loadReviews();
    }

    public function startEdit(Review $review): void
    {
        // Check using enum for better type safety
        if ($review->statusEnum === ReviewStatusEnum::PENDING && $review->created_at->diffInDays(now()) > 30) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Ulasan tidak dapat diedit lagi (lebih dari 30 hari).',
            ]);
            return;
        }

        $this->editingReviewId = $review->id;
        $this->editRating = $review->rating;
        $this->editTitle = $review->title ?? '';
        $this->editContent = $review->content ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editingReviewId = null;
        $this->editRating = 5;
        $this->editTitle = '';
        $this->editContent = '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editRating' => 'required|integer|min:1|max:5',
            'editTitle' => 'nullable|string|max:255',
            'editContent' => 'nullable|string|max:2000',
        ]);

        $review = Review::where('user_id', auth()->id())->find($this->editingReviewId);

        if (!$review) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Ulasan tidak ditemukan.',
            ]);
            return;
        }

        $this->authorize('update', $review);

        ReviewService::updateReview(
            $review,
            $this->editRating,
            $this->editContent ?: null,
            $this->editTitle ?: null
        );

        $this->cancelEdit();
        $this->loadReviews();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil diperbarui.',
        ]);
    }

    public function deleteReview(int $reviewId): void
    {
        $review = Review::where('user_id', auth()->id())->find($reviewId);

        if (!$review) {
            return;
        }

        $this->authorize('delete', $review);

        ReviewService::deleteReview($review);

        $this->loadReviews();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }

    public function render()
    {
        return view('livewire.customer.my-reviews');
    }
}
