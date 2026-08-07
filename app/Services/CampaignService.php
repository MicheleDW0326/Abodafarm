<?php

namespace App\Services;

use App\Models\Marketing\CampaignRule;
use App\Models\Marketing\CustomerVoucher;
use App\Models\Marketing\MarketingCampaign;
use App\Models\Order;
use App\Models\User;
use App\Events\VoucherClaimed;
use App\Events\VoucherExpired;
use App\Notifications\NewVoucherNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignService
{
    /**
     * Create a new marketing campaign.
     */
    public function createCampaign(array $data): MarketingCampaign
    {
        $data['slug'] = MarketingCampaign::generateSlug($data['name']);
        $data['created_by'] = auth()->id();

        return MarketingCampaign::create($data);
    }

    /**
     * Update an existing campaign.
     */
    public function updateCampaign(MarketingCampaign $campaign, array $data): MarketingCampaign
    {
        if (isset($data['name']) && $data['name'] !== $campaign->name) {
            $data['slug'] = MarketingCampaign::generateSlug($data['name']);
        }

        $campaign->update($data);
        return $campaign->fresh();
    }

    /**
     * Add rules to a campaign.
     */
    public function addRules(MarketingCampaign $campaign, array $rules): void
    {
        foreach ($rules as $rule) {
            $campaign->rules()->create($rule);
        }
    }

    /**
     * Check if a user qualifies for a campaign based on rules.
     */
    public function userQualifiesForCampaign(User $user, MarketingCampaign $campaign): bool
    {
        $rules = $campaign->rules;

        if ($rules->isEmpty()) {
            return true;
        }

        $context = $this->buildUserContext($user);

        foreach ($rules as $rule) {
            if (!$rule->evaluate($context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build evaluation context for a user.
     */
    protected function buildUserContext(User $user): array
    {
        $user->load(['orders', 'addresses']);

        $totalOrders = $user->orders()->where('status', 'completed')->count();
        $totalSpent = $user->orders()->where('status', 'completed')->sum('total_amount');
        $defaultAddress = $user->addresses()->first();

        return [
            'customer_total_orders' => $totalOrders,
            'customer_total_spent' => $totalSpent,
            'customer_role' => $user->roles->first()?->name,
            'customer_registration_date' => $user->created_at,
            'province' => $defaultAddress?->province,
            'city' => $defaultAddress?->city,
            'first_order' => $totalOrders === 0,
        ];
    }

    /**
     * Generate and distribute vouchers for a campaign.
     */
    public function generateVouchers(MarketingCampaign $campaign, array $targetUsers = []): array
    {
        if (!$campaign->auto_generate_voucher) {
            return ['generated' => 0, 'vouchers' => []];
        }

        $vouchers = [];
        $quantity = $campaign->voucher_quantity ?? 100;

        if (!empty($targetUsers)) {
            $users = User::whereIn('id', $targetUsers)->get();
        } else {
            $users = User::whereHas('roles', fn($q) => $q->where('name', 'pelanggan'))->get();
        }

        foreach ($users->take($quantity) as $user) {
            if (!$this->userQualifiesForCampaign($user, $campaign)) {
                continue;
            }

            if ($campaign->max_claim && $this->getUserVoucherCount($campaign, $user->id) >= $campaign->max_claim) {
                continue;
            }

            $voucher = $this->createVoucher($campaign, $user);
            if ($voucher) {
                $vouchers[] = $voucher;
            }
        }

        $campaign->update(['total_vouchers_generated' => count($vouchers)]);

        return ['generated' => count($vouchers), 'vouchers' => $vouchers];
    }

    /**
     * Create a voucher for a specific user.
     */
    public function createVoucher(MarketingCampaign $campaign, User $user, array $overrides = []): ?CustomerVoucher
    {
        $prefix = $this->getVoucherPrefix($campaign);

        $voucher = CustomerVoucher::create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'voucher_code' => CustomerVoucher::generateCode($prefix),
            'name' => $overrides['name'] ?? $campaign->name,
            'description' => $overrides['description'] ?? $campaign->description,
            'discount_type' => $overrides['discount_type'] ?? $this->getDefaultDiscountType($campaign),
            'discount_value' => $overrides['discount_value'] ?? 10,
            'maximum_discount' => $overrides['maximum_discount'] ?? null,
            'minimum_order' => $overrides['minimum_order'] ?? 100000,
            'usage_limit' => $overrides['usage_limit'] ?? 1,
            'used_count' => 0,
            'valid_from' => $overrides['valid_from'] ?? now(),
            'valid_until' => $overrides['valid_until'] ?? $campaign->end_date,
            'status' => CustomerVoucher::STATUS_ACTIVE,
            'is_auto_applied' => false,
        ]);

        $campaign->incrementClaimed();

        event(new VoucherClaimed($voucher, $user));

        $this->notifyUser($user, $voucher);

        return $voucher;
    }

    /**
     * Get voucher prefix based on campaign type.
     */
    protected function getVoucherPrefix(MarketingCampaign $campaign): string
    {
        return match($campaign->campaign_type) {
            MarketingCampaign::TYPE_BIRTHDAY => 'BDAY',
            MarketingCampaign::TYPE_NEW_CUSTOMER => 'WELCOME',
            MarketingCampaign::TYPE_LOYAL_CUSTOMER => 'LOYAL',
            MarketingCampaign::TYPE_FREE_SHIPPING => 'SHIP',
            MarketingCampaign::TYPE_CASHBACK => 'CASH',
            MarketingCampaign::TYPE_REVIEW_REWARD => 'REV',
            default => 'VCH',
        };
    }

    /**
     * Get default discount type for campaign.
     */
    protected function getDefaultDiscountType(MarketingCampaign $campaign): string
    {
        return match($campaign->campaign_type) {
            MarketingCampaign::TYPE_FREE_SHIPPING => CustomerVoucher::DISCOUNT_FREE_SHIPPING,
            MarketingCampaign::TYPE_CASHBACK => CustomerVoucher::DISCOUNT_CASHBACK,
            default => CustomerVoucher::DISCOUNT_PERCENTAGE,
        };
    }

    /**
     * Notify user about new voucher.
     */
    protected function notifyUser(User $user, CustomerVoucher $voucher): void
    {
        try {
            $user->notify(new NewVoucherNotification($voucher));
        } catch (\Exception $e) {
            Log::error('Failed to notify user about voucher', [
                'user_id' => $user->id,
                'voucher_id' => $voucher->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get user's voucher count for a campaign.
     */
    public function getUserVoucherCount(MarketingCampaign $campaign, int $userId): int
    {
        return CustomerVoucher::where('campaign_id', $campaign->id)
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Get user's available vouchers.
     */
    public function getUserVouchers(int $userId, string $status = 'available'): Collection
    {
        $query = CustomerVoucher::forUser($userId)->available();

        return match($status) {
            'available' => $query->orderBy('valid_until', 'asc')->get(),
            'used' => CustomerVoucher::forUser($userId)->where('status', CustomerVoucher::STATUS_USED)->get(),
            'expired' => CustomerVoucher::forUser($userId)->where('status', CustomerVoucher::STATUS_EXPIRED)->get(),
            'all' => CustomerVoucher::forUser($userId)->get(),
            default => $query->get(),
        };
    }

    /**
     * Find best voucher for a cart.
     */
    public function findBestVoucher(int $userId, float $subtotal, array $cartItems = []): ?CustomerVoucher
    {
        $vouchers = $this->getUserVouchers($userId, 'available');

        if ($vouchers->isEmpty()) {
            return null;
        }

        $bestVoucher = null;
        $bestDiscount = 0;

        foreach ($vouchers as $voucher) {
            $discount = $voucher->calculateDiscount($subtotal);
            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestVoucher = $voucher;
            }
        }

        return $bestVoucher;
    }

    /**
     * Get expiring vouchers for a user.
     */
    public function getExpiringVouchers(int $userId, int $days = 3): Collection
    {
        return CustomerVoucher::forUser($userId)
            ->expiringSoon($days)
            ->get();
    }

    /**
     * Expire old vouchers.
     */
    public function expireOldVouchers(): int
    {
        $vouchers = CustomerVoucher::where('status', CustomerVoucher::STATUS_ACTIVE)
            ->where('valid_until', '<', now())
            ->get();

        foreach ($vouchers as $voucher) {
            $voucher->markAsExpired();
            event(new VoucherExpired($voucher));
        }

        return $vouchers->count();
    }

    /**
     * Process birthday campaign.
     */
    public function processBirthdayCampaign(): array
    {
        // Check if birthday column exists
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'birthday')) {
            return ['processed' => 0, 'error' => 'birthday column not found'];
        }

        $campaign = MarketingCampaign::where('campaign_type', MarketingCampaign::TYPE_BIRTHDAY)
            ->active()
            ->first();

        if (!$campaign) {
            return ['processed' => 0];
        }

        $users = User::whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day)
            ->whereHas('roles', fn($q) => $q->where('name', 'pelanggan'))
            ->get();

        $count = 0;
        foreach ($users as $user) {
            if ($this->getUserVoucherCount($campaign, $user->id) < ($campaign->max_claim ?? 1)) {
                $this->createVoucher($campaign, $user, [
                    'valid_until' => now()->addDays(7),
                    'name' => 'Happy Birthday!',
                    'description' => 'Voucher ulang tahun spesial untuk Anda!',
                    'discount_type' => CustomerVoucher::DISCOUNT_PERCENTAGE,
                    'discount_value' => 15,
                    'maximum_discount' => 50000,
                    'minimum_order' => 100000,
                ]);
                $count++;
            }
        }

        return ['processed' => $count, 'campaign' => $campaign];
    }

    /**
     * Process new customer campaign.
     */
    public function processNewCustomerCampaign(User $user): ?CustomerVoucher
    {
        $campaign = MarketingCampaign::where('campaign_type', MarketingCampaign::TYPE_NEW_CUSTOMER)
            ->active()
            ->first();

        if (!$campaign) {
            return null;
        }

        if (!$this->userQualifiesForCampaign($user, $campaign)) {
            return null;
        }

        return $this->createVoucher($campaign, $user);
    }

    /**
     * Process loyal customer campaign.
     */
    public function processLoyalCustomerCampaign(User $user): ?CustomerVoucher
    {
        $completedOrders = $user->orders()->where('status', 'completed')->count();

        if ($completedOrders < 10) {
            return null;
        }

        $campaign = MarketingCampaign::where('campaign_type', MarketingCampaign::TYPE_LOYAL_CUSTOMER)
            ->active()
            ->first();

        if (!$campaign) {
            return null;
        }

        if (!$this->userQualifiesForCampaign($user, $campaign)) {
            return null;
        }

        if ($this->getUserVoucherCount($campaign, $user->id) >= ($campaign->max_claim ?? 1)) {
            return null;
        }

        return $this->createVoucher($campaign, $user, [
            'discount_type' => CustomerVoucher::DISCOUNT_PERCENTAGE,
            'discount_value' => 20,
            'maximum_discount' => 100000,
            'minimum_order' => 200000,
            'usage_limit' => 3,
            'name' => 'Terima Kasih Pelanggan Setia!',
            'description' => 'Voucher penghargaan untuk pelanggan setia dengan 10+ pesanan',
        ]);
    }

    /**
     * Process cart abandonment.
     */
    public function processCartAbandonment(User $user, array $cartItems): ?CustomerVoucher
    {
        $campaign = MarketingCampaign::where('campaign_type', MarketingCampaign::TYPE_CART_ABANDONMENT)
            ->active()
            ->first();

        if (!$campaign) {
            return null;
        }

        if ($this->getUserVoucherCount($campaign, $user->id) >= ($campaign->max_claim ?? 1)) {
            return null;
        }

        return $this->createVoucher($campaign, $user, [
            'discount_type' => CustomerVoucher::DISCOUNT_PERCENTAGE,
            'discount_value' => 5,
            'maximum_discount' => 25000,
            'minimum_order' => 100000,
            'valid_until' => now()->addDays(3),
            'name' => 'Jangan Lupa Pesanan Anda!',
            'description' => 'Voucher spesial karena Anda meninggalkan keranjang',
        ]);
    }

    /**
     * Process review reward.
     */
    public function processReviewReward(User $user): ?CustomerVoucher
    {
        $campaign = MarketingCampaign::where('campaign_type', MarketingCampaign::TYPE_REVIEW_REWARD)
            ->active()
            ->first();

        if (!$campaign) {
            return null;
        }

        if ($this->getUserVoucherCount($campaign, $user->id) >= ($campaign->max_claim ?? 1)) {
            return null;
        }

        return $this->createVoucher($campaign, $user, [
            'discount_type' => CustomerVoucher::DISCOUNT_FIXED,
            'discount_value' => 10000,
            'minimum_order' => 50000,
            'valid_until' => now()->addDays(30),
            'name' => 'Terima Kasih atas Ulasan Anda!',
            'description' => 'Voucher apresiasi untuk ulasan produk Anda',
        ]);
    }

    /**
     * Process weekend sale campaign.
     */
    public function processWeekendSale(): array
    {
        $today = now()->dayOfWeek;

        if (!in_array($today, [5, 6, 7])) {
            return ['activated' => false];
        }

        $campaign = MarketingCampaign::where('campaign_type', MarketingCampaign::TYPE_WEEKEND_SALE)
            ->scheduled()
            ->first();

        if (!$campaign) {
            return ['activated' => false];
        }

        $campaign->update(['status' => MarketingCampaign::STATUS_ACTIVE]);

        $result = $this->generateVouchers($campaign);

        return [
            'activated' => true,
            'campaign' => $campaign,
            'vouchers_generated' => $result['generated'],
        ];
    }

    /**
     * End expired campaigns.
     */
    public function endExpiredCampaigns(): int
    {
        $campaigns = MarketingCampaign::whereIn('status', [
            MarketingCampaign::STATUS_ACTIVE,
            MarketingCampaign::STATUS_SCHEDULED,
        ])->where('end_date', '<', now())->get();

        foreach ($campaigns as $campaign) {
            $campaign->update(['status' => MarketingCampaign::STATUS_ENDED]);
        }

        return $campaigns->count();
    }

    /**
     * Get campaign analytics.
     */
    public function getCampaignAnalytics(MarketingCampaign $campaign): array
    {
        $vouchers = $campaign->vouchers;

        return [
            'total_generated' => $vouchers->count(),
            'total_claimed' => $campaign->total_vouchers_claimed,
            'total_used' => $campaign->total_vouchers_used,
            'total_expired' => $vouchers->where('status', CustomerVoucher::STATUS_EXPIRED)->count(),
            'total_discount_given' => $campaign->total_discount_given,
            'conversion_rate' => $campaign->conversion_rate,
            'budget_used' => $campaign->budget_used,
            'budget_limit' => $campaign->budget_limit,
            'budget_remaining' => $campaign->budget_limit ? $campaign->budget_limit - $campaign->budget_used : null,
        ];
    }
}
