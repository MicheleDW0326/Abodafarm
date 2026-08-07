<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class StaffSelection extends Component
{
    public ?Order $order = null;
    public bool $showModal = false;

    public string $searchQuery = '';
    public ?User $selectedStaff = null;

    public function mount()
    {
        $this->reset(['searchQuery', 'selectedStaff']);
    }

    #[On('open-staff-selection')]
    public function openModal(Order $order)
    {
        $this->order = $order;
        $this->showModal = true;
        $this->reset(['searchQuery', 'selectedStaff']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->order = null;
        $this->reset(['searchQuery', 'selectedStaff']);
    }

    public function selectStaff(User $staff)
    {
        $this->selectedStaff = $staff;
    }

    public function clearSelection()
    {
        $this->selectedStaff = null;
    }

    public function assignStaff()
    {
        if (!$this->order || !$this->selectedStaff) {
            return;
        }

        // Validate staff role
        if (!$this->selectedStaff->hasRole('staf')) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Pengguna yang dipilih bukan staff.',
            ]);
            return;
        }

        // Validate staff is active (not deleted, etc.)
        if ($this->selectedStaff->deleted_at || !$this->selectedStaff->is_active) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Staff yang dipilih tidak aktif atau telah dihapus.',
            ]);
            return;
        }

        // Update order assignment
        $this->order->update([
            'assigned_staff_id' => $this->selectedStaff->id,
            'assigned_at' => now(),
            'assigned_by' => auth()->id(),
        ]);

        // Dispatch event for other components
        $this->dispatch('staffAssigned', [
            'orderId' => $this->order->id,
            'staffId' => $this->selectedStaff->id,
        ]);

        $this->closeModal();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => "Pesanan {$this->order->order_number} ditugaskan ke {$this->selectedStaff->name}.",
        ]);
    }

    public function getAvailableStaffProperty()
    {
        $query = User::query()
            ->role('staf')
            ->where(function ($q) {
                $q->whereNull('deleted_at')
                    ->orWhere('deleted_at', '>=', now());
            })
            ->where('is_active', true);

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return $query->get()->map(function ($staff) {
            // Get active order count for this staff
            $activeOrderCount = DB::table('orders')
                ->where('assigned_staff_id', $staff->id)
                ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                ->count();

            // Determine status
            $status = $activeOrderCount >= 5 ? 'busy' : 'available';

            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone ?? '-',
                'active_orders' => $activeOrderCount,
                'status' => $status,
                'status_label' => $status === 'available' ? 'Tersedia' : 'Sibuk',
                'initials' => $staff->initials(),
            ];
        });
    }

    public function render()
    {
        return view('livewire.admin.staff-selection');
    }
}
