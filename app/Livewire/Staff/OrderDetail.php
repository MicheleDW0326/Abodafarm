<?php

namespace App\Livewire\Staff;

use App\Models\Order;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Detail Pesanan - Staff AbodaFarm')]
class OrderDetail extends Component
{
    public ?Order $order = null;

    public function mount(Request $request)
    {
        $orderId = $request->route('id');

        if (!$orderId) {
            abort(404);
        }

        $this->order = Order::with([
            'user',
            'items.product',
            'statusLogs.triggeredBy',
            'assignedStaff',
            'assignedByUser',
            'payment',
            'paymentStatusRelation',
            'shipmentStatusRelation',
        ])->find($orderId);

        if (!$this->order) {
            abort(404);
        }

        // Check if this order is assigned to the current staff
        // For Phase 2, we show all orders to staff (admin assignment not implemented yet)
        // In production, uncomment the following:
        // if ($this->order->assigned_staff_id !== auth()->id()) {
        //     abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        // }
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            'pending' => 'bg-amber-50 text-amber-700',
            'paid', 'approved' => 'bg-green-50 text-green-700',
            'processing' => 'bg-blue-50 text-blue-700',
            'ready_to_ship' => 'bg-violet-50 text-violet-700',
            'shipped' => 'bg-purple-50 text-purple-700',
            'delivered', 'completed' => 'bg-emerald-50 text-emerald-700',
            'cancelled', 'rejected' => 'bg-red-50 text-red-700',
            default => 'bg-slate-50 text-slate-600',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return Order::getStatusLabel($status);
    }

    public function getPaymentBadgeClass(?string $status): string
    {
        if (!$status) {
            return 'bg-slate-50 text-slate-600';
        }

        return match($status) {
            'paid', 'success' => 'bg-green-50 text-green-700',
            'pending' => 'bg-amber-50 text-amber-700',
            'failed', 'expired' => 'bg-red-50 text-red-700',
            default => 'bg-slate-50 text-slate-600',
        };
    }

    public function getFormattedEstimatedDeliveryProperty(): ?string
    {
        return $this->order?->getFormattedEstimatedDelivery();
    }

    public function render()
    {
        return view('livewire.staff.order-detail', [
            'order' => $this->order,
        ]);
    }
}
