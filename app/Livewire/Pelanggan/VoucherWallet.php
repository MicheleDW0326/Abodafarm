<?php

namespace App\Livewire\Pelanggan;

use App\Models\Marketing\CustomerVoucher;
use App\Services\CampaignService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Voucher Saya - AbodaFarm')]
class VoucherWallet extends Component
{
    public string $activeTab = 'available';

    public ?CustomerVoucher $selectedVoucher = null;

    protected CampaignService $campaignService;

    public function mount(CampaignService $campaignService): void
    {
        $this->campaignService = $campaignService;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->selectedVoucher = null;
    }

    public function selectVoucher(CustomerVoucher $voucher): void
    {
        $this->selectedVoucher = $voucher;
    }

    public function closeDetail(): void
    {
        $this->selectedVoucher = null;
    }

    public function getVouchersProperty()
    {
        if (!auth()->check()) {
            return collect();
        }

        return $this->campaignService->getUserVouchers(auth()->id(), $this->activeTab);
    }

    public function getVoucherStatsProperty(): array
    {
        if (!auth()->check()) {
            return [
                'available' => 0,
                'used' => 0,
                'expired' => 0,
                'expiring' => 0,
            ];
        }

        $userId = auth()->id();

        // OPTIMIZED: Single query for all voucher counts
        $now = now();
        $threeDaysLater = $now->copy()->addDays(3);

        $counts = DB::table('customer_vouchers as cv')
            ->join('promotions as p', 'cv.promotion_id', '=', 'p.id')
            ->where('cv.user_id', $userId)
            ->selectRaw("
                SUM(CASE
                    WHEN cv.used_at IS NULL AND (p.expires_at IS NULL OR p.expires_at >= ?) THEN 1 ELSE 0
                END) as available,
                SUM(CASE WHEN cv.used_at IS NOT NULL THEN 1 ELSE 0 END) as used,
                SUM(CASE
                    WHEN cv.used_at IS NULL AND p.expires_at IS NOT NULL AND p.expires_at < ? THEN 1 ELSE 0
                END) as expired,
                SUM(CASE
                    WHEN cv.used_at IS NULL
                        AND p.expires_at IS NOT NULL
                        AND p.expires_at >= ?
                        AND p.expires_at <= ?
                    THEN 1 ELSE 0
                END) as expiring
            ", [$now, $now, $now, $threeDaysLater])
            ->first();

        return [
            'available' => (int) ($counts->available ?? 0),
            'used' => (int) ($counts->used ?? 0),
            'expired' => (int) ($counts->expired ?? 0),
            'expiring' => (int) ($counts->expiring ?? 0),
        ];
    }

    // Backward compatible property accessors
    public function getAvailableCountProperty(): int
    {
        return $this->voucherStats['available'] ?? 0;
    }

    public function getUsedCountProperty(): int
    {
        return $this->voucherStats['used'] ?? 0;
    }

    public function getExpiredCountProperty(): int
    {
        return $this->voucherStats['expired'] ?? 0;
    }

    public function getExpiringCountProperty(): int
    {
        return $this->voucherStats['expiring'] ?? 0;
    }

    public function render()
    {
        return view('livewire.pelanggan.voucher-wallet', [
            'vouchers' => $this->vouchers,
        ]);
    }
}
