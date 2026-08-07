<?php

namespace App\Console\Commands;

use App\Models\Marketing\MarketingCampaign;
use App\Services\CampaignService;
use Illuminate\Console\Command;

class GenerateCampaignVouchers extends Command
{
    protected $signature = 'campaign:generate-vouchers
                            {campaign_id : The campaign ID to generate vouchers for}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Generate vouchers for a specific campaign';

    public function handle(CampaignService $campaignService): int
    {
        $campaignId = (int) $this->argument('campaign_id');
        $dryRun = $this->option('dry-run');

        $campaign = MarketingCampaign::find($campaignId);

        if (!$campaign) {
            $this->error("Campaign not found: {$campaignId}");
            return Command::FAILURE;
        }

        if (!$campaign->auto_generate_voucher) {
            $this->error("Campaign does not have auto_generate_voucher enabled");
            return Command::FAILURE;
        }

        $this->info("Generating vouchers for campaign: {$campaign->name}");
        $this->info("Type: {$campaign->campaign_type}");

        if ($dryRun) {
            $this->warn('DRY RUN - No vouchers will be created');
            $quantity = $campaign->voucher_quantity ?? 100;
            $this->info("Would generate up to {$quantity} vouchers");
            return Command::SUCCESS;
        }

        $result = $campaignService->generateVouchers($campaign);

        $this->info("Generated {$result['generated']} vouchers");

        if (!empty($result['vouchers'])) {
            $this->table(
                ['Code', 'User', 'Discount', 'Valid Until'],
                collect($result['vouchers'])->take(10)->map(fn($v) => [
                    $v->voucher_code,
                    $v->user->name,
                    $v->formatted_discount,
                    $v->valid_until->format('d M Y'),
                ])
            );

            if (count($result['vouchers']) > 10) {
                $this->info('... and ' . (count($result['vouchers']) - 10) . ' more');
            }
        }

        return Command::SUCCESS;
    }
}
