<?php

namespace App\Livewire\Staff;

use App\Events\DeliveryStarted;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\NotificationService;
use App\Services\OrderStateMachine;
use Livewire\Attributes\On;
use Livewire\Component;

class StartDelivery extends Component
{
    public bool $showModal = false;
    public ?Order $order = null;

    // Form fields
    public string $estimatedDeliveryTime = '';
    public string $deliveryNotes = '';
    public string $vehicleInfo = '';
    public string $courierName = '';

    protected $rules = [
        'estimatedDeliveryTime' => 'required|date|after:now',
        'deliveryNotes' => 'nullable|string|max:500',
        'vehicleInfo' => 'nullable|string|max:255',
        'courierName' => 'nullable|string|max:100',
    ];

    protected $validationAttributes = [
        'estimatedDeliveryTime' => 'estimasi waktu pengiriman',
        'deliveryNotes' => 'catatan pengiriman',
        'vehicleInfo' => 'informasi kendaraan',
        'courierName' => 'nama kurir',
    ];

    #[On('open-start-delivery')]
    public function openModal(Order $order)
    {
        // Verify order is assigned to current staff
        if (!$order->canStaffManageDelivery(auth()->id())) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki akses untuk mengelola pesanan ini.',
            ]);
            return;
        }

        // Verify order is ready for delivery
        if (!$order->isReadyForDelivery()) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Tidak Dapat Memulai',
                'message' => 'Pesanan ini belum siap untuk dikirim.',
            ]);
            return;
        }

        $this->order = $order;
        $this->reset(['estimatedDeliveryTime', 'deliveryNotes', 'vehicleInfo', 'courierName']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->order = null;
        $this->reset(['estimatedDeliveryTime', 'deliveryNotes', 'vehicleInfo', 'courierName']);
    }

    public function startDelivery()
    {
        $this->validate();

        if (!$this->order) {
            return;
        }

        // Verify order is still assigned to current staff
        if (!$this->order->canStaffManageDelivery(auth()->id())) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki akses untuk mengelola pesanan ini.',
            ]);
            $this->closeModal();
            return;
        }

        $user = auth()->user();

        // Update order with delivery info
        $this->order->update([
            'status' => Order::STATUS_SHIPPED,
            'shipped_at' => now(),
            'delivery_started_at' => now(),
            'estimated_delivery_time' => $this->estimatedDeliveryTime,
            'delivery_notes' => $this->deliveryNotes ?: null,
            'vehicle_info' => $this->vehicleInfo ?: null,
            'courier_name' => $this->courierName ?: null,
        ]);

        // Log status change
        OrderStatusLog::create([
            'order_id' => $this->order->id,
            'from_status' => $this->order->getOriginal('status'),
            'to_status' => Order::STATUS_SHIPPED,
            'triggered_by_type' => OrderStatusLog::TRIGGER_STAFF,
            'triggered_by_id' => $user->id,
            'notes' => "Staff {$user->name} memulai pengiriman. Estimasi: " . \Carbon\Carbon::parse($this->estimatedDeliveryTime)->format('d M Y H:i') .
                       ($this->courierName ? ". Kurir: {$this->courierName}" : '') .
                       ($this->deliveryNotes ? ". Catatan: {$this->deliveryNotes}" : ''),
        ]);

        // Fire event
        event(new DeliveryStarted(
            $this->order->fresh(),
            $user,
            $this->estimatedDeliveryTime,
            $this->deliveryNotes ?: null,
            $this->vehicleInfo ?: null,
            $this->courierName ?: null
        ));

        // Send notification to customer
        app(NotificationService::class)->orderShipped(
            $this->order->user,
            $this->order->order_number,
            $this->order->tracking_number
        );

        $this->closeModal();

        $this->dispatch('deliveryStarted', ['orderId' => $this->order->id]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => "Pengiriman pesanan {$this->order->order_number} telah dimulai.",
        ]);
    }

    public function render()
    {
        return view('livewire.staff.start-delivery');
    }
}
