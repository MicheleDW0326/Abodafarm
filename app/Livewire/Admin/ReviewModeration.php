<?php

namespace App\Livewire\Admin;

use App\Enums\ReviewStatusEnum;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Services\ReviewService;
use App\Services\TrustService;
use Illuminate\Support\Facades\DB;
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

    // Report handling
    public int $selectedReportId = 0;
    public ?ReviewReport $selectedReport = null;
    public string $reportAction = 'resolve';
    public string $adminNotes = '';

    public function getReviewsProperty()
    {
        return Review::with(['user', 'product', 'farmer.farmProfile', 'photos', 'videos'])
            ->withCount(['likes', 'reports'])
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

    public function getPendingReportsProperty()
    {
        return ReviewReport::query()
            ->with(['review', 'user'])
            ->pending()
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function getStatsProperty(): array
    {
        // OPTIMIZED: Single query with GROUP BY instead of 6 separate queries
        $reviewStats = Review::query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = \'rejected\' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN is_visible = 0 THEN 1 ELSE 0 END) as hidden
            ')
            ->first();

        $pendingReports = ReviewReport::pending()->count();

        return [
            'total' => (int) $reviewStats->total,
            'pending' => (int) $reviewStats->pending,
            'approved' => (int) $reviewStats->approved,
            'rejected' => (int) $reviewStats->rejected,
            'hidden' => (int) $reviewStats->hidden,
            'pending_reports' => (int) $pendingReports,
        ];
    }

    public function approveReview(int $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        ReviewService::approveReview($review);

        // Update farmer rating
        if ($review->farmer_id) {
            $trustService = app(TrustService::class);
            $user = \App\Models\User::find($review->farmer_id);
            if ($user) {
                $trustService->updateFarmerRating($user);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil disetujui.',
        ]);
    }

    public function toggleVisibility(int $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        if ($review->is_visible) {
            ReviewService::hideReview($review);
            $this->dispatch('notify', [
                'type' => 'info',
                'title' => 'Berhasil',
                'message' => 'Ulasan berhasil disembunyikan.',
            ]);
        } else {
            ReviewService::showReview($review);
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Ulasan berhasil ditampilkan.',
            ]);
        }
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
        $farmerId = $review->farmer_id;

        // Delete related records
        $review->photos()->delete();
        $review->videos()->delete();
        $review->likes()->delete();
        $review->reports()->delete();

        ReviewService::deleteReview($review);

        // Update farmer rating
        if ($farmerId) {
            $trustService = app(TrustService::class);
            $user = \App\Models\User::find($farmerId);
            if ($user) {
                $trustService->updateFarmerRating($user);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }

    // Report handling
    public function selectReport(ReviewReport $report): void
    {
        $this->selectedReport = $report->load(['review', 'user']);
        $this->selectedReportId = $report->id;
        $this->adminNotes = '';
    }

    public function clearReportSelection(): void
    {
        $this->selectedReport = null;
        $this->selectedReportId = 0;
        $this->adminNotes = '';
    }

    public function resolveReport(): void
    {
        if (!$this->selectedReport) {
            return;
        }

        $status = $this->reportAction === 'resolve'
            ? ReviewReport::STATUS_RESOLVED
            : ReviewReport::STATUS_DISMISSED;

        $this->selectedReport->markAsReviewed(
            auth()->id(),
            $this->adminNotes,
            $status
        );

        // If resolved and action is hide_review, hide the review
        if ($this->reportAction === 'resolve' && $this->selectedReport->review) {
            $this->selectedReport->review->update(['is_visible' => false]);
        }

        $this->clearReportSelection();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Laporan berhasil diproses.',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.review-moderation', [
            'reviews' => $this->reviews,
            'pendingReports' => $this->pendingReports,
            'stats' => $this->stats,
        ]);
    }
}
