<?php

namespace App\Livewire\Staff;

use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
#[Title('Pengiriman - Staff AbodaFarm')]
class Shipping extends Component
{
    use WithFileUploads;

    public string $tab = 'active';

    public ?Delivery $selectedDelivery = null;

    // Form fields for proof
    public $proofPhoto = null;
    public $customerSignature = null;
    public string $receiverName = '';
    public string $deliveryNotes = '';
    public ?float $latitude = null;
    public ?float $longitude = null;

    // Failure form
    public string $failureReason = '';

    public DeliveryService $deliveryService;

    public function mount(DeliveryService $deliveryService): void
    {
        $this->deliveryService = $deliveryService;
    }

    public function getActiveDeliveriesProperty()
    {
        return Delivery::forStaff(auth()->id())
            ->with(['order.user', 'timeline'])
            ->active()
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getAssignedDeliveriesProperty()
    {
        return Delivery::forStaff(auth()->id())
            ->where('status', Delivery::STATUS_ASSIGNED)
            ->with(['order.user'])
            ->get();
    }

    public function getOutForDeliveryProperty()
    {
        return Delivery::forStaff(auth()->id())
            ->where('status', Delivery::STATUS_OUT_FOR_DELIVERY)
            ->with(['order.user'])
            ->get();
    }

    public function getCompletedTodayProperty()
    {
        return Delivery::forStaff(auth()->id())
            ->where('status', Delivery::STATUS_DELIVERED)
            ->whereDate('delivered_at', today())
            ->with(['order.user'])
            ->get();
    }

    public function getStatsProperty(): array
    {
        return $this->deliveryService->getStaffWorkload(auth()->id());
    }

    public function selectDelivery(Delivery $delivery): void
    {
        if ($delivery->assigned_staff_id !== auth()->id()) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Delivery ini tidak ditugaskan ke Anda']);
            return;
        }

        $this->selectedDelivery = $delivery->load(['order.user', 'timeline']);
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->proofPhoto = null;
        $this->customerSignature = null;
        $this->receiverName = '';
        $this->deliveryNotes = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->failureReason = '';
    }

    public function closeModal(): void
    {
        $this->selectedDelivery = null;
        $this->resetForm();
    }

    public function acceptDelivery(): void
    {
        if (!$this->selectedDelivery) {
            return;
        }

        try {
            $this->deliveryService->acceptDelivery($this->selectedDelivery, auth()->id());
            $this->selectedDelivery->refresh();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Pesanan berhasil diterima']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function startDelivery(): void
    {
        if (!$this->selectedDelivery) {
            return;
        }

        try {
            $this->deliveryService->startDelivery($this->selectedDelivery, auth()->id());
            $this->selectedDelivery->refresh();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Pengiriman dimulai']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function completeDelivery(): void
    {
        if (!$this->selectedDelivery) {
            return;
        }

        $this->validate([
            'receiverName' => 'required|string|max:255',
            'proofPhoto' => 'nullable|image|max:2048',
            'customerSignature' => 'nullable|image|max:1024',
        ]);

        try {
            $proofData = [
                'receiver_name' => $this->receiverName,
                'notes' => $this->deliveryNotes,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ];

            if ($this->proofPhoto) {
                $proofData['proof_photo'] = $this->proofPhoto->store('delivery-proofs', 'public');
            }

            if ($this->customerSignature) {
                $proofData['customer_signature'] = $this->customerSignature->store('delivery-signatures', 'public');
            }

            $this->deliveryService->completeDelivery($this->selectedDelivery, auth()->id(), $proofData);
            $this->selectedDelivery->refresh();
            $this->closeModal();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Pengiriman berhasil diselesaikan!']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function markDeliveryFailed(): void
    {
        if (!$this->selectedDelivery || empty($this->failureReason)) {
            return;
        }

        try {
            $this->deliveryService->markDeliveryFailed(
                $this->selectedDelivery,
                auth()->id(),
                $this->failureReason,
                ['latitude' => $this->latitude, 'longitude' => $this->longitude]
            );
            $this->selectedDelivery->refresh();
            $this->closeModal();
            $this->dispatch('alert', ['type' => 'info', 'message' => 'Pengiriman ditandai gagal']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.staff.shipping', [
            'stats' => $this->stats,
            'activeDeliveries' => $this->activeDeliveries,
            'assignedDeliveries' => $this->assignedDeliveries,
            'outForDelivery' => $this->outForDelivery,
            'completedToday' => $this->completedToday,
        ]);
    }
}
