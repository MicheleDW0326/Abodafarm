<?php

namespace App\Livewire\Staff;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\OrderStateMachine;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Operasi Staff')]
class Operations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Quick filter tabs
    public string $quickFilter = 'ready_to_process';

    // Selection
    public array $selectedOrders = [];
    public bool $selectAll = false;

    // Order Details
    public ?Order $selectedOrder = null;
    public bool $showDetailModal = false;

    // Bulk action
    public bool $showBulkConfirmModal = false;
    public string $bulkAction = '';

    // Quick filter options
    public array $quickFilters = [
        'ready_to_process' => ['label' => 'Siap Diproses', 'statuses' => ['paid', 'approved']],
        'processing' => ['label' => 'Sedang Diproses', 'statuses' => ['processing']],
        'ready_to_ship' => ['label' => 'Siap Dikirim', 'statuses' => ['ready_to_ship']],
        'shipped' => ['label' => 'Sedang Dikirim', 'statuses' => ['shipped']],
        'delivered' => ['label' => 'Diterima', 'statuses' => ['delivered']],
    ];

    // Status badges for display
    public array $statusBadges = [
        'paid' => ['label' => 'Lunas', 'class' => 'bg-green-100 text-green-700'],
        'approved' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-700'],
        'processing' => ['label' => 'Diproses', 'class' => 'bg-blue-100 text-blue-700'],
        'ready_to_ship' => ['label' => 'Siap Kirim', 'class' => 'bg-violet-100 text-violet-700'],
        'shipped' => ['label' => 'Dikirim', 'class' => 'bg-purple-100 text-purple-700'],
        'delivered' => ['label' => 'Diterima', 'class' => 'bg-emerald-100 text-emerald-700'],
        'completed' => ['label' => 'Selesai', 'class' => 'bg-emerald-100 text-emerald-700'],
    ];

    protected $queryString = [
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
        'filter' => ['except' => 'ready_to_process'],
    ];

    public function mount()
    {
        if (request()->has('status')) {
            $this->statusFilter = request()->query('status');
        }
        if (request()->has('search')) {
            $this->searchQuery = request()->query('search');
        }
        if (request()->has('filter')) {
            $this->quickFilter = request()->query('filter');
        }
    }

    public function setQuickFilter(string $filter)
    {
        $this->quickFilter = $filter;
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

    public function updatedSearchQuery()
    {
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

    // Quick actions - Process Order
    public function processOrder(Order $order)
    {
        return $this->updateStatus($order, 'processing');
    }

    // Quick actions - Start Packing (mark as ready to ship)
    public function startPacking(Order $order)
    {
        return $this->updateStatus($order, 'ready_to_ship');
    }

    // Quick actions - Mark as Shipped
    public function shipOrder(Order $order)
    {
        return $this->updateStatus($order, 'shipped');
    }

    // Quick actions - Mark as Delivered
    public function markDelivered(Order $order)
    {
        return $this->updateStatus($order, 'delivered');
    }

    // Core status update method
    public function updateStatus(Order $order, string $status)
    {
        $fromStatus = $order->effective_status ?? $order->status;

        $result = OrderStateMachine::transition(
            $order,
            $status,
            OrderStatusLog::TRIGGER_STAFF,
            auth()->id(),
            "Diupdate oleh staff: {$this->getStatusLabel($status)}"
        );

        if ($result['success']) {
            event(new OrderStatusChanged(
                $order->fresh(['statusLogs.triggeredBy']),
                $fromStatus,
                $status,
                OrderStatusLog::TRIGGER_STAFF,
                auth()->id(),
                "Diupdate oleh staff"
            ));

            if ($this->selectedOrder && $this->selectedOrder->id === $order->id) {
                $this->selectedOrder = $order->fresh(['items', 'user', 'statusLogs.triggeredBy']);
            }

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => "Status pesanan {$order->order_number} diupdate ke {$this->getStatusLabel($status)}"
            ]);
        } else {
            // Validation errors should use warning icon
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Tidak Dapat Mengubah Status',
                'message' => $result['message']
            ]);
        }
    }

    // Bulk actions
    public function bulkAction(string $action)
    {
        if (empty($this->selectedOrders)) {
            return;
        }

        $targetStatus = match($action) {
            'process' => 'processing',
            'ready_to_ship' => 'ready_to_ship',
            'ship' => 'shipped',
            default => null,
        };

        if (!$targetStatus) {
            return;
        }

        $count = 0;
        $skipped = 0;
        $skipMessages = [];

        foreach ($this->selectedOrders as $orderId) {
            $order = Order::find($orderId);
            if (!$order) continue;

            $validStatuses = OrderStateMachine::getValidNextStatuses($order);
            if (!in_array($targetStatus, $validStatuses)) {
                $skipped++;
                $skipMessages[] = $order->order_number;
                continue;
            }

            $fromStatus = $order->effective_status ?? $order->status;

            $result = OrderStateMachine::transition(
                $order,
                $targetStatus,
                OrderStatusLog::TRIGGER_STAFF,
                auth()->id(),
                'Bulk update oleh staff'
            );

            if ($result['success']) {
                event(new OrderStatusChanged(
                    $order->fresh(['statusLogs.triggeredBy']),
                    $fromStatus,
                    $targetStatus,
                    OrderStatusLog::TRIGGER_STAFF,
                    auth()->id(),
                    'Bulk update oleh staff'
                ));
                $count++;
            } else {
                $skipped++;
                $skipMessages[] = $order->order_number;
            }
        }

        $this->clearSelection();

        if ($count === 0 && $skipped > 0) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Tidak Ada Perubahan',
                'message' => "Tidak ada pesanan yang dapat diupdate ke status ini. Pesanan: " . implode(', ', $skipMessages)
            ]);
        } elseif ($skipped > 0) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'Sebagian Berhasil',
                'message' => "{$count} pesanan berhasil diupdate, {$skipped} gagal."
            ]);
        } else {
            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => "{$count} pesanan berhasil diupdate"
            ]);
        }
    }

    // View order details
    public function viewOrder(Order $order)
    {
        $this->selectedOrder = $order->load(['items', 'user', 'statusLogs.triggeredBy']);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedOrder = null;
    }

    // Get available next statuses for an order
    public function getValidNextStatuses(Order $order): array
    {
        return OrderStateMachine::getValidNextStatuses($order);
    }

    // Check if order can be processed by staff
    public function canStaffProcess(Order $order): bool
    {
        $status = $order->effective_status ?? $order->status;
        return in_array($status, ['paid', 'approved']);
    }

    // Get status label
    protected function getStatusLabel(string $status): string
    {
        return Order::getStatusLabel($status);
    }

    // Stats for dashboard
    public function getStats(): array
    {
        $staffId = auth()->id();

        // OPTIMIZED: Single query for all stats using CASE WHEN
        $statsData = DB::table('orders')
            ->where('assigned_staff_id', $staffId)
            ->selectRaw("
                SUM(CASE WHEN status IN ('paid', 'approved') THEN 1 ELSE 0 END) as ready_to_process,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'ready_to_ship' THEN 1 ELSE 0 END) as ready_to_ship,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN status IN ('delivered', 'completed') THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN 1 ELSE 0 END) as total_active
            ")
            ->first();

        return [
            'ready_to_process' => (int) $statsData->ready_to_process,
            'processing' => (int) $statsData->processing,
            'ready_to_ship' => (int) $statsData->ready_to_ship,
            'shipped' => (int) $statsData->shipped,
            'delivered' => (int) $statsData->delivered,
            'total_active' => (int) $statsData->total_active,
        ];
    }

    protected function getOrdersQuery()
    {
        $query = Order::query()
            ->with(['user', 'statusLogs', 'assignedStaff']);

        // Filter by current staff's assigned orders
        $query->where('assigned_staff_id', auth()->id());

        // Apply quick filter
        if (isset($this->quickFilters[$this->quickFilter])) {
            $statuses = $this->quickFilters[$this->quickFilter]['statuses'];
            $query->whereIn('status', $statuses);
        }

        // Apply status filter if different from quick filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply search
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('customer_phone', 'like', '%' . $this->searchQuery . '%');
            });
        }

        // Only show actionable orders (not cancelled, rejected, completed)
        $query->whereNotIn('status', ['cancelled', 'rejected', 'completed']);

        return $query;
    }

    public function render()
    {
        $ordersQuery = $this->getOrdersQuery()
            ->orderBy($this->sortField, $this->sortDirection);

        $orders = $ordersQuery->paginate(15);
        $stats = $this->getStats();

        return view('livewire.staff.operations', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
