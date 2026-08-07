<?php

namespace App\Livewire\Admin;

use App\Models\Marketing\CustomerVoucher;
use App\Models\Marketing\MarketingCampaign;
use App\Services\CampaignService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Analisis Kampanye - AbodaFarm')]
class CampaignAnalytics extends Component
{
    public ?MarketingCampaign $selectedCampaign = null;

    protected CampaignService $campaignService;

    public function mount(CampaignService $campaignService): void
    {
        $this->campaignService = $campaignService;
    }

    public function selectCampaign(MarketingCampaign $campaign): void
    {
        $this->selectedCampaign = $campaign;
    }

    public function clearSelection(): void
    {
        $this->selectedCampaign = null;
    }

    public function getOverallStatsProperty(): array
    {
        $totalVouchers = CustomerVoucher::count();
        $activeVouchers = CustomerVoucher::active()->available()->count();
        $usedVouchers = CustomerVoucher::where('status', CustomerVoucher::STATUS_USED)->count();
        $expiredVouchers = CustomerVoucher::where('status', CustomerVoucher::STATUS_EXPIRED)->count();
        $totalDiscountGiven = CustomerVoucher::where('status', CustomerVoucher::STATUS_USED)->sum('discount_amount');

        return [
            'total_vouchers' => $totalVouchers,
            'active_vouchers' => $activeVouchers,
            'used_vouchers' => $usedVouchers,
            'expired_vouchers' => $expiredVouchers,
            'total_discount_given' => $totalDiscountGiven,
            'conversion_rate' => $totalVouchers > 0 ? round(($usedVouchers / $totalVouchers) * 100, 2) : 0,
        ];
    }

    public function getCampaignStatsProperty(): array
    {
        return MarketingCampaign::query()
            ->withCount(['vouchers', 'vouchers as claimed_count' => fn($q) => $q->whereNotNull('user_id')])
            ->get()
            ->map(fn($campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'type' => $campaign->type_label,
                'status' => $campaign->status_label,
                'total_generated' => $campaign->vouchers_count,
                'total_claimed' => $campaign->claimed_count,
                'total_used' => $campaign->total_vouchers_used,
                'conversion_rate' => $campaign->conversion_rate,
                'total_discount' => $campaign->total_discount_given,
                'start_date' => $campaign->start_date->format('d M Y'),
                'end_date' => $campaign->end_date->format('d M Y'),
            ])
            ->toArray();
    }

    public function getTopCampaignsProperty(): array
    {
        return MarketingCampaign::query()
            ->orderBy('total_vouchers_used', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($campaign) => [
                'name' => $campaign->name,
                'type' => $campaign->type_label,
                'used' => $campaign->total_vouchers_used,
                'conversion' => $campaign->conversion_rate,
                'discount' => $campaign->total_discount_given,
            ])
            ->toArray();
    }

    public function getVoucherUsageByTypeProperty(): array
    {
        $types = CustomerVoucher::selectRaw('discount_type, COUNT(*) as count, SUM(discount_amount) as total_discount')
            ->where('status', CustomerVoucher::STATUS_USED)
            ->groupBy('discount_type')
            ->get();

        return $types->map(fn($item) => [
            'type' => CustomerVoucher::getDiscountTypes()[$item->discount_type] ?? $item->discount_type,
            'count' => $item->count,
            'total_discount' => $item->total_discount,
        ])->toArray();
    }

    public function getRecentVoucherUsagesProperty(): array
    {
        return CustomerVoucher::with(['user', 'campaign'])
            ->where('status', CustomerVoucher::STATUS_USED)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($voucher) => [
                'code' => $voucher->voucher_code,
                'name' => $voucher->name,
                'user' => $voucher->user?->name ?? 'Unknown',
                'discount' => $voucher->discount_amount,
                'used_at' => $voucher->updated_at->format('d M Y H:i'),
            ])
            ->toArray();
    }

    public function getSelectedCampaignAnalyticsProperty(): ?array
    {
        if (!$this->selectedCampaign) {
            return null;
        }

        return $this->campaignService->getCampaignAnalytics($this->selectedCampaign);
    }

    public function render()
    {
        return view('livewire.admin.campaign-analytics');
    }
}
