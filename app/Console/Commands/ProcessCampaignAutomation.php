<?php

namespace App\Console\Commands;

use App\Models\Marketing\CustomerVoucher;
use App\Models\User;
use App\Notifications\VoucherExpiringSoonNotification;
use App\Services\CampaignService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessCampaignAutomation extends Command
{
    protected $signature = 'campaign:process
                            {--action= : Action to process: all, birthday, weekend, expire-vouchers, end-campaigns}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Process campaign automation tasks';

    public function handle(CampaignService $campaignService): int
    {
        $action = $this->option('action') ?? 'all';
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made');
        }

        $this->info("Processing campaign automation: {$action}");

        if ($action === 'all' || $action === 'birthday') {
            $this->processBirthdayCampaign($campaignService, $dryRun);
        }

        if ($action === 'all' || $action === 'weekend') {
            $this->processWeekendSale($campaignService, $dryRun);
        }

        if ($action === 'all' || $action === 'expire-vouchers') {
            $this->expireOldVouchers($campaignService, $dryRun);
        }

        if ($action === 'all' || $action === 'end-campaigns') {
            $this->endExpiredCampaigns($campaignService, $dryRun);
        }

        if ($action === 'all' || $action === 'expiring-notify') {
            $this->notifyExpiringVouchers($dryRun);
        }

        if ($action === 'all' || $action === 'loyal-customer') {
            $this->processLoyalCustomer($campaignService, $dryRun);
        }

        $this->info('Campaign automation completed!');

        return Command::SUCCESS;
    }

    protected function processBirthdayCampaign(CampaignService $campaignService, bool $dryRun): void
    {
        $this->info('Processing birthday campaigns...');

        // Check if birthday column exists
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'birthday')) {
            $this->warn('  Birthday feature not available (no birthday column in users table)');
            return;
        }

        if ($dryRun) {
            $users = User::whereMonth('birthday', now()->month)
                ->whereDay('birthday', now()->day)
                ->count();
            $this->info("  Would process {$users} birthday customers");
            return;
        }

        $result = $campaignService->processBirthdayCampaign();
        $this->info("  Processed {$result['processed']} birthday vouchers");
    }

    protected function processWeekendSale(CampaignService $campaignService, bool $dryRun): void
    {
        $this->info('Processing weekend sale...');

        if ($dryRun) {
            $this->info('  Would activate weekend campaign');
            return;
        }

        $result = $campaignService->processWeekendSale();
        if ($result['activated']) {
            $this->info("  Activated weekend campaign with {$result['vouchers_generated']} vouchers");
        } else {
            $this->info('  No weekend campaign to activate');
        }
    }

    protected function expireOldVouchers(CampaignService $campaignService, bool $dryRun): void
    {
        $this->info('Expiring old vouchers...');

        if ($dryRun) {
            $count = CustomerVoucher::where('status', CustomerVoucher::STATUS_ACTIVE)
                ->where('valid_until', '<', now())
                ->count();
            $this->info("  Would expire {$count} vouchers");
            return;
        }

        $count = $campaignService->expireOldVouchers();
        $this->info("  Expired {$count} vouchers");
    }

    protected function endExpiredCampaigns(CampaignService $campaignService, bool $dryRun): void
    {
        $this->info('Ending expired campaigns...');

        if ($dryRun) {
            $count = \App\Models\Marketing\MarketingCampaign::whereIn('status', ['active', 'scheduled'])
                ->where('end_date', '<', now())
                ->count();
            $this->info("  Would end {$count} campaigns");
            return;
        }

        $count = $campaignService->endExpiredCampaigns();
        $this->info("  Ended {$count} campaigns");
    }

    protected function notifyExpiringVouchers(bool $dryRun): void
    {
        $this->info('Notifying users about expiring vouchers...');

        $expiringVouchers = CustomerVoucher::expiringSoon(3)
            ->where('status', CustomerVoucher::STATUS_ACTIVE)
            ->get()
            ->groupBy('user_id');

        $notified = 0;

        foreach ($expiringVouchers as $userId => $vouchers) {
            $user = User::find($userId);
            if (!$user) continue;

            foreach ($vouchers as $voucher) {
                if ($dryRun) {
                    $notified++;
                    continue;
                }

                try {
                    $user->notify(new VoucherExpiringSoonNotification(
                        $voucher,
                        $voucher->days_until_expiry ?? 3
                    ));
                    $notified++;
                } catch (\Exception $e) {
                    Log::error('Failed to send expiring voucher notification', [
                        'user_id' => $userId,
                        'voucher_id' => $voucher->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("  Notified {$notified} users about expiring vouchers");
    }

    protected function processLoyalCustomer(CampaignService $campaignService, bool $dryRun): void
    {
        $this->info('Processing loyal customer rewards...');

        $users = User::whereHas('roles', fn($q) => $q->where('name', 'pelanggan'))
            ->withCount(['orders' => fn($q) => $q->where('status', 'completed')])
            ->having('orders_count', '>=', 10)
            ->get();

        $processed = 0;

        foreach ($users as $user) {
            if ($dryRun) {
                $processed++;
                continue;
            }

            $voucher = $campaignService->processLoyalCustomerCampaign($user);
            if ($voucher) {
                $processed++;
            }
        }

        $this->info("  Processed {$processed} loyal customers");
    }
}
