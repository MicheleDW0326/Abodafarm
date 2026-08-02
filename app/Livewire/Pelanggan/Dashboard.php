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

        // ========================================================================
        // BOOKING PROGRESS (New Workflow)
        // ========================================================================
        $pendingBookings = Order::booking()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('bookingStatusRelation', fn($q2) => $q2->where('code', 'pending'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['pending']));
            })
            ->count();

        $approvedBookings = Order::booking()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('bookingStatusRelation', fn($q2) => $q2->where('code', 'approved'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['approved', 'processing']));
            })
            ->count();

        $completedBookings = Order::booking()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('bookingStatusRelation', fn($q2) => $q2->whereIn('code', ['completed']))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['completed']));
            })
            ->count();

        $rejectedBookings = Order::booking()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('bookingStatusRelation', fn($q2) => $q2->where('code', 'rejected'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['rejected', 'cancelled']));
            })
            ->count();

        // ========================================================================
        // ORDER PROGRESS (New Workflow)
        // ========================================================================
        $pendingPayments = Order::cart()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('paymentStatusRelation', fn($q2) => $q2->where('code', 'pending'))
                    ->orWhere(fn($q2) => $q2->whereNull('payment_status_id')->whereIn('status', ['pending']));
            })
            ->count();

        $processingOrders = Order::cart()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('shipmentStatusRelation', fn($q2) => $q2->where('code', 'processing'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['processing']));
            })
            ->count();

        $shippedOrders = Order::cart()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('shipmentStatusRelation', fn($q2) => $q2->where('code', 'shipped'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['shipped']));
            })
            ->count();

        $deliveredOrders = Order::cart()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('shipmentStatusRelation', fn($q2) => $q2->whereIn('code', ['delivered', 'completed']))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['delivered', 'completed']));
            })
            ->count();

        $activeOrders = $pendingPayments + $processingOrders + $shippedOrders;

        // ========================================================================
        // PAYMENT STATUS (New Workflow)
        // ========================================================================
        $paidOrders = Order::cart()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('paymentStatusRelation', fn($q2) => $q2->where('code', 'paid'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed']));
            })
            ->count();

        $failedPayments = Order::cart()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereHas('paymentStatusRelation', fn($q2) => $q2->where('code', 'payment_failed'))
                    ->orWhere(fn($q2) => $q2->whereIn('status', ['cancelled']));
            })
            ->count();

        // Stats
        $stats = [
            'totalBookings' => Order::booking()->where('user_id', $userId)->count(),
            'pendingBookings' => $pendingBookings,
            'approvedBookings' => $approvedBookings,
            'completedBookings' => $completedBookings,
            'rejectedBookings' => $rejectedBookings,
            'activeOrders' => $activeOrders,
            'pendingPayments' => $pendingPayments,
            'processingOrders' => $processingOrders,
            'shippedOrders' => $shippedOrders,
            'deliveredOrders' => $deliveredOrders,
            'paidOrders' => $paidOrders,
            'failedPayments' => $failedPayments,
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
