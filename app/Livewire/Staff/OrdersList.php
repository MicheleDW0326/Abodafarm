<?php

namespace App\Livewire\Staff;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Pesanan Saya - Staff AbodaFarm')]
class OrdersList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $searchQuery = '';
    public string $statusFilter = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Status filter options
    public array $statusOptions = [
        'all' => 'Semua Status',
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'approved' => 'Disetujui',
        'processing' => 'Diproses',
        'ready_to_ship' => 'Siap Dikirim',
        'shipped' => 'Dikirim',
        'delivered' => 'Diterima',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        if (request()->has('search')) {
            $this->searchQuery = request()->query('search');
        }
        if (request()->has('status')) {
            $this->statusFilter = request()->query('status');
        }
        if (request()->has('dateFrom')) {
            $this->dateFrom = request()->query('dateFrom');
        }
        if (request()->has('dateTo')) {
            $this->dateTo = request()->query('dateTo');
        }
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status)
    {
        $this->statusFilter = $status;
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

    public function clearFilters()
    {
        $this->searchQuery = '';
        $this->statusFilter = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    protected function getOrdersQuery()
    {
        $staffId = auth()->id();

        $query = Order::query()
            ->with(['user', 'assignedByUser', 'items'])
            ->assignedToStaff($staffId);

        // Search
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('customer_phone', 'like', '%' . $this->searchQuery . '%')
                    ->orWhereHas('items', function ($itemQuery) {
                        $itemQuery->where('product_name', 'like', '%' . $this->searchQuery . '%');
                    });
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    public function getOrders()
    {
        return $this->getOrdersQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function getStats(): array
    {
        $staffId = auth()->id();

        // OPTIMIZED: Single query for all stats using CASE WHEN
        $statsData = DB::table('orders')
            ->where('assigned_staff_id', $staffId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status IN ('paid', 'approved', 'processing') THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN status IN ('delivered', 'completed') THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        return [
            'total' => (int) $statsData->total,
            'pending' => (int) $statsData->pending,
            'processing' => (int) $statsData->processing,
            'shipped' => (int) $statsData->shipped,
            'completed' => (int) $statsData->completed,
        ];
    }

    public function getProductSummary(Order $order): string
    {
        $items = $order->items ?? collect();
        if ($items->isEmpty()) {
            return '-';
        }

        $count = $items->count();
        $firstProduct = $items->first()?->product_name ?? 'Produk';

        if ($count === 1) {
            return $firstProduct;
        }

        return $firstProduct . ' dan ' . ($count - 1) . ' lainnya';
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            'pending' => 'bg-amber-50 text-amber-700',
            'paid', 'approved' => 'bg-green-50 text-green-700',
            'processing' => 'bg-blue-50 text-blue-700',
            'ready_to_ship' => 'bg-violet-50 text-violet-700',
            'shipped' => 'bg-purple-50 text-purple-700',
            'delivered', 'completed' => 'bg-emerald-50 text-emerald-700',
            'cancelled', 'rejected' => 'bg-red-50 text-red-700',
            default => 'bg-slate-50 text-slate-600',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return $this->statusOptions[$status] ?? Order::getStatusLabel($status);
    }

    public function render()
    {
        $orders = $this->getOrders();
        $stats = $this->getStats();

        return view('livewire.staff.orders-list', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
