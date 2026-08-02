<?php

namespace App\Livewire\Admin;

use App\Enums\ReviewStatusEnum;
use App\Models\Review;
use App\Services\ReviewService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Moderasi Ulasan - Admin')]
class ReviewModeration extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $statusFilter = 'pending';
    public string $searchQuery = '';
    public ?int $selectedReviewId = null;
    public string $rejectionReason = '';

    public function getReviewsProperty()
    {
        return Review::with(['user', 'product', 'farmer', 'photos'])
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$this->searchQuery}%"))
                        ->orWhere('content', 'like', "%{$this->searchQuery}%")
                        ->orWhere('title', 'like', "%{$this->searchQuery}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => Review::count(),
            'pending' => Review::where('status', ReviewStatusEnum::PENDING->value)->count(),
            'approved' => Review::where('status', ReviewStatusEnum::APPROVED->value)->count(),
            'rejected' => Review::where('status', ReviewStatusEnum::REJECTED->value)->count(),
        ];
    }

    public function approveReview(int $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        ReviewService::approveReview($review);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil disetujui.',
        ]);
    }

    public function openRejectModal(int $reviewId)
    {
        $this->selectedReviewId = $reviewId;
        $this->rejectionReason = '';
    }

    public function closeRejectModal()
    {
        $this->selectedReviewId = null;
        $this->rejectionReason = '';
    }

    public function confirmRejection()
    {
        $this->validate([
            'rejectionReason' => 'required|string|max:500',
        ]);

        $review = Review::findOrFail($this->selectedReviewId);
        ReviewService::rejectReview($review, $this->rejectionReason);

        $this->closeRejectModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil ditolak.',
        ]);
    }

    public function deleteReview(int $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        ReviewService::deleteReview($review);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.review-moderation', [
            'reviews' => $this->reviews,
            'stats' => $this->stats,
        ]);
    }
}
