<?php

namespace App\Livewire\Admin;

use App\Models\FarmProfile;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Verifikasi Peternak - Admin')]
class FarmerVerification extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public string $verificationFilter = 'all';

    public function getFarmersProperty()
    {
        return FarmProfile::with('user')
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($query) {
                    $query->where('farm_name', 'like', "%{$this->searchQuery}%")
                        ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$this->searchQuery}%"));
                });
            })
            ->when($this->verificationFilter !== 'all', function ($q) {
                $q->where('is_verified', $this->verificationFilter === 'verified');
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                if ($this->statusFilter === 'complete') {
                    $q->where('profile_completion', '>=', 70);
                } else {
                    $q->where('profile_completion', '<', 70);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => FarmProfile::count(),
            'verified' => FarmProfile::where('is_verified', true)->count(),
            'unverified' => FarmProfile::where('is_verified', false)->count(),
            'complete' => FarmProfile::where('profile_completion', '>=', 70)->count(),
            'incomplete' => FarmProfile::where('profile_completion', '<', 70)->count(),
        ];
    }

    public function toggleVerification(FarmProfile $profile)
    {
        $profile->update(['is_verified' => !$profile->is_verified]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => $profile->is_verified
                ? "{$profile->farm_name} telah diverifikasi."
                : "Verifikasi {$profile->farm_name} dicabut.",
        ]);
    }

    public function verifyMultiple(array $ids)
    {
        FarmProfile::whereIn('id', $ids)->update(['is_verified' => true]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => count($ids) . ' peternak berhasil diverifikasi.',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.farmer-verification', [
            'farmers' => $this->farmers,
            'stats' => $this->stats,
        ]);
    }
}
