<?php

namespace App\Livewire\Pelanggan;

use App\Models\CustomerNotification;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard - Akun Saya')]
class Dashboard extends Component
{
    public function render()
    {
        $userId = auth()->id();

        // Stats
        $stats = [
            'totalBookings' => Order::booking()->where('user_id', $userId)->count(),
            'pendingBookings' => Order::booking()->where('user_id', $userId)->where('status', 'pending')->count(),
            'approvedBookings' => Order::booking()->where('user_id', $userId)->where('status', 'approved')->count(),
            'activeOrders' => Order::cart()->where('user_id', $userId)->active()->count(),
            'completedOrders' => Order::cart()->where('user_id', $userId)->whereIn('status', ['delivered', 'completed'])->count(),
            'totalSpent' => Order::cart()->where('user_id', $userId)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'unreadNotifications' => CustomerNotification::where('user_id', $userId)->unread()->count(),
        ];

        // Recent bookings
        $recentBookings = Order::booking()
            ->where('user_id', $userId)
            ->with(['items.product.owner.farmProfile'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Recent orders
        $recentOrders = Order::cart()
            ->where('user_id', $userId)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Unread notifications
        $unreadNotifications = CustomerNotification::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.pelanggan.dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'recentOrders' => $recentOrders,
            'unreadNotifications' => $unreadNotifications,
            'formatRupiah' => fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.'),
        ]);
    }
}
