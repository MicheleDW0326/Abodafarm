<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Manajemen Pesanan')]
class OrderManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $activeTab = 'all';
    public string $searchQuery = '';
    public string $statusFilter = 'all';

    // Order Details
    public ?Order $selectedOrder = null;

    // UI State
    public bool $showDetailModal = false;

    protected $queryString = [
        'tab' => ['except' => 'all'],
    ];

    public function mount()
    {
        if (request()->has('tab')) {
            $this->activeTab = request()->query('tab');
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function viewOrder(Order $order)
    {
        $this->selectedOrder = $order->load('items');
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedOrder = null;
    }

    public function updateStatus(Order $order, string $status)
    {
        $order->update(['status' => $status]);
        $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Status pesanan berhasil diupdate!']);
    }

    public function getOrderStats()
    {
        $stats = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $typeStats = Order::selectRaw('order_type, COUNT(*) as count')
            ->groupBy('order_type')
            ->pluck('count', 'order_type');

        return [
            'total' => Order::count(),
            'cart' => (int) ($typeStats['cart'] ?? 0),
            'inquiry' => (int) ($typeStats['inquiry'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'processing' => (int) ($stats['processing'] ?? 0),
            'shipped' => (int) ($stats['shipped'] ?? 0),
            'delivered' => (int) ($stats['delivered'] ?? 0),
            'cancelled' => (int) ($stats['cancelled'] ?? 0),
        ];
    }

    public function render()
    {
        $ordersQuery = Order::query()
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('customer_phone', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->activeTab === 'cart', function ($query) {
                $query->where('order_type', 'cart');
            })
            ->when($this->activeTab === 'inquiry', function ($query) {
                $query->where('order_type', 'inquiry');
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->paginate(15);
        $stats = $this->getOrderStats();

        return view('livewire.admin.order-management', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
