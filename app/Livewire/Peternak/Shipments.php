<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
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

        $statsQuery = Order::cart()->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
        $stats = [
            'in_transit' => (clone $statsQuery)->where('status', 'shipped')->count(),
            'processing' => (clone $statsQuery)->where('status', 'processing')->count(),
        ];

        return view('livewire.peternak.shipments', [
            'shipments' => $shipments,
            'stats' => $stats,
        ]);
    }
}
