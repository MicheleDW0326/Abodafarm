<?php

namespace App\Livewire\Pelanggan;

use App\Events\CustomerConfirmedDelivery;
use App\Events\OrderCompleted;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\NotificationService;
use Livewire\Attributes\On;
use Livewire\Component;

class ConfirmDelivery extends Component
{
    public bool $showModal = false;
    public ?Order $order = null;

    public function mount()
    {
        // Auto-open modal if order_id is passed
        if ($orderId = request()->route('order_id')) {
            $order = Order::find($orderId);
            if ($order && $order->user_id === auth()->id() && $order->isAwaitingConfirmation()) {
                $this->order = $order;
                $this->showModal = true;
            }
        }
    }

    #[On('open-confirm-delivery')]
    public function openModal(Order $order)
    {
        // Verify order belongs to current user
        if ($order->user_id !== auth()->id()) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki akses ke pesanan ini.',
            ]);
            return;
        }

        // Verify order is awaiting confirmation
        if (!$order->isAwaitingConfirmation()) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Tidak Dapat Dikonfirmasi',
                'message' => 'Pesanan ini tidak menunggu konfirmasi.',
            ]);
            return;
        }

        $this->order = $order;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->order = null;
    }

    public function confirmDelivery()
    {
        if (!$this->order) {
            return;
        }

        // Verify order still belongs to current user
        if ($this->order->user_id !== auth()->id()) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki akses ke pesanan ini.',
            ]);
            $this->closeModal();
            return;
        }

        // Verify order is still awaiting confirmation
        if (!$this->order->isAwaitingConfirmation()) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Tidak Dapat Dikonfirmasi',
                'message' => 'Pesanan ini tidak menunggu konfirmasi.',
            ]);
            $this->closeModal();
            return;
        }

        $user = auth()->user();
        $completedAt = now();

        // Update order status
        $this->order->update([
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => $completedAt,
            'completed_by_customer' => true,
            'completed_automatically' => false,
        ]);

        // Log status change
        OrderStatusLog::create([
            'order_id' => $this->order->id,
            'from_status' => 'delivered',
            'to_status' => Order::STATUS_COMPLETED,
            'triggered_by_type' => OrderStatusLog::TRIGGER_CUSTOMER,
            'triggered_by_id' => $user->id,
            'notes' => "Pelanggan {$user->name} mengkonfirmasi pesanan telah diterima.",
        ]);

        // Fire events
        event(new CustomerConfirmedDelivery(
            $this->order->fresh(),
            $user,
            $completedAt->toIso8601String()
        ));

        event(new OrderCompleted(
            $this->order->fresh(),
            $user,
            false,
            $completedAt->toIso8601String()
        ));

        // Notify staff
        if ($this->order->assignedStaff) {
            app(NotificationService::class)->orderCompleted(
                $this->order->assignedStaff,
                $this->order->order_number
            );
        }

        // Notify admin
        app(NotificationService::class)->orderCompletedAdmin(
            $this->order->order_number,
            'Pelanggan'
        );

        $this->closeModal();

        $this->dispatch('deliveryConfirmed', ['orderId' => $this->order->id]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Pesanan Dikonfirmasi!',
            'message' => "Terima kasih! Pesanan {$this->order->order_number} telah dikonfirmasi.",
        ]);
    }

    public function render()
    {
        return view('livewire.pelanggan.confirm-delivery');
    }
}
