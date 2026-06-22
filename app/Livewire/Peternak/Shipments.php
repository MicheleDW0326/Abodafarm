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

        $shipmentsQuery = Order::whereHas('items.product', function ($query) use ($userId) {
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

        $stats = [
            'in_transit' => Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))->where('status', 'shipped')->count(),
            'processing' => Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))->where('status', 'processing')->count(),
        ];

        return view('livewire.peternak.shipments', [
            'shipments' => $shipments,
            'stats' => $stats,
        ]);
    }
}
