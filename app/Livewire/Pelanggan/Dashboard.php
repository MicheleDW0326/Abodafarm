<?php

namespace App\Livewire\Pelanggan;

use App\Models\CustomerNotification;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
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

        // ========================================================================
        // OPTIMIZED: Single query for booking stats
        // ========================================================================
        $bookingStats = DB::table('orders')
            ->where('user_id', $userId)
            ->where('order_type', 'inquiry')
            ->selectRaw("
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status IN ('pending', 'survey_scheduled') THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN status IN ('approved', 'processing', 'ready_for_delivery') THEN 1 ELSE 0 END) as approved_bookings,
                SUM(CASE WHEN status IN ('completed', 'delivered') THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN status IN ('rejected', 'cancelled') THEN 1 ELSE 0 END) as rejected_bookings
            ")
            ->first();

        // ========================================================================
        // OPTIMIZED: Single query for order stats
        // ========================================================================
        $orderStats = DB::table('orders')
            ->where('user_id', $userId)
            ->where('order_type', 'cart')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                SUM(CASE WHEN status IN ('paid', 'processing') THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN status IN ('delivered', 'completed') THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN status IN ('paid', 'processing', 'shipped', 'delivered', 'completed') THEN 1 ELSE 0 END) as paid_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as failed_payments,
                COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END), 0) as total_spent
            ")
            ->first();

        // Notification count
        $unreadNotifications = CustomerNotification::where('user_id', $userId)->unread()->count();

        // Stats
        $activeOrders = (int) $orderStats->pending_payments + (int) $orderStats->processing_orders + (int) $orderStats->shipped_orders;

        $stats = [
            'totalBookings' => (int) $bookingStats->total_bookings,
            'pendingBookings' => (int) $bookingStats->pending_bookings,
            'approvedBookings' => (int) $bookingStats->approved_bookings,
            'completedBookings' => (int) $bookingStats->completed_bookings,
            'rejectedBookings' => (int) $bookingStats->rejected_bookings,
            'activeOrders' => $activeOrders,
            'pendingPayments' => (int) $orderStats->pending_payments,
            'processingOrders' => (int) $orderStats->processing_orders,
            'shippedOrders' => (int) $orderStats->shipped_orders,
            'deliveredOrders' => (int) $orderStats->delivered_orders,
            'paidOrders' => (int) $orderStats->paid_orders,
            'failedPayments' => (int) $orderStats->failed_payments,
            'totalSpent' => (int) $orderStats->total_spent,
            'unreadNotifications' => $unreadNotifications,
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
        $unreadNotificationsList = CustomerNotification::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.pelanggan.dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'recentOrders' => $recentOrders,
            'unreadNotifications' => $unreadNotificationsList,
            'formatRupiah' => fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.'),
        ]);
    }
}
