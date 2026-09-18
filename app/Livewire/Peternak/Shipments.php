<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Pengiriman')]
class Shipments extends Component
{
    public string $statusFilter = 'all';

    public function render()
    {
        $userId = auth()->id();

        // Get cart orders with shipment statuses (shipped or processing)
        $shipmentsQuery = Order::cart()
            ->whereHas('items.product', function ($query) use ($userId) {
                $query->where('owner_id', $userId);
            })
            ->whereIn('status', ['shipped', 'processing'])
            ->with(['items.product' => function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            }])
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $shipments = $shipmentsQuery->get();

        // OPTIMIZED: Single query for stats using CASE WHEN
        $statsData = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->where('o.order_type', 'cart')
            ->whereIn('o.status', ['shipped', 'processing'])
            ->selectRaw("
                SUM(CASE WHEN o.status = 'shipped' THEN 1 ELSE 0 END) as in_transit,
                SUM(CASE WHEN o.status = 'processing' THEN 1 ELSE 0 END) as processing
            ")
            ->first();

        $stats = [
            'in_transit' => (int) $statsData->in_transit,
            'processing' => (int) $statsData->processing,
        ];

        return view('livewire.peternak.shipments', [
            'shipments' => $shipments,
            'stats' => $stats,
        ]);
    }
}
