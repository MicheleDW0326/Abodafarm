<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        // OPTIMIZED: Single query with CASE WHEN instead of 3 separate queries
        $stats = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'pelanggan')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN users.created_at >= ? THEN 1 ELSE 0 END) as new_this_month,
                (SELECT COUNT(DISTINCT user_id) FROM orders WHERE created_at >= ?) as active_customers
            ', [now()->startOfMonth()->toDateTimeString(), now()->subDays(30)->toDateTimeString()])
            ->first();

        return [
            'total' => (int) $stats->total,
            'newThisMonth' => (int) $stats->new_this_month,
            'activeCustomers' => (int) $stats->active_customers,
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
