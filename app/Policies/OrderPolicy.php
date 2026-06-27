<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin or staff can view all orders
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        // Customer can view their own order
        if ($order->user_id === $user->id) {
            return true;
        }

        // Farmer can view orders related to their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Admin or staff can update all orders
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        // Farmer can update orders related to their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Admin or staff can update status of all orders
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        // Farmer can update status of orders related to their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can approve a booking.
     */
    public function approve(User $user, Order $order): bool
    {
        // Must be a booking (inquiry type)
        if (!$order->isInquiry()) {
            return false;
        }

        // Must be in pending status
        if ($order->status !== Order::STATUS_PENDING) {
            return false;
        }

        // Admin or staff can approve any booking
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        // Farmer can approve bookings related to their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can reject a booking.
     */
    public function reject(User $user, Order $order): bool
    {
        // Must be a booking (inquiry type)
        if (!$order->isInquiry()) {
            return false;
        }

        // Must be in pending status
        if ($order->status !== Order::STATUS_PENDING) {
            return false;
        }

        // Admin or staff can reject any booking
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        // Farmer can reject bookings related to their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Must be in pending status
        if ($order->status !== Order::STATUS_PENDING) {
            return false;
        }

        // Customer can cancel their own order
        if ($order->user_id === $user->id) {
            return true;
        }

        // Admin or staff can cancel any order
        if ($user->hasRole(['admin', 'staf'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admin can delete orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the order timeline.
     */
    public function viewTimeline(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }
}
