<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Manajemen Pelanggan')]
class CustomerManagement extends Component
{
    public string $searchQuery = '';
    public ?User $selectedCustomer = null;
    public bool $showDetailModal = false;

    public function getCustomersProperty()
    {
        return User::role('pelanggan')
            ->when($this->searchQuery, function ($query) {
                $query->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
            })
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewCustomer(User $customer)
    {
        $this->selectedCustomer = $customer->load(['orders' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'orders.items']);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedCustomer = null;
    }

    public function getCustomerStats()
    {
        return [
            'total' => User::role('pelanggan')->count(),
            'newThisMonth' => User::role('pelanggan')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.customer-management', [
            'customers' => $this->customers,
            'stats' => $this->getCustomerStats(),
        ]);
    }
}
