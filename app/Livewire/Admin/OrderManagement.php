<?php

namespace App\Livewire\Admin;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\OrderStateMachine;
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
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Bulk selection
    public array $selectedOrders = [];
    public bool $selectAll = false;

    // Order Details
    public ?Order $selectedOrder = null;

    // Timeline data for selected order
    public array $selectedOrderTimeline = [];

    // UI State
    public bool $showDetailModal = false;

    // Status change
    public bool $showStatusModal = false;
    public string $newStatus = '';

    // Status options
    public array $statusOptions = [
        'all' => 'Semua Status',
        'pending' => 'Menunggu',
        'paid' => 'Sudah Bayar',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Diterima',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    // Sortable columns
    public array $sortableColumns = [
        'order_number' => 'Kode Pesanan',
        'customer_name' => 'Pelanggan',
        'total_amount' => 'Total',
        'created_at' => 'Tanggal',
        'status' => 'Status',
    ];

    protected $queryString = [
        'tab' => ['except' => 'all'],
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        if (request()->has('tab')) {
            $this->activeTab = request()->query('tab');
        }
        if (request()->has('status')) {
            $this->statusFilter = request()->query('status');
        }
        if (request()->has('search')) {
            $this->searchQuery = request()->query('search');
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->clearSelection();
    }

    public function setStatusFilter(string $status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
        $this->clearSelection();
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

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedOrders = $this->getOrdersQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function toggleSelection(string $orderId)
    {
        $index = array_search($orderId, $this->selectedOrders);
        if ($index !== false) {
            unset($this->selectedOrders[$index]);
            $this->selectedOrders = array_values($this->selectedOrders);
        } else {
            $this->selectedOrders[] = $orderId;
        }
    }

    public function clearSelection()
    {
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function bulkUpdateStatus(string $status)
    {
        if (empty($this->selectedOrders)) {
            return;
        }

        $count = 0;
        foreach ($this->selectedOrders as $orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $fromStatus = $order->effective_status ?? $order->status;

                $result = OrderStateMachine::transition(
                    $order,
                    $status,
                    OrderStatusLog::TRIGGER_ADMIN,
                    auth()->id(),
                    'Bulk update status oleh admin'
                );

                if ($result['success']) {
                    event(new OrderStatusChanged(
                        $order->fresh(['statusLogs.triggeredBy']),
                        $fromStatus,
                        $status,
                        OrderStatusLog::TRIGGER_ADMIN,
                        auth()->id(),
                        'Bulk update status oleh admin'
                    ));
                    $count++;
                }
            }
        }

        $this->clearSelection();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => "{$count} pesanan berhasil diupdate ke status '{$this->statusOptions[$status]}'"
        ]);
    }

    public function viewOrder(Order $order)
    {
        $this->selectedOrder = $order->load(['items', 'user', 'statusLogs.triggeredBy']);
        $this->selectedOrderTimeline = $this->selectedOrder->timeline_data;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedOrder = null;
        $this->selectedOrderTimeline = [];
        $this->showStatusModal = false;
        $this->newStatus = '';
    }

    public function openStatusModal(string $status)
    {
        $this->newStatus = $status;
        $this->showStatusModal = true;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->newStatus = '';
    }

    public function updateStatus(Order $order, string $status)
    {
        $fromStatus = $order->effective_status ?? $order->status;

        $result = OrderStateMachine::transition(
            $order,
            $status,
            OrderStatusLog::TRIGGER_ADMIN,
            auth()->id(),
            'Status diupdate oleh admin'
        );

        if ($result['success']) {
            event(new OrderStatusChanged(
                $order->fresh(['statusLogs.triggeredBy']),
                $fromStatus,
                $status,
                OrderStatusLog::TRIGGER_ADMIN,
                auth()->id(),
                'Status diupdate oleh admin'
            ));

            if ($this->selectedOrder && $this->selectedOrder->id === $order->id) {
                $this->selectedOrder = $order->fresh(['items', 'user', 'statusLogs.triggeredBy']);
                $this->selectedOrderTimeline = $this->selectedOrder->timeline_data;
            }

            $this->closeStatusModal();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Status pesanan berhasil diupdate!'
            ]);
        } else {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => $result['message']
            ]);
        }
    }

    public function getValidNextStatuses(Order $order): array
    {
        return OrderStateMachine::getValidNextStatuses($order);
    }

    public function getOrderStats(): array
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
            'completed' => (int) ($stats['completed'] ?? 0),
            'cancelled' => (int) ($stats['cancelled'] ?? 0),
            'approved' => (int) ($stats['approved'] ?? 0),
            'rejected' => (int) ($stats['rejected'] ?? 0),
        ];
    }

    protected function getOrdersQuery()
    {
        return Order::query()
            ->with(['user', 'statusLogs'])
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('customer_phone', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->activeTab === 'cart', fn($q) => $q->where('order_type', 'cart'))
            ->when($this->activeTab === 'inquiry', fn($q) => $q->where('order_type', 'inquiry'))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter));
    }

    public function render()
    {
        $ordersQuery = $this->getOrdersQuery()
            ->orderBy($this->sortField, $this->sortDirection);

        $orders = $ordersQuery->paginate(15);
        $stats = $this->getOrderStats();

        return view('livewire.admin.order-management', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
