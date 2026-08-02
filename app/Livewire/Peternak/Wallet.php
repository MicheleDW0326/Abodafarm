<?php

namespace App\Livewire\Peternak;

use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dompet')]
class Wallet extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $userId = auth()->id();

        // Completed statuses for earning calculation
        $completedStatuses = ['completed', 'delivered', 'approved', 'ready_for_delivery'];

        // Calculate earnings
        $earningsQuery = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->whereIn('orders.status', $completedStatuses)
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);

        $grossSales = (float) $earningsQuery->sum('subtotal');
        $commissionRate = 0.05; // 5% commission
        $commission = $grossSales * $commissionRate;
        $netEarnings = $grossSales - $commission;

        // Pending earnings (approved but not yet delivered)
        $pendingEarnings = (float) OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->whereIn('orders.status', ['approved', 'ready_for_delivery'])
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->sum('order_items.subtotal');

        // Transaction history
        $transactions = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->whereIn('orders.status', $completedStatuses)
            ->selectRaw('orders.placed_at, orders.order_number, orders.status as order_status, order_items.product_name, order_items.quantity, order_items.subtotal')
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->orderBy('orders.placed_at', 'desc')
            ->limit(20)
            ->get();

        return view('livewire.peternak.wallet', [
            'grossSales' => $grossSales,
            'commission' => $commission,
            'netEarnings' => $netEarnings,
            'pendingEarnings' => $pendingEarnings,
            'commissionRate' => $commissionRate,
            'transactions' => $transactions,
        ]);
    }
}
