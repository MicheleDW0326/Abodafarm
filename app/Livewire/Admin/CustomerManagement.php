<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Manajemen Pelanggan')]
class CustomerManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $searchQuery = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public ?User $selectedCustomer = null;
    public bool $showDetailModal = false;

    public array $sortableColumns = [
        'name' => 'Nama',
        'email' => 'Email',
        'created_at' => 'Tanggal Daftar',
    ];

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function getCustomersProperty()
    {
        return User::role('pelanggan')
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('phone', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->with('roles')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
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

    public function getCustomerStats(): array
    {
        $totalCustomers = User::role('pelanggan')->count();
        $newThisMonth = User::role('pelanggan')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $activeCustomers = User::role('pelanggan')
            ->whereHas('orders', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->count();

        return [
            'total' => $totalCustomers,
            'newThisMonth' => $newThisMonth,
            'activeCustomers' => $activeCustomers,
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
