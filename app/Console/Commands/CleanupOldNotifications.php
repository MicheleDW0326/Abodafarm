<?php

namespace App\Console\Commands;

use App\Models\CustomerNotification;
use App\Models\PeternakNotification;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--hours=24 : Delete notifications older than this many hours}';

    protected $description = 'Delete notifications older than specified hours (default: 24 hours)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        // Cleanup PeternakNotifications
        $peternakCount = PeternakNotification::where('created_at', '<', $cutoff)->count();
        $peternakDeleted = 0;
        if ($peternakCount > 0) {
            $peternakDeleted = PeternakNotification::where('created_at', '<', $cutoff)->delete();
        }

        // Cleanup CustomerNotifications
        $customerCount = CustomerNotification::where('created_at', '<', $cutoff)->count();
        $customerDeleted = 0;
        if ($customerCount > 0) {
            $customerDeleted = CustomerNotification::where('created_at', '<', $cutoff)->delete();
        }

        $totalDeleted = $peternakDeleted + $customerDeleted;

        if ($totalDeleted === 0) {
            $this->info('No old notifications to delete.');
            return Command::SUCCESS;
        }

        $this->info("Deleted {$totalDeleted} notification(s) older than {$hours} hours:");
        if ($peternakDeleted > 0) {
            $this->info("  - PeternakNotifications: {$peternakDeleted}");
        }
        if ($customerDeleted > 0) {
            $this->info("  - CustomerNotifications: {$customerDeleted}");
        }

        return Command::SUCCESS;
    }
}
