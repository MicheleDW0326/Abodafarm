<?php

namespace App\Livewire\Pelanggan;

use App\Models\Marketing\CustomerVoucher;
use App\Services\CampaignService;
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

    public function getAvailableCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }
        return $this->campaignService->getUserVouchers(auth()->id(), 'available')->count();
    }

    public function getUsedCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }
        return $this->campaignService->getUserVouchers(auth()->id(), 'used')->count();
    }

    public function getExpiredCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }
        return $this->campaignService->getUserVouchers(auth()->id(), 'expired')->count();
    }

    public function getExpiringCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }
        return $this->campaignService->getExpiringVouchers(auth()->id(), 3)->count();
    }

    public function render()
    {
        return view('livewire.pelanggan.voucher-wallet', [
            'vouchers' => $this->vouchers,
        ]);
    }
}
