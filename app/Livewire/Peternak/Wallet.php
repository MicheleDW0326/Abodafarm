<?php

namespace App\Livewire\Peternak;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
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

        // Calculate earnings
        $earningsQuery = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);

        $grossSales = (float) $earningsQuery->sum('subtotal');
        $commissionRate = 0.05; // 5% commission
        $commission = $grossSales * $commissionRate;
        $netEarnings = $grossSales - $commission;

        // Transaction history
        $transactions = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('orders.placed_at, orders.order_number, order_items.product_name, order_items.quantity, order_items.subtotal')
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->orderBy('orders.placed_at', 'desc')
            ->limit(20)
            ->get();

        return view('livewire.peternak.wallet', [
            'grossSales' => $grossSales,
            'commission' => $commission,
            'netEarnings' => $netEarnings,
            'commissionRate' => $commissionRate,
            'transactions' => $transactions,
        ]);
    }
}
