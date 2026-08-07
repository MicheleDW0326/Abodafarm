<?php

namespace App\Livewire\Staff;

use App\Events\DeliveryCompleted;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\NotificationService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompleteDelivery extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public ?Order $order = null;

    // Form fields
    public string $receiverName = '';
    public string $deliveryNotes = '';
    public $deliveryPhoto = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $deliveryTime = '';

    // Validation rules
    protected function rules(): array
    {
        return [
            'receiverName' => 'required|string|max:100',
            'deliveryNotes' => 'nullable|string|max:500',
            'deliveryPhoto' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:3072',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'receiverName' => 'nama penerima',
            'deliveryNotes' => 'catatan pengiriman',
            'deliveryPhoto' => 'foto bukti pengiriman',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }

    #[On('open-complete-delivery')]
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

        // Verify order is being shipped
        if (!$order->isInDelivery()) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Tidak Dapat Menyelesaikan',
                'message' => 'Pesanan ini belum dalam proses pengiriman.',
            ]);
            return;
        }

        $this->order = $order;
        $this->reset(['receiverName', 'deliveryNotes', 'latitude', 'longitude']);
        $this->deliveryPhoto = null;
        $this->deliveryTime = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->order = null;
        $this->reset(['receiverName', 'deliveryNotes', 'latitude', 'longitude']);
        $this->deliveryPhoto = null;
    }

    public function updatedDeliveryPhoto()
    {
        $this->validateOnly('deliveryPhoto');
    }

    public function getCurrentLocation()
    {
        $this->dispatch('getCurrentLocation');
    }

    #[On('locationReceived')]
    public function setLocation(float $lat, float $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    public function completeDelivery()
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
        $deliveryTime = now();

        // Store the photo
        $photoPath = null;
        if ($this->deliveryPhoto) {
            $photoPath = $this->deliveryPhoto->store('delivery-proofs/' . $this->order->id, 'public');
        }

        // Update order with delivery proof
        $this->order->update([
            'status' => Order::STATUS_DELIVERED,
            'delivered_at' => $deliveryTime,
            'delivery_completed_at' => $deliveryTime,
            'receiver_name' => $this->receiverName,
            'delivery_photo' => $photoPath,
            'delivery_note' => $this->deliveryNotes ?: null,
            'delivery_latitude' => $this->latitude,
            'delivery_longitude' => $this->longitude,
        ]);

        // Log status change
        OrderStatusLog::create([
            'order_id' => $this->order->id,
            'from_status' => 'shipped',
            'to_status' => Order::STATUS_DELIVERED,
            'triggered_by_type' => OrderStatusLog::TRIGGER_STAFF,
            'triggered_by_id' => $user->id,
            'notes' => "Staff {$user->name} menyelesaikan pengiriman. " .
                       "Penerima: {$this->receiverName}. " .
                       "Waktu: " . $deliveryTime->format('d M Y H:i') .
                       ($this->deliveryNotes ? ". Catatan: {$this->deliveryNotes}" : ''),
        ]);

        // Fire event
        event(new DeliveryCompleted(
            $this->order->fresh(),
            $user,
            $this->receiverName,
            $photoPath,
            $this->deliveryNotes ?: null,
            $this->latitude,
            $this->longitude,
            $deliveryTime->toIso8601String()
        ));

        // Send notification to customer
        app(NotificationService::class)->orderDelivered(
            $this->order->user,
            $this->order->order_number
        );

        $this->closeModal();

        $this->dispatch('deliveryCompleted', ['orderId' => $this->order->id]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => "Pengiriman pesanan {$this->order->order_number} telah diselesaikan.",
        ]);
    }

    public function render()
    {
        return view('livewire.staff.complete-delivery');
    }
}
