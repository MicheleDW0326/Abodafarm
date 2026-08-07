<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOrderStock extends Command
{
    protected $signature = 'orders:sync-stock {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Sync stock for paid cart orders that may not have been decremented';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Searching for paid cart orders with potentially un-synced stock...');

        // Find cart orders that are paid (have payment with paid_at) but stock hasn't been decremented
        $orders = Order::cart()
            ->whereHas('payment', function ($query) {
                $query->whereNotNull('paid_at');
            })
            ->with(['items.product'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No paid cart orders found that need stock sync.');
            return Command::SUCCESS;
        }

        $this->info("Found {$orders->count()} orders to check.");

        $synced = 0;
        $skipped = 0;

        foreach ($orders as $order) {
            $itemsToSync = [];

            foreach ($order->items as $item) {
                if (!$item->product_id || !$item->product) {
                    continue;
                }

                $currentStock = $item->product->stock;
                $orderedQty = $item->quantity;
                $expectedStock = $currentStock - $orderedQty;

                // If stock hasn't been decremented (stock is still higher than expected)
                if ($currentStock >= $orderedQty) {
                    $itemsToSync[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'current_stock' => $currentStock,
                        'ordered_qty' => $orderedQty,
                        'new_stock' => max(0, $currentStock - $orderedQty),
                    ];
                }
            }

            if (!empty($itemsToSync)) {
                $this->line("\nOrder: {$order->order_number}");
                foreach ($itemsToSync as $item) {
                    $this->line("  - {$item['product_name']}: {$item['current_stock']} -> {$item['new_stock']} (ordered: {$item['ordered_qty']})");
                }

                if (!$isDryRun) {
                    foreach ($itemsToSync as $item) {
                        $item['product_id']::withoutEvents(function () use ($item) {
                            \App\Models\Product::where('id', $item['product_id'])
                                ->update(['stock' => $item['new_stock']]);
                        });
                    }

                    Log::info('Stock synced via command', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'items_synced' => count($itemsToSync),
                    ]);
                }

                $synced++;
            } else {
                $skipped++;
            }
        }

        $this->info("\n--- Summary ---");
        $this->info("Orders synced: {$synced}");
        $this->info("Orders skipped (already correct): {$skipped}");

        if ($isDryRun) {
            $this->warn("\nThis was a dry run. No changes were made. Run without --dry-run to apply changes.");
        } else {
            $this->info("\nStock sync completed!");
        }

        return Command::SUCCESS;
    }
}
